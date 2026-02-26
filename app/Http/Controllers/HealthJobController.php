<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthJobRequest;
use App\Models\Facility;
use App\Models\HealthJob;
use App\Models\User;
use App\Notifications\JobInterestNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Exceptions\RateLimitException;
use OpenAI\Exceptions\ApiException;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;

class HealthJobController extends Controller
{




    public function checkLicence(Request $request)
    {
        $validated = $request->validate([
            'licence_number' => [
                'required',
                'size:12',
                'regex:/^PT\d{4}[A-Z]\d{5}$/',
                'unique:facilities,licence_number',
            ],
        ]);
        //        dd($validated);

        $response = Http::asForm()->post('https://practice.pharmacyboardkenya.org/ajax/public', [
            'search_register' => 1,
            'cadre_id' => 4,
            'search_text' => $validated['licence_number'],
        ]);

        //        if (!$response->ok()) {
        //            return back()->withErrors([
        //                'licence_number' => 'License verification service is currently unavailable. Please try again later.'
        //            ]);
        //        }

        $crawler = new Crawler($response->body());
        $practitioner = null;

        // Extract data from the first matching row
        $crawler->filter('#datatable2 tbody tr')->each(function (Crawler $row) use (&$practitioner, $validated) {
            if ($practitioner) {
                return;
            } // Skip if we already found a match

            $name = trim($row->filter('td')->eq(0)->text());
            $license = trim($row->filter('td')->eq(1)->text());
            $statusValidity = trim($row->filter('td')->eq(2)->text());

            // Parse status and expiry date
            $words = explode(' ', $statusValidity);
            $expiryDate = end($words);
            $status = isset($words[1]) ? $words[1] : 'Unknown';

            // Verify this is the correct license
            if ($license === $validated['licence_number']) {
                $practitioner = [
                    'name' => $name,
                    'licence_number' => $license,
                    'expiry_date' => $expiryDate,
                    'status' => $status,
                ];
            }
        });

        if (! $practitioner) {
            return back()->withErrors([
                'licence_number' => 'License number not found or invalid. Please check and try again.',
            ]);
        }

        // Check if license is valid/active
        if ($practitioner['status'] !== 'Active') {
            return back()->withErrors([
                'licence_number' => "License is {$practitioner['status']}. Only valid licenses can be used for registration.",
            ]);
        }

        dd($practitioner);

        return to_route('register', [
            'practitioner' => $practitioner,
            'licence_verified' => true,
        ]);

        // Return success with practitioner data
        return Inertia::render('auth/Register', [
            'practitioner' => $practitioner,
            'licence_verified' => true,
        ]);

    }

