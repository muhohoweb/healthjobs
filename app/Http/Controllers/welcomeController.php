<?php

namespace App\Http\Controllers;

use App\Models\HealthJob;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class welcomeController extends Controller
{
    public function index()
    {
        $featuredJobs = collect();
        $totalJobs = 0;
        $totalFacilities = 0;

        if (Schema::hasTable('health_jobs')) {
            $featuredJobs = HealthJob::where('is_active', true)
                ->where('created_at', '>=', now()->subDays(5))
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($job) {
                    return [
                        'id'             => $job->id,
                        'uuid'           => $job->uuid,
                        'title'          => $job->title,
                        'description'    => $job->description,
                        'job_type'       => $job->job_type,
                        'location'       => $job->location,
                        'salary_min'     => $job->salary_min,
                        'salary_max'     => $job->salary_max,
                        'experience_level' => $job->experience_level,
                        'qualifications' => $job->qualifications ?? [],
                        'deadline'       => $job->deadline?->format('M d, Y'),
                        'created_at'     => $job->created_at->format('M d, Y'),
                    ];
                });

            $totalJobs = HealthJob::where('is_active', true)->count();
        }

        if (Schema::hasTable('facilities')) {
            $totalFacilities = \App\Models\Facility::count();
        }

        return Inertia::render('Welcome', [
            'featuredJobs' => $featuredJobs,
            'jobStats' => [
                'total_jobs'      => $totalJobs,
                'total_facilities' => $totalFacilities,
                'success_rate'    => 98,
            ],
        ]);
    }
}