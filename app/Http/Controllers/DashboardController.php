<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\InstitutionProgram;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Job;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $summary = [
            'total_jobs' => 0,
            'total_applications' => 0,
            'total_hires' => 0,
        ];

        $graduates = [];
        $programs = [];
        $careerOpportunities = [];

        if ($user->hasRole('institution')) {
            $institutionId = $user->id;

            $summary = [
                'total_graduates' => Graduate::where('institution_id', $institutionId)->count(),
                'employed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'employed')->count(),
                'underemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'underemployed')->count(),
                'unemployed' => Graduate::where('institution_id', $institutionId)->where('employment_status', 'unemployed')->count(),
            ];

            // Get institution-specific programs
            $programs = InstitutionProgram::with('program')
                ->where('institution_id', $institutionId)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->program->id,
                        'name' => $item->program->name,
                        'code' => $item->program_code,
                        'degree' => $item->degree->type ?? null,
                    ];
                });

            // Graduates
            $graduates = Graduate::where('institution_id', $institutionId)->get();

            // Career Opportunities
            $careerOpportunities = $graduates
                ->whereNotNull('current_job_title')
                ->where('current_job_title', '!=', 'N/A')
                ->pluck('current_job_title')
                ->unique()
                ->values();
        }

        return Inertia::render('Dashboard', [
            'userNotApproved' => !$user->is_approved,
            'roles' => [
                'isCompany' => $user->hasRole('company'),
                'isInstitution' => $user->hasRole('institution'),
            ],
            'summary' => $summary,
            'graduates' => $graduates,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
        ]);
    }

    public function companyStats()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('company')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $companyId = $user->id;

        // Job Openings Overview
        $jobStats = [
            'total_job_openings' => Job::where('company_id', $companyId)->count(),
            'active_listings' => Job::where('company_id', $companyId)
                ->where('status', 'active')
                ->count(),
            'roles_filled' => Job::where('company_id', $companyId)
                ->where('status', 'filled')
                ->count(),
        ];

        // Job Type Distribution
        $jobTypeDistribution = Job::where('company_id', $companyId)
            ->select('job_type', DB::raw('count(*) as count'))
            ->groupBy('job_type')
            ->get()
            ->map(fn($item) => [
                'label' => $item->job_type,
                'value' => $item->count
            ]);

        // Get all job IDs for this company
        $companyJobIds = Job::where('company_id', $companyId)->pluck('id');

        // Applicant Status Overview
        $applicationStats = [
            'total_applications' => Application::whereIn('job_id', $companyJobIds)->count(),
            'shortlisted' => Application::whereIn('job_id', $companyJobIds)
                ->where('status', 'shortlisted')
                ->count(),
            'hired' => Application::whereIn('job_id', $companyJobIds)
                ->where('status', 'hired')
                ->count(),
        ];

        // Applicant Status Distribution
        $applicantStatusDistribution = Application::whereIn('job_id', $companyJobIds)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'label' => ucfirst($item->status),
                'value' => $item->count
            ]);

        // Graduate Pool Overview
        $graduateStats = [
            'total_scouted' => Graduate::whereHas('applications', function($query) use ($companyJobIds) {
                $query->whereIn('job_id', $companyJobIds);
            })->count(),
            'matched' => Graduate::whereHas('applications', function($query) use ($companyJobIds) {
                $query->whereIn('job_id', $companyJobIds)
                    ->where('status', 'matched');
            })->count(),
            'avg_qualification' => Graduate::whereHas('applications', function($query) use ($companyJobIds) {
                $query->whereIn('job_id', $companyJobIds);
            })->avg('qualification_score') ?? 0,
        ];

        // Graduates by Study Field
        $graduatesByField = Graduate::whereHas('applications', function($query) use ($companyJobIds) {
            $query->whereIn('job_id', $companyJobIds);
        })
            ->select('field_of_study', DB::raw('count(*) as count'))
            ->groupBy('field_of_study')
            ->get()
            ->map(fn($item) => [
                'label' => $item->field_of_study,
                'value' => $item->count
            ]);

        // Application Trend (last 12 months)
        $applicationTrend = Application::whereIn('job_id', $companyJobIds)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(fn($item) => [
                'label' => $item->month,
                'value' => $item->count
            ]);

        // Average Stage Duration
        $stageDuration = Application::whereIn('job_id', $companyJobIds)
            ->select(
                'current_stage',
                DB::raw('avg(DATEDIFF(updated_at, created_at)) as avg_days')
            )
            ->groupBy('current_stage')
            ->get()
            ->map(fn($item) => [
                'label' => ucfirst($item->current_stage),
                'value' => round($item->avg_days, 1)
            ]);

        return response()->json([
            ...$jobStats,
            'job_type_distribution' => $jobTypeDistribution,
            ...$applicationStats,
            'applicant_status_distribution' => $applicantStatusDistribution,
            ...$graduateStats,
            'graduates_by_study_field' => $graduatesByField,
            'application_trend_by_time' => $applicationTrend,
            'average_stage_duration' => $stageDuration,
        ]);
    }
}
