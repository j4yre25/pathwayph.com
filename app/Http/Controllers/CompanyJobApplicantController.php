<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyJobApplicantController extends Controller
{
    public function index(User $user)
    {
        $companyId = Auth::user()->company_id;
        $now = Carbon::now();

        $jobs = Job::withCount('applications')
            ->where('company_id', $companyId)
            ->get();

        // All applications for the company's jobs
        $allApplications = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->get();

        $totalApplicants = $allApplications->count();

        $applicationsThisMonth = $allApplications
            ->filter(fn($app) => $app->created_at->month === $now->month && $app->created_at->year === $now->year)
            ->count();

        $startOfMonth = $now->copy()->startOfMonth()->format('F j');
        $today = $now->format('j, Y'); 
        $thisMonthLabel = "{$startOfMonth} – {$today}";

        $hiredCount = $allApplications->where('status', 'hired')->count();
        $rejectedCount = $allApplications->where('status', 'rejected')->count();
        $interviewsCount = $allApplications->whereNotNull('interview_date')->count();

        return Inertia::render('Company/Applicants/Index', [
            'jobs' => $jobs,
            'stats' => [
                'this_month' => $applicationsThisMonth,
                'this_month_label' => $thisMonthLabel,
                'hired' => $hiredCount,
                'rejected' => $rejectedCount,
                'interviews' => $interviewsCount,
                'total_jobs' => $jobs->count(),
                'total_applicants' => $totalApplicants,
            ]
        ]);
    }

    public function show(Job $job)
    {
        $applicationsQuery = JobApplication::where('job_id', $job->id);

        // Stats
        $totalApplicants = $applicationsQuery->count();
        $hiredCount = (clone $applicationsQuery)->where('status', 'hired')->count();
        $rejectedCount = (clone $applicationsQuery)->where('status', 'rejected')->count();
        $declinedByGraduate = (clone $applicationsQuery)->where('status', 'declined')->count();
        $interviewsCount = (clone $applicationsQuery)->whereNotNull('interview_date')->count();
        $pendingCount = $totalApplicants - ($hiredCount + $rejectedCount + $declinedByGraduate);

        // Applications with user info
        $applicants = $applicationsQuery->with('user', 'job')->get()->map(function ($application) {
            return [
                'id' => $application->id,
                'name' => $application->user->graduate_first_name. ' ' . $application->user->graduate_last_name,
                'job_title' => $application->job->job_title,
                'email' => $application->user->email,
                'status' => $application->status,
                'notes' => $application->notes,
                'applied_at' => $application->applied_at ? $application->applied_at->format('M d, Y') : null,
                'interview_date' => $application->interview_date ? $application->interview_date->format('M d, Y') : null,
            ];
        });

        return Inertia::render('Company/Applicants/ListOfApplicants/Index', [
            'job' => $job,
            'applicants' => $applicants,
            'stats' => [
                'total' => $totalApplicants,
                'hired' => $hiredCount,
                'rejected' => $rejectedCount,
                'declined' => $declinedByGraduate,
                'interviews' => $interviewsCount,
                'pending' => $pendingCount,
            ],
        ]);
    }

}
