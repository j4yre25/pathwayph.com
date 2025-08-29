<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\Program;
use App\Models\Job;
use App\Models\SeminarRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PesoCareerGuidanceController extends Controller
{
    public function index()
    {
        // Example counseling tips
        $counselingTips = [
            'Prepare a professional resume and cover letter.',
            'Practice common interview questions.',
            'Research the company before your interview.',
            'Highlight your soft skills and adaptability.',
            'Follow up after interviews with a thank you note.',
        ];

        // Get in-demand roles and skills from jobs table
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

        // Top skills (by frequency in jobs)
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

        $resources = [
            [
                'title' => 'Resume Builder',
                'url' => 'https://www.canva.com/resumes/templates/',
                'description' => 'Create a professional resume with free templates.'
            ],
            [
                'title' => 'Interview Preparation',
                'url' => 'https://www.indeed.com/career-advice/interviewing',
                'description' => 'Tips and sample questions for job interviews.'
            ],
            [
                'title' => 'Labor Market Trends (DOLE)',
                'url' => 'https://www.dole.gov.ph/news-category/labor-market-information/',
                'description' => 'Latest labor market information from DOLE.'
            ],
        ];

        $jobCounts = Job::where('is_approved', 1)
            ->selectRaw('job_title, count(*) as openings')
            ->groupBy('job_title')
            ->orderByDesc('openings')
            ->take(10)
            ->get();

        // Seminar requests logic
        $seminarRequests = SeminarRequest::with('institution')->orderByDesc('created_at')->get();

        return Inertia::render('Admin/CareerGuidance', [
            'counselingTips' => $counselingTips,
            'inDemandJobs' => $inDemandJobs,
            'topSkills' => $skillsFlat,
            'resources' => $resources,
            'jobCounts' => $jobCounts,
            'seminarRequests' => $seminarRequests,
        ]);
    }

    public function updateSeminarRequestStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,disapproved',
        ]);
        $seminarRequest = SeminarRequest::findOrFail($id);
        $seminarRequest->update(['status' => $data['status']]);
        return back()->with('flash.banner', 'Request status updated.');
    }

    public function showSeminarRequest($id)
    {
        $request = SeminarRequest::with('institution')->findOrFail($id);
        return response()->json($request);
    }
}