    public function index(Request $request): \Inertia\Response
    {


        $user = auth()->user();

        $isProfileComplete = $user?->isProfileComplete() ?? false;

        $jobs = HealthJob::query()
            ->with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->when($request->job_type, fn ($query, $jobType) => $query->where('job_type', $jobType))
            ->when($request->location, fn ($query, $location) => $query->where('location', $location))
            ->where('is_active', true)
            ->when($request->time_filter, function ($query, $time_filter) {
                if ($time_filter === 'latest') {
                    $query->orderBy('created_at', 'desc');
                } elseif ($time_filter === 'oldest') {
                    $query->orderBy('created_at', 'asc');
                }
            }, function ($query) {
                // Default ordering when no time_filter is provided
                $query->orderBy('created_at', 'desc');
            })

            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('HealthJobs/Index', [
            'locations' => HealthJob::query()
                ->distinct()
                ->whereNotNull('location')
                ->get(['location']),
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'job_type', 'experience_level']),
            'isProfileComplete' => $isProfileComplete,
        ]);
    }

    public function isProfileComplete() {}

    public function create()
    {
        return Inertia::render('HealthJobs/Create');
    }



    public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $uploadedFile = $request->file('image');

        $prompt = "You are posting this job. Write in first person as the employer.

        Extract ALL visible information from this job posting image and return ONLY this JSON format (no markdown, no code blocks):

        {
            \"description\": \"<p>We are looking for...</p><h3>What You'll Do</h3><ul><li>Task 1</li></ul><h3>What We Need</h3><ul><li>Requirement 1</li></ul><h3>Additional Information</h3><p>Include any extra details like contact info, application process, company info, etc.</p>\",
            \"title\": \"job title from image\",
            \"location\": \"exact location mentioned\",
            \"job_type\": \"full-time or part-time or contract\",
            \"salary_min\": number,
            \"salary_max\": number,
            \"qualifications\": [\"requirement 1\", \"requirement 2\"],
            \"application_deadline\": \"deadline date if mentioned\",
            \"contact_email\": \"email if visible\",
            \"contact_phone\": \"phone if visible\",
            \"company_name\": \"organization/hospital name\",
            \"department\": \"specific department if mentioned\",
            \"experience_required\": \"years of experience mentioned\",
            \"additional_benefits\": [\"benefit 1\", \"benefit 2\"],
            \"application_instructions\": \"how to apply information\",
            \"extra_details\": \"any other information visible in the image that doesn't fit above categories\"
        }

        IMPORTANT:
        - Include ALL text visible in the image
        - If application deadline, contact info, or other details are visible, extract them
        - Put any information that doesn't fit standard categories in 'extra_details'
        - In description, include a section for additional information found in the image
        - Use null for missing information, don't guess

        Write naturally as if you're the hiring manager. Use 'we', 'our team', 'you'll join us'. Return raw JSON only.";

        try {
            $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
                ->generateContent([
                    $prompt,
                    new Blob(
                        mimeType: MimeType::IMAGE_JPEG,
                        data: base64_encode(file_get_contents($uploadedFile->getPathname()))
                    )
                ]);

            $responseText = $result->text();

            // Try to extract JSON from the response
            $jobData = $this->extractAndParseJson($responseText);

            if (!$jobData) {
                $formattedDescription = $this->formatDescriptionAsHtml($responseText);
                $jobData = [
                    'description' => $formattedDescription,
                    'title' => $this->generateTitleFromDescription($responseText),
                    'location' => null,
                    'job_type' => null,
                    'salary_min' => null,
                    'salary_max' => null,
                    'experience_level' => null,
                    'qualifications' => null,
                    'is_active' => true,
                    // Add these new fields
                    'application_deadline' => null,
                    'contact_email' => null,
                    'contact_phone' => null,
                    'company_name' => null,
                    'department' => null,
                    'experience_required' => null,
                    'additional_benefits' => null,
                    'application_instructions' => null,
                    'extra_details' => null
                ];
            }else {
//                / Ensure we have at least a title and description
                if (empty($jobData['description'])) {
                    $jobData['description'] = $this->formatDescriptionAsHtml($responseText);
                }

                // Enhance description with extracted extras
                $jobData['description'] = $this->enhanceDescriptionWithExtras($jobData['description'], $jobData);


                if (empty($jobData['title'])) {
                    $jobData['title'] = $this->generateTitleFromDescription($jobData['description']);
                }

                // Ensure is_active is set
                $jobData['is_active'] = true;

                // Clean up salary fields - ensure they're numbers or null
                $jobData['salary_min'] = $this->extractNumericValue($jobData['salary_min'] ?? null);
                $jobData['salary_max'] = $this->extractNumericValue($jobData['salary_max'] ?? null);
            }

            // Return the same view but with success data instead of redirecting
//            return redirect()->route('health-jobs.create')->with([
//                'success' => true,
//                'data' => $jobData,
//                'raw_response' => $responseText
//            ]);


            return  Inertia::render('HealthJobs/Create', [
                'success' => true,
                'data' => $jobData,
                'raw_response' => $responseText // Include raw response for debugging
            ]);

        } catch (\Exception $e) {
            // Handle errors gracefully
            return Inertia::render('HealthJobs/Create', [
                'success' => false,
                'error' => 'Failed to process image: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format plain text description as HTML for rich text editor
     */
    private function formatDescriptionAsHtml($text)
    {
        // Clean up the text
        $text = trim($text);

        // Split into lines and remove empty ones
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            // Skip empty lines
            if (empty($line)) continue;

            // Check if line looks like a heading (short line, possibly with colons)
            if (strlen($line) < 50 && (str_contains($line, ':') || $this->looksLikeHeading($line))) {
                // Close any open list
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<h3>' . htmlspecialchars(rtrim($line, ':')) . '</h3>';
            }
            // Check if line looks like a bullet point
            elseif (preg_match('/^[\-\*\•]\s*(.+)/', $line, $matches)) {
                if (!$inList) {
                    $html .= '<ul>';
                    $inList = true;
                }
                $html .= '<li>' . htmlspecialchars($matches[1]) . '</li>';
            }
            // Check if line starts with a number (numbered list)
            elseif (preg_match('/^\d+[\.\)]\s*(.+)/', $line, $matches)) {
                if (!$inList) {
                    $html .= '<ol>';
                    $inList = true;
                }
                $html .= '<li>' . htmlspecialchars($matches[1]) . '</li>';
            }
            // Regular paragraph
            else {
                // Close any open list
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<p>' . htmlspecialchars($line) . '</p>';
            }
        }

        // Close any remaining open list
        if ($inList) {
            $html .= '</ul>';
        }

        // If we don't have any HTML, wrap the entire text in a paragraph
        if (empty($html)) {
            $html = '<p>' . htmlspecialchars($text) . '</p>';
        }

        return $html;
    }

    /**
     * Check if a line looks like a heading
     */
    /**
     * Check if a line looks like a heading
     */
    private function looksLikeHeading($line)
    {
        $headingKeywords = [
            'overview', 'description', 'responsibilities', 'requirements',
            'qualifications', 'benefits', 'duties', 'skills', 'experience',
            'job summary', 'about', 'role', 'position', 'what you', 'we offer'
        ];

        $lowerLine = strtolower($line);

        foreach ($headingKeywords as $keyword) {
            if (str_contains($lowerLine, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract numeric value from salary strings
     */
    private function extractNumericValue($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove non-numeric characters except decimals
        $numeric = preg_replace('/[^0-9.]/', '', $value);

        if (empty($numeric)) {
            return null;
        }

        return (float) $numeric;
    }

    private function enhanceDescriptionWithExtras($description, $jobData)
    {
        $extras = [];

        // Collect extra information
        if (!empty($jobData['application_deadline'])) {
            $extras[] = "<strong>Application Deadline:</strong> " . htmlspecialchars($jobData['application_deadline']);
        }

        if (!empty($jobData['contact_email'])) {
            $extras[] = "<strong>Contact Email:</strong> " . htmlspecialchars($jobData['contact_email']);
        }

        if (!empty($jobData['contact_phone'])) {
            $extras[] = "<strong>Contact Phone:</strong> " . htmlspecialchars($jobData['contact_phone']);
        }

        if (!empty($jobData['company_name'])) {
            $extras[] = "<strong>Organization:</strong> " . htmlspecialchars($jobData['company_name']);
        }

        if (!empty($jobData['department'])) {
            $extras[] = "<strong>Department:</strong> " . htmlspecialchars($jobData['department']);
        }

        if (!empty($jobData['experience_required'])) {
            $extras[] = "<strong>Experience Required:</strong> " . htmlspecialchars($jobData['experience_required']);
        }

        if (!empty($jobData['additional_benefits']) && is_array($jobData['additional_benefits'])) {
            $benefits = array_map('htmlspecialchars', $jobData['additional_benefits']);
            $extras[] = "<strong>Benefits:</strong> " . implode(', ', $benefits);
        }

        if (!empty($jobData['application_instructions'])) {
            $extras[] = "<strong>How to Apply:</strong> " . htmlspecialchars($jobData['application_instructions']);
        }

        if (!empty($jobData['extra_details'])) {
            $extras[] = "<strong>Additional Information:</strong> " . htmlspecialchars($jobData['extra_details']);
        }

        // If we have extras, add them to the description
        if (!empty($extras)) {
            $description .= "<h3>Additional Details</h3><p>" . implode("</p><p>", $extras) . "</p>";
        }

        return $description;
    }


    /**
     * Enhanced title generation that considers healthcare roles
     */
    private function generateTitleFromDescription($description)
    {
        $description = strtolower(trim(strip_tags($description)));

        // Healthcare-specific job keywords with more specific titles
        $jobKeywords = [
            'registered nurse' => 'Registered Nurse',
            'staff nurse' => 'Staff Nurse',
            'charge nurse' => 'Charge Nurse',
            'head nurse' => 'Head Nurse',
            'nurse manager' => 'Nurse Manager',
            'clinical nurse' => 'Clinical Nurse',
            'icu nurse' => 'ICU Nurse',
            'er nurse' => 'Emergency Room Nurse',
            'theatre nurse' => 'Theatre Nurse',
            'pediatric nurse' => 'Pediatric Nurse',
            'maternity nurse' => 'Maternity Nurse',
            'nurse' => 'Nursing Position',

            'medical doctor' => 'Medical Doctor',
            'consultant' => 'Medical Consultant',
            'specialist' => 'Medical Specialist',
            'resident doctor' => 'Resident Doctor',
            'house officer' => 'House Officer',
            'medical officer' => 'Medical Officer',
            'doctor' => 'Medical Doctor',
            'physician' => 'Physician',
            'surgeon' => 'Surgeon',
            'anesthesiologist' => 'Anesthesiologist',
            'radiologist' => 'Radiologist',
            'pathologist' => 'Pathologist',
            'cardiologist' => 'Cardiologist',
            'neurologist' => 'Neurologist',
            'gynecologist' => 'Gynecologist',
            'pediatrician' => 'Pediatrician',

            'pharmacist' => 'Pharmacist',
            'clinical pharmacist' => 'Clinical Pharmacist',
            'pharmacy technician' => 'Pharmacy Technician',

            'lab technician' => 'Laboratory Technician',
            'medical technician' => 'Medical Technician',
            'radiology technician' => 'Radiology Technician',
            'technician' => 'Medical Technician',

            'physiotherapist' => 'Physiotherapist',
            'occupational therapist' => 'Occupational Therapist',
            'therapist' => 'Therapist',

            'medical assistant' => 'Medical Assistant',
            'nursing assistant' => 'Nursing Assistant',
            'healthcare assistant' => 'Healthcare Assistant',
            'assistant' => 'Healthcare Assistant',

            'hospital administrator' => 'Hospital Administrator',
            'health manager' => 'Health Manager',
            'medical records' => 'Medical Records Officer',
            'health information' => 'Health Information Officer',
            'manager' => 'Healthcare Manager',
            'coordinator' => 'Healthcare Coordinator',
            'supervisor' => 'Healthcare Supervisor',
        ];

        // Look for more specific matches first
        foreach ($jobKeywords as $keyword => $title) {
            if (str_contains($description, $keyword)) {
                return $title;
            }
        }

        // Default title
        return 'Healthcare Position';
    }

    private function extractAndParseJson($text)
    {
        // Remove markdown code blocks
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);
        $text = trim($text);

        // Try to find JSON in the text
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try decoding the entire text
        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return null;
    }



    public function store(HealthJobRequest $request)
    {
        Log::info(json_encode($request->all()));
        $healthJob = $request->validated();

        //        Facility::query()->where('');
//        $healthJob['facility_id'] = $request->user()->facility->id;
        $healthJob['user_id'] = Auth::id();
        $healthJob['uuid'] = Str::uuid();
        $healthJob['requirements'] = $request->qualifications;
        $healthJob['location'] = $request->location;

        // Create the health job record
        $healthJob = HealthJob::create($healthJob);

        return redirect()->route('health-jobs.index');

        return response()->json([
            'message' => 'Health job created successfully.',
            'data' => $healthJob,
        ], 201);
    }

    public function storeFromWhatsApp(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'job_type'    => 'nullable|string',
            'location'    => 'nullable|string',
            'salary_min'  => 'nullable|numeric',
            'salary_max'  => 'nullable|numeric',
            'qualifications' => 'nullable|array',
            'requirements'   => 'nullable|array',
            'raw_text'    => 'nullable|string',
        ]);

        HealthJob::create([
            'uuid'        => Str::uuid(),
            'title'       => $data['title'],
            'description' => $data['description'] ?? $data['raw_text'],
            'job_type'    => in_array($data['job_type'] ?? '', ['full-time','part-time','contract'])
                ? $data['job_type']
                : 'full-time',
            'location'       => $data['location'] ?? 'Kenya',
            'cadre'          => 'General',
            'salary_min'     => $data['salary_min'] ?? null,
            'salary_max'     => $data['salary_max'] ?? null,
            'qualifications' => $data['qualifications'] ?? null,
            'requirements'   => $data['requirements'] ?? null,
            'user_id'        => null,
            'is_active'      => false,
        ]);

        return response()->json(['message' => 'Job received'], 201);
    }


    public function show($id)
    {
        // Allow lookup by UUID (preferred) or by primary key ID to support routes/tests using either.
        $healthJob = HealthJob::query()
            ->with('interestedUsers')
            ->where('uuid', $id)
            ->first();

        if ($healthJob === null) {
            $healthJob = HealthJob::query()
                ->with('interestedUsers')
                ->findOrFail($id);
        }

        return Inertia::render('HealthJobs/Show', [
            'job' => $healthJob,
        ]);
    }


    public function test()
    {
        return response('OK', 200);
    }

    public function interested(Request $request)
    {
        $job = HealthJob::query()->where('uuid', $request->job)->with('user')->firstOrFail();


        // Try to create interest record
        $interest = $job->interests()->firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        $response = [
            'message' => 'You are already interested in this job',
            'already_interested' => true
        ];


        // Only send notification if this is a new interest
        if ($interest->wasRecentlyCreated) {
            $job->user->notify(new JobInterestNotification($request->user(), $job));

            return redirect(route('health-jobs.show',$request->job),303)->with([
                'flashMessage' => $response['message'] ,
            ]);

        }

        return redirect(route('health-jobs.show',$request->job),303)->with([
            'flashMessage' => $response['message'] ,
        ]);
    }
}
