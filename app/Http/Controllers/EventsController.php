<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventsController extends Controller
{
    public function index()
    {
        return Inertia::render('Events/Index', [
            'events' => Event::all(),
        ]);
    }


    public function create()
    {
        return Inertia::render('Events/Create');
    }

    public function storeFromWhatsApp(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|string',
            'end_date'    => 'nullable|string',
            'link'        => 'nullable|string',
            'image_url'   => 'nullable|string', // encrypted WhatsApp URL
            'media_key'   => 'nullable|string', // for decryption
        ]);

        $imagePath = null;

        // Download and save the image if provided
        if (!empty($data['image_url']) && !empty($data['media_key'])) {
            $imagePath = $this->downloadWhatsAppImage($data['image_url'], $data['media_key']);
        }

        $event = Event::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date'  => $data['start_date'] ? Carbon::parse($data['start_date']) : null,
            'end_date'    => $data['end_date'] ? Carbon::parse($data['end_date']) : null,
            'link'        => $data['link'] ?? null,
            'image_path'  => $imagePath,
        ]);

        return response()->json([
            'message' => 'Event created',
            'id'      => $event->id,
            'image'   => $imagePath,
        ], 201);
    }

    private function downloadWhatsAppImage(string $url, string $mediaKey): ?string
    {
        try {
            $mediaKeyBytes = base64_decode($mediaKey);

            // HKDF derive keys
            $expanded  = hash_hkdf('sha256', $mediaKeyBytes, 112, 'WhatsApp Image Keys');
            $iv        = substr($expanded, 0, 16);
            $cipherKey = substr($expanded, 16, 32);

            // Download encrypted image
            $encrypted = \Illuminate\Support\Facades\Http::timeout(30)->get($url)->body();

            if (empty($encrypted)) {
                \Illuminate\Support\Facades\Log::error('Empty image download');
                return null;
            }

            // Strip MAC (last 10 bytes) and decrypt
            $decrypted = openssl_decrypt(
                substr($encrypted, 0, -10),
                'aes-256-cbc',
                $cipherKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                \Illuminate\Support\Facades\Log::error('Image decryption failed');
                return null;
            }

            $filename = 'events/wa_' . uniqid('', true) . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decrypted);

            \Illuminate\Support\Facades\Log::info('Event image saved', ['path' => $filename]);
            return $filename;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Image download failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(EventRequest $request)
    {

        $filename = '';
        $slimPayload = $request->get('slim');
        if (is_array($slimPayload) && !empty($slimPayload[0])) {
            $decodedData = json_decode($slimPayload[0], true);

            if (is_array($decodedData) && isset($decodedData['output']['image'])) {
                // Get the base64 image data
                $imageData = $decodedData['output']['image'];
                $parts = explode(',', (string)$imageData, 2);
                if (count($parts) === 2) {
                    $base64Data = $parts[1];
                    $binaryData = base64_decode($base64Data, true);

                    if ($binaryData !== false) {
                        // Delete old avatar if it exists
                        // Generate filename with user ID for better organization
                        $filename = 'events/event' . '_' . uniqid('', true) . '.jpg';
                        $request->validated()['image_path'] = $filename;


//                        Storage::disk('public')->put($filename, $binaryData);

                        Storage::disk('public')->put($filename, $binaryData, ['visibility' => 'public']);

                        $decodedData['output']['url'] = Storage::url($filename);

                    }
                }
                $start_date = Carbon::parse($request->start_date);
                $end_date = Carbon::parse($request->end_date);

                $validatedData = $request->validated();
                $validatedData['start_date'] = $start_date;
                $validatedData['end_date'] = $end_date;
                $validatedData['image_path'] = $filename;


                Event::query()->create($validatedData);

                return Redirect::route('events.index');

            }
        }
    }
}
