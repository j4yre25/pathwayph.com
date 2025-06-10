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
use App\Models\InstitutionSchoolYear;
use App\Models\SchoolYear;
use App\Models\InstitutionCareerOpportunity;
use App\Models\CareerOpportunity;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Always initialize variables
        $summary = [
            'total_graduates' => 0,
            'employed' => 0,
            'underemployed' => 0,
            'unemployed' => 0,
        ];
        $graduates = collect();
        $programs = collect();
        $careerOpportunities = collect();
        $schoolYears = collect();
        $institutionCareerOpportunities = collect();

        if ($user->hasRole('institution')) {
            $institution = Institution::where('user_id', $user->id)->first();

            if ($institution) {
                $institutionId = $institution->id;

                // Programs
                $programs = InstitutionProgram::with('program', 'degree')
                    ->where('institution_id', $institutionId)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->program->id,
                            'name' => $item->program->name,
                            'degree' => $item->degree->type ?? null,
                        ];
                    });

                // Graduates (with all fields needed for dashboard)
                $graduates = Graduate::where('institution_id', $institutionId)
                    ->with('schoolYear') // eager load the schoolYear relation
                    ->get()
                    ->map(function ($g) {
                        return [
                            'first_name' => $g->first_name,
                            'middle_name' => $g->middle_name,
                            'last_name' => $g->last_name,
                            'program_id' => $g->program_id,
                            'current_job_title' => $g->current_job_title,
                            'employment_status' => ucfirst($g->employment_status),
                            'gender' => $g->gender,
                            'school_year_id' => $g->school_year_id,
                            'school_year_range' => $g->schoolYear ? $g->schoolYear->school_year_range : null,
                        ];
                    });

                // Career Opportunities (unique job titles)
                $careerOpportunities = $graduates
                    ->pluck('current_job_title')
                    ->filter(fn($title) => $title && $title !== 'N/A')
                    ->unique()
                    ->values();

                // Get all school years for this institution (with range)
                $schoolYears = InstitutionSchoolYear::with('schoolYear')
                    ->where('institution_id', $institutionId)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->schoolYear->id,
                            'range' => $item->schoolYear->school_year_range,
                            'term' => $item->term,
                        ];
                    });

                // Get all institution career opportunities with program info
                $institutionCareerOpportunities = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])
                    ->where('institution_id', $institutionId)
                    ->get()
                    ->map(function ($ico) {
                        return [
                            'id' => $ico->careerOpportunity->id,
                            'title' => $ico->careerOpportunity->title,
                            'program_id' => $ico->program_id,
                            'program_name' => $ico->program ? $ico->program->name : null,
                        ];
                    });

                $summary = [
                    'total_graduates' => $graduates->count(),
                    'employed' => $graduates->where('employment_status', 'Employed')->count(),
                    'underemployed' => $graduates->where('employment_status', 'Underemployed')->count(),
                    'unemployed' => $graduates->where('employment_status', 'Unemployed')->count(),
                ];
            }
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
            'schoolYears' => $schoolYears,
            'institutionCareerOpportunities' => $institutionCareerOpportunities,
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
