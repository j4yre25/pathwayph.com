<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyJobApplicantController extends Controller
{
    public function index(User $user)
    {
        $user = Auth::user();
    
        // Get the company associated with the logged-in user
        $company = Company::where('user_id', $user->id)->firstOrFail();

        $companyId = $company->id;
        $now = Carbon::now();

        $jobs = Job::withCount('applications')
        ->with(['jobTypes:id,type',
            'locations:id,address',
            'workEnvironments:id,environment_type',
            'salary'])
        ->where('company_id', $companyId)
        ->where('status', 'open')
        ->orderBy('created_at', 'desc') // then newest
        ->get();

        // Accurate count of active jobs (open + approved)
        $activeJobCount = Job::where('company_id', $companyId)
        ->where('status', 'open')
        ->where('is_approved', true)
        ->count();
        
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
        $interviewsCount = JobApplication::whereHas('interviews', function($q) {
        })->whereHas('job', fn($q) => $q->where('company_id', $companyId))->count();

        return Inertia::render('Company/Applicants/Index', [
            'jobs' => $jobs,
            'stats' => [
                'this_month' => $applicationsThisMonth,
                'this_month_label' => $thisMonthLabel,
                'hired' => $hiredCount,
                'rejected' => $rejectedCount,
                'interviews' => $interviewsCount,
                'total_jobs' => $activeJobCount,
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
        $interviewsCount = (clone $applicationsQuery)->whereHas('interviews')->count();
        $pendingCount = $totalApplicants - ($hiredCount + $rejectedCount + $declinedByGraduate);

        $applicants = $applicationsQuery
            ->with(['graduate.user', 'job']) // eager load graduate and their user
            ->get()
            ->map(function ($application) {
                $graduate = $application->graduate;
                $user = $graduate?->user;

                return [
                    'id' => $application->id,
                    'graduate_picture' => $graduate->graduate_picture,
                    'name' => $graduate
                        ? $graduate->first_name . ' ' . $graduate->last_name
                        : 'N/A',
                    'job_title' => $application->job->job_title,
                    'email' => $user?->email ?? 'N/A',
                    'status' => $application->status,
                    'stage' => $application->stage,
                    'notes' => $application->notes,
                    'applied_at' => optional($application->applied_at)->format('M d, Y'),
                    'interview_date' => optional($application->interviews->sortByDesc('scheduled_at')->first()?->scheduled_at)->format('M d, Y'),
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