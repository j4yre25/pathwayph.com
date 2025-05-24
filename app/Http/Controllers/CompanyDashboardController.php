<?php

namespace App\Http\Controllers;

use App\Models\Job;
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
        $now = Carbon::now();

        $totalJobs = Job::count();

        $activeJobs = Job::where('status', 'active')
            ->where('job_deadline', '>', $now)
            ->count();

        $filledJobs = Job::withCount('applications')
            ->whereNotNull('job_application_limit')
            ->get()
            ->filter(function ($job) {
                return $job->applications_count >= $job->job_application_limit;
            })->count();

        $jobTypeDistribution = Job::select('job_employment_type', DB::raw('count(*) as total'))
            ->groupBy('job_employment_type')
            ->get();

        return Inertia::render('Dashboard', [
            'kpis' => [
                'total' => $totalJobs,
                'active' => $activeJobs,
                'filled' => $filledJobs,
            ],
            'jobTypes' => $jobTypeDistribution,
        ]);
    }
}    
