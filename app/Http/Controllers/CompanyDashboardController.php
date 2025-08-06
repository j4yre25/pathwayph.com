<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyDashboardController extends Controller
{
    /**
     * Show the company dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $company = auth()->user()->company;
        $now = Carbon::now();

        return Inertia::render('Company/CompanyDashboard', [
            'summary' => [
                'total_jobs' => Job::where('company_id', $company->id)->count(),
                'total_applications' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->count(),
                'total_interviews' => Interview::whereHas('jobApplication.job', fn($q) => 
                    $q->where('company_id', $company->id))->count(),
                'total_hires' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->where('status', 'hired')->count(),
                'new_applications' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->where('status', 'pending')->count(),
                'screening' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->where('stage', 'screening')->count(),
                'in_interview' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->where('stage', 'interview')->count(),
                'in_offer' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))->where('stage', 'offer')->count(),
            ],
            'recentApplications' => JobApplication::with(['graduate', 'job'])
                ->whereHas('job', fn($q) => $q->where('company_id', $company->id))
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($app) => [
                    'id' => $app->id,
                    'applicant_name' => $app->graduate->full_name,
                    'position' => $app->job->job_title,
                    'status' => $app->status,
                    'applied_date' => $app->created_at->format('M d, Y')
                ]),
            'applicationTrends' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => JobApplication::whereHas('job', fn($q) => 
                    $q->where('company_id', $company->id))
                    ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('month')
                    ->get()
                    ->pluck('count')
                    ->toArray()
            ],
            'jobPerformance' => Job::where('company_id', $company->id)
                ->withCount(['applications', 'interviews'])
                ->get()
                ->map(fn($job) => [
                    'id' => $job->id,
                    'title' => $job->job_title,
                    'applications' => $job->applications_count,
                    'interview_rate' => $job->applications_count > 0 
                        ? round(($job->interviews_count / $job->applications_count) * 100) 
                        : 0
                ])
                ->sortByDesc('interview_rate')
                ->take(5)
                ->values()
                ->toArray()
        ]);
    }
}
