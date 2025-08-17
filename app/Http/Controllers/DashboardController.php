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

        if ($user->hasRole('company') && !$user->company ) {
            return redirect()->route('company.information');
        }
        if ($user->hasRole('institution') && !$user->institution) {
            return redirect()->route('institution.information');
        }
        if ($user->hasRole('graduate') && !$user->graduate) {
            return redirect()->route('graduate.information');
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

                // Graduates
                $graduates = Graduate::where('institution_id', $institutionId)
                    ->with('schoolYear')
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

                // <-- ADD DEBUG LOGS HERE
                \Log::info('Institution ID: ' . $institutionId);
                \Log::info('Programs:', $programs->toArray());
                \Log::info('Graduates:', $graduates->toArray());

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
                    'total_programs' => $programs->count(), // <-- ADD THIS LINE

                ];
            }
        }

        if ($user->hasRole('graduate')) {
            $hasReferralLetter = $user->graduate && $user->graduate->referral_letter_submitted;
        }

        // Calculate employed graduates per program
        $programEmploymentStats = collect($programs)
            ->map(function ($prog) use ($graduates) {
                $total = $graduates->where('program_id', $prog['id'])->count();
                $employed = $graduates->where('program_id', $prog['id'])->where('employment_status', 'Employed')->count();
                $percent = $total ? round(($employed / $total) * 100, 1) : 0;
                return [
                    'program_name' => $prog['name'],
                    'degree' => $prog['degree'],
                    'employed' => $employed,
                    'total' => $total,
                    'percent' => $percent,
                ];
            })
            ->filter(fn($stat) => $stat['total'] > 0) // <-- Only programs with graduates
            ->sortByDesc('percent')
            ->take(10)
            ->values();

        \Log::info('Top Programs Employment:', $programEmploymentStats->toArray());

        return Inertia::render('Institutions/Dashboard/InstitutionDashboard', [
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

            'kpi' => [
                'registeredEmployers' => 42,
                'activeJobListings' => 18,
                'registeredJobSeekers' => 350,
                'referralsThisMonth' => 27,
                'successfulPlacements' => 12,
                'upcomingCareerGuidance' => 3,
                'pendingEmployerRegistrations' => 2,
            ],
            'recentJobs' => [
                [
                    'title' => 'Customer Service Representative',
                    'sector' => 'BPO',
                    'employer' => 'Acme Corp',
                    'date_posted' => '2025-08-01',
                ],
                [
                    'title' => 'Sales Associate',
                    'sector' => 'Retail',
                    'employer' => 'ShopSmart',
                    'date_posted' => '2025-08-03',
                ],
                [
                    'title' => 'Production Operator',
                    'sector' => 'Manufacturing',
                    'employer' => 'MegaMakers',
                    'date_posted' => '2025-08-05',
                ],
            ],
            'expiringJobs' => [
                [
                    'title' => 'Warehouse Staff',
                    'employer' => 'LogiPro',
                    'expires_at' => '2025-08-10',
                ],
                [
                    'title' => 'IT Support',
                    'employer' => 'Techies Inc',
                    'expires_at' => '2025-08-12',
                ],
            ],
            'topSectorsChartOption' => [
                'tooltip' => ['trigger' => 'item'],
                'legend' => ['top' => '5%'],
                'series' => [
                    [
                        'name' => 'Sectors',
                        'type' => 'pie',
                        'radius' => '60%',
                        'data' => [
                            ['value' => 10, 'name' => 'BPO'],
                            ['value' => 7, 'name' => 'Retail'],
                            ['value' => 5, 'name' => 'Manufacturing'],
                            ['value' => 3, 'name' => 'Education'],
                            ['value' => 2, 'name' => 'Healthcare'],
                        ],
                    ],
                ],
            ],
            'referralTrendOption' => [
                'tooltip' => ['trigger' => 'axis'],
                'xAxis' => ['type' => 'category', 'data' => ['Jul', 'Aug']],
                'yAxis' => ['type' => 'value'],
                'series' => [
                    [
                        'name' => 'Referrals',
                        'type' => 'line',
                        'data' => [22, 27],
                    ],
                ],
            ],
            'topEmployersOption' => [
                'tooltip' => ['trigger' => 'axis'],
                'xAxis' => ['type' => 'category', 'data' => ['Acme Corp', 'ShopSmart', 'MegaMakers']],
                'yAxis' => ['type' => 'value'],
                'series' => [
                    [
                        'name' => 'Referrals',
                        'type' => 'bar',
                        'data' => [12, 8, 7],
                    ],
                ],
            ],
            'pendingReferrals' => [
                [
                    'job_seeker' => 'Juan Dela Cruz',
                    'employer' => 'Acme Corp',
                    'due_date' => '2025-08-09',
                ],
                [
                    'job_seeker' => 'Maria Santos',
                    'employer' => 'ShopSmart',
                    'due_date' => '2025-08-11',
                ],
            ],
            'upcomingEvents' => [
                [
                    'title' => 'Career Guidance Seminar',
                    'date' => '2025-08-15',
                    'venue' => 'City Hall',
                ],
                [
                    'title' => 'Job Fair',
                    'date' => '2025-08-20',
                    'venue' => 'Convention Center',
                ],
            ],
            'eventAttendanceOption' => [
                'tooltip' => ['trigger' => 'axis'],
                'xAxis' => ['type' => 'category', 'data' => ['May', 'Jun', 'Jul', 'Aug']],
                'yAxis' => ['type' => 'value'],
                'series' => [
                    [
                        'name' => 'Attendance',
                        'type' => 'bar',
                        'data' => [120, 150, 180, 90],
                    ],
                ],
            ],
            'alerts' => [
                'employersNoPermit' => ['Acme Corp', 'MegaMakers'],
                'unreviewedApplications' => ['ShopSmart', 'LogiPro'],
                'inactiveEmployers' => ['OldCo'],
            ],
            'sectorPieOption' => [
                'tooltip' => ['trigger' => 'item'],
                'legend' => ['top' => '5%'],
                'series' => [
                    [
                        'name' => 'Sectors',
                        'type' => 'pie',
                        'radius' => '50%',
                        'data' => [
                            ['value' => 15, 'name' => 'BPO'],
                            ['value' => 10, 'name' => 'Retail'],
                            ['value' => 8, 'name' => 'Manufacturing'],
                            ['value' => 5, 'name' => 'Education'],
                            ['value' => 2, 'name' => 'Healthcare'],
                        ],
                    ],
                ],
            ],
            'inDemandCategories' => [
                ['name' => 'Customer Service', 'count' => 12],
                ['name' => 'Sales', 'count' => 9],
                ['name' => 'IT Support', 'count' => 7],
                ['name' => 'Production', 'count' => 5],
                ['name' => 'Teaching', 'count' => 3],
            ],


            'recentApplications' => $recentApplications,
            'applicationTrends' => $applicationTrends,
            'jobPerformance' => $jobPerformance,
            'topProgramsEmployment' => $programEmploymentStats,

        ]);
    }

}
