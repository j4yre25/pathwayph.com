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

        if ($user->hasRole('company') && !$user->company) {
            return redirect()->route('company.information');
        }

        // Default values for all props
        $summary = [];
        $graduates = collect();
        $programs = collect();
        $careerOpportunities = collect();
        $schoolYears = collect();
        $institutionCareerOpportunities = collect();
        $recentApplications = collect();
        $applicationTrends = [];
        $jobPerformance = [];
        $hasReferralLetter = false;

        if ($user->hasRole('company')) {
            $company = $user->company;
            $now = now();

            $activeJobs = Job::where('company_id', $company->id)
                ->where('status', 'open');

            $applicationsQuery = \App\Models\JobApplication::whereHas('job', fn($q) =>
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

            $summary = [
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
            ];

            $recentApplications = \App\Models\JobApplication::with(['graduate', 'job'])
                ->whereHas('job', fn($q) => $q->where('company_id', $company->id))
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($app) => [
                    'id' => $app->id,
                    'applicant_name' => $app->graduate->first_name . ' ' . $app->graduate->last_name,
                    'position' => $app->job->job_title,
                    'status' => $app->status,
                    'applied_date' => $app->created_at->format('M d, Y')
                ]);

            // Application trends by month
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $monthlyCounts = array_fill(1, 12, 0);
            $monthlyData = \App\Models\JobApplication::whereHas('job', fn($q) =>
                    $q->where('company_id', $company->id))
                ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get();
            foreach ($monthlyData as $row) {
                $monthlyCounts[$row->month] = $row->count;
            }
            $applicationTrends = [
                'labels' => $months,
                'data' => array_values($monthlyCounts),
            ];

            $jobPerformance = Job::where('company_id', $company->id)
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
                ->toArray();
        }

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

        if ($user->hasRole('graduate')) {
            $hasReferralLetter = $user->graduate && $user->graduate->referral_letter_submitted;
        }

        return Inertia::render('Dashboard', [
            'userNotApproved' => !$user->is_approved,
            'hasReferralLetter' => $hasReferralLetter ?? false,
            'roles' => [
                'isGraduate' => $user->hasRole('graduate'),
                'isCompany' => $user->hasRole('company'),
                'isInstitution' => $user->hasRole('institution'),
            ],
            'summary' => $summary,
            'graduates' => $graduates,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'schoolYears' => $schoolYears,
            'institutionCareerOpportunities' => $institutionCareerOpportunities,
            'recentApplications' => $recentApplications,
            'applicationTrends' => $applicationTrends,
            'jobPerformance' => $jobPerformance,
        ]);
    }

}
