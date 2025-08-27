<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\Program;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionCareerGuidanceController extends Controller
{
    public function index()
    {
        // You can customize these tips for institutions if needed

        // You may want to filter jobs by institution here if needed
        $inDemandJobs = Job::where('is_approved', 1)
            ->with('programs', 'category', 'sector')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($job) {
                return [
                    'role' => $job->job_title,
                    'category' => $job->category->name ?? null,
                    'sector' => $job->sector->name ?? null,
                    'skills' => is_array($job->skills) ? $job->skills : json_decode($job->skills, true),
                    'programs' => $job->programs->pluck('name')->toArray(),
                ];
            });

        $allSkills = Job::where('is_approved', 1)->pluck('skills')->toArray();
        $skillsFlat = collect($allSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy()
            ->sortDesc()
            ->take(10);


        $jobCounts = Job::where('is_approved', 1)
            ->selectRaw('job_title, count(*) as openings')
            ->groupBy('job_title')
            ->orderByDesc('openings')
            ->take(10)
            ->get();

        return Inertia::render('Institutions/CareerCounseling/Index', [
            'inDemandJobs' => $inDemandJobs,
            'topSkills' => $skillsFlat,
            'jobCounts' => $jobCounts, 
        ]);
    }
}