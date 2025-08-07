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
        $now = now();

        $activeJobs = Job::where('company_id', $company->id)
            ->where('status', 'open');

        $applicationsQuery = JobApplication::whereHas('job', fn($q) =>
            $q->where('company_id', $company->id));

        $statusCounts = [
            'pending' => $applicationsQuery->clone()->where('status', 'pending')->count(),
            'hired' => $applicationsQuery->clone()->where('status', 'hired')->count(),
            'rejected' => $applicationsQuery->clone()->where('status', 'rejected')->count(),
            'declined' => $applicationsQuery->clone()->where('status', 'declined')->count(),
        ];

        $pipelineCounts = [
            'applied' => $applicationsQuery->clone()->where('stage', 'applied')->count(),
            'screening' => $applicationsQuery->clone()->where('stage', 'screening')->count(),
            'interview' => $applicationsQuery->clone()->where('stage', 'interview')->count(),
            'offer' => $applicationsQuery->clone()->where('stage', 'offer')->count(),
        ];

        return Inertia::render('Company/CompanyDashboard', [
            'summary' => [
                'active_jobs' => $activeJobs->count(),
                'total_applications' => $applicationsQuery->count(),
                'this_month_applications' => $applicationsQuery->clone()
                    ->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->count(),
                'total_hires' => $statusCounts['hired'],
                'pipeline' => $pipelineCounts,
                'status_counts' => $statusCounts,
                'new_applications' => $pipelineCounts['applied'],
                'screening' => $pipelineCounts['screening'],
                'in_interview' => $pipelineCounts['interview'],
                'in_offer' => $pipelineCounts['offer'],
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
