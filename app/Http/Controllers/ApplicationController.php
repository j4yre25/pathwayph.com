<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Job $job)
    {
        // Get applications for a specific job
        $applications = $job->applications()->with('applicant')->get();

        return Inertia::render('Application/Index', [
            'applications' => $applications
        ]);
    }


    public function store(Request $request, Job $job)
    {
        $validated = $request->validate([
            'applicant_id' => 'required|exists:users,id', 
            'resume' => 'required|file|mimes:pdf,docx|max:2048',
        ]);

        $application = new JobApplication();
        $application->job_id = $job->id;
        $application->user_id = $validated['applicant_id'];
        $application->resume = $request->file('resume')->store('resumes');
        $application->status = 'pending'; // Default status
        $application->save();

        return redirect()->back()->with('flash.banner', 'Application submitted successfully.');
    }

    public function summary(Request $request)
{
    $user = $request->user();

    // Only get applications for this company’s jobs
    $jobIds = $user->jobs()->pluck('id');

    $applicationCount = JobApplication::whereIn('job_id', $jobIds)->count();
    $hireCount = JobApplication::whereIn('job_id', $jobIds)->where('status', 'hired')->count();
    $jobCount = $jobIds->count();

    return Inertia::render('Company/CompanyDashboard', [
        'summary' => [
            'total_jobs' => $jobCount,
            'total_applications' => $applicationCount,
            'total_hires' => $hireCount,
        ]
    ]);
}
}
