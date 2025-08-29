<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\Program;
use App\Models\Job;
use App\Models\SeminarRequest;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstitutionCareerGuidanceController extends Controller
{
    public function index()
    {
        // Existing logic
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

        // Seminar requests logic
        $institution = Institution::where('user_id', Auth::id())->first();
        $seminarRequests = $institution
            ? SeminarRequest::where('institution_id', $institution->id)->orderByDesc('created_at')->get()
            : collect();

        return Inertia::render('Institutions/CareerCounseling/Index', [
            'inDemandJobs' => $inDemandJobs,
            'topSkills' => $skillsFlat,
            'jobCounts' => $jobCounts,
            'seminarRequests' => $seminarRequests,
        ]);
    }

    public function storeSeminarRequest(Request $request)
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $data = $request->validate([
            'request_for' => 'required|string',
            'event_name' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'description' => 'nullable|string',
        ]);
        $data['institution_id'] = $institution->id;
        SeminarRequest::create($data);
        return back()->with('flash.banner', 'Seminar request sent!');
    }

    public function cancelSeminarRequest($id)
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $request = SeminarRequest::where('institution_id', $institution->id)->findOrFail($id);
        $request->update(['status' => 'cancelled']);
        return back()->with('flash.banner', 'Request cancelled.');
    }

    public function showSeminarRequest($id)
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $request = SeminarRequest::where('institution_id', $institution->id)->findOrFail($id);
        return response()->json($request);
    }
}