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
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ Role: Company
        if ($user->hasRole('company')) {

            if (!$user->company || !$user->has_completed_information) {
                return redirect()->route('company.information');
            }

            $data = $this->handleCompanyDashboard($user);
            return Inertia::render('Dashboard', $data);
        }

        // ✅ Role: Institution
        if ($user->hasRole('institution')) {

            if (!$user->institution) {
                return redirect()->route('institution.information');
            }

            $filters = $request->only(['school_year_id','term','gender']);
            $data = $this->handleInstitutionDashboard($user, $filters);
            return Inertia::render('Institutions/Dashboard/InstitutionDashboard', $data);
        }

        // ✅ Role: Graduate
        if ($user->hasRole('graduate')) {
            if (!$user->graduate) {
                return redirect()->route('graduate.information');
            }

            $data = $this->handleGraduateDashboard($user);
            return Inertia::render('Dashboard', $data);
        }

        // ✅ Default (Admin or others)
        return Inertia::render('Dashboard', $this->getAdminDashboardData());
    }

    /* ==========================================================
     |  COMPANY DASHBOARD
     ========================================================== */
    private function handleCompanyDashboard($user)
    {
        $company = $user->company;
        $now = now();

        $applicationsQuery = \App\Models\JobApplication::whereHas('job', fn($q) =>
            $q->where('company_id', $company->id));

        $statusCounts = [
            'pending' => (clone $applicationsQuery)->where('status', 'pending')->count(),
            'hired' => (clone $applicationsQuery)->where('status', 'hired')->count(),
            'rejected' => (clone $applicationsQuery)->where('status', 'rejected')->count(),
            'declined' => (clone $applicationsQuery)->where('status', 'declined')->count(),
        ];

        $pipelineCounts = [
            'applied' => (clone $applicationsQuery)->where('stage', 'applied')->count(),
            'screening' => (clone $applicationsQuery)->where('stage', 'screening')->count(),
            'interview' => (clone $applicationsQuery)->where('stage', 'interview')->count(),
            'offer' => (clone $applicationsQuery)->where('stage', 'offer')->count(),
        ];

        $summary = [
            'active_jobs' => Job::where('company_id', $company->id)->where('status', 'open')->count(),
            'total_applications' => $applicationsQuery->count(),
            'this_month_applications' => (clone $applicationsQuery)
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

        return [
            'summary' => $summary,
            'recentApplications' => $recentApplications,
            'applicationTrends' => $this->getApplicationTrends($company->id),
            'jobPerformance' => $this->getJobPerformance($company->id),
            'roles' => [
                'isGraduate' => false,
                'isCompany' => true,
                'isInstitution' => false,
            ],
        ];
    }

    private function getApplicationTrends($companyId)
    {
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyCounts = array_fill(1, 12, 0);

        $monthlyData = \App\Models\JobApplication::whereHas('job', fn($q) =>
                $q->where('company_id', $companyId))
            ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        foreach ($monthlyData as $row) {
            $monthlyCounts[$row->month] = $row->count;
        }

        return [
            'labels' => $months,
            'data' => array_values($monthlyCounts),
        ];
    }

    private function getJobPerformance($companyId)
    {
        return Job::where('company_id', $companyId)
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

    /* ==========================================================
     |  INSTITUTION DASHBOARD
     ========================================================== */
    private function handleInstitutionDashboard($user, $filters)
    {
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution) return [];

        $institutionId = $institution->id;

        // Programs
        $programs = InstitutionProgram::with('program', 'degree')
            ->where('institution_id', $institutionId)
            ->get()
            ->map(fn($item) => [
                'id' => $item->program->id,
                'name' => $item->program->name,
                'degree' => $item->degree->type ?? null,
            ]);

        // Graduates
        $graduates = Graduate::where('institution_id', $institutionId)
            ->when($filters['school_year_id'] ?? null, fn($q, $sy) => $q->where('school_year_id', $sy))
            ->when($filters['term'] ?? null, fn($q, $term) => $q->whereHas('schoolYear', fn($sq) => $sq->where('term', $term)))
            ->when($filters['gender'] ?? null, fn($q, $gender) => $q->where('gender', $gender))
            ->with('schoolYear')
            ->get()
            ->map(fn($g) => [
                'first_name' => $g->first_name,
                'middle_name' => $g->middle_name,
                'last_name' => $g->last_name,
                'program_id' => $g->program_id,
                'current_job_title' => $g->current_job_title,
                'employment_status' => ucfirst($g->employment_status),
                'gender' => $g->gender,
                'school_year_id' => $g->school_year_id,
                'school_year_range' => $g->schoolYear?->school_year_range,
            ]);

        // Career opportunities from graduates
        $careerOpportunities = $graduates
            ->pluck('current_job_title')
            ->filter(fn($title) => $title && $title !== 'N/A')
            ->unique()
            ->values();

        // School years
        $schoolYears = InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institutionId)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'range' => $item->schoolYear->school_year_range,
                'term' => $item->term,
            ]);

        // Institution career opportunities
        $institutionCareerOpportunities = InstitutionCareerOpportunity::with(['careerOpportunity', 'program'])
            ->where('institution_id', $institutionId)
            ->get()
            ->map(fn($ico) => [
                'id' => $ico->careerOpportunity->id,
                'title' => $ico->careerOpportunity->title,
                'program_id' => $ico->program_id,
                'program_name' => $ico->program?->name,
            ]);

        $summary = [
            'total_graduates' => $graduates->count(),
            'employed' => $graduates->where('employment_status', 'Employed')->count(),
            'underemployed' => $graduates->where('employment_status', 'Underemployed')->count(),
            'unemployed' => $graduates->where('employment_status', 'Unemployed')->count(),
            'total_programs' => $programs->count(),
        ];

        // Top programs employment
        $programEmploymentStats = collect($programs)
            ->map(fn($prog) => [
                'program_name' => $prog['name'],
                'degree' => $prog['degree'],
                'employed' => $graduates->where('program_id', $prog['id'])->where('employment_status', 'Employed')->count(),
                'total' => $graduates->where('program_id', $prog['id'])->count(),
            ])
            ->map(fn($stat) => array_merge($stat, [
                'percent' => $stat['total'] ? round(($stat['employed'] / $stat['total']) * 100, 1) : 0,
            ]))
            ->filter(fn($stat) => $stat['total'] > 0)
            ->sortByDesc('percent')
            ->take(5)
            ->values();
      
        $registeredEmployers = \App\Models\User::where('role', 'company')
            ->whereHas('company')
            ->count();

        $registeredJobSeekers = \App\Models\User::where('role', 'graduate')
            ->whereHas('graduate')
            ->count();

        $activeJobListings = \App\Models\Job::where('status', 'active')->count();


        $recentJobs = Job::where('status', 'active')
            ->with(['company', 'sector', 'category', 'locations']) // Use correct relationships
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($job) {
                return [
                    'title' => $job->job_title,
                    'sector' => $job->sector ? $job->sector->name : '-', // sector() relationship
                    'category' => $job->category ? $job->category->name : '-', // category() relationship
                    'employer' => $job->company ? $job->company->company_name : '-', // company() relationship
                    'location' => $job->locations->pluck('name')->join(', ') ?: '-',
                    'date_posted' => $job->created_at->format('Y-m-d'),
                ];
            });


        return Inertia::render('Dashboard', [

            'userNotApproved' => !$user->is_approved,
            'roles' => [
                'isGraduate' => false,
                'isCompany' => false,
                'isInstitution' => true,
            ],
            'summary' => $summary,
            'graduates' => $graduates,
            'programs' => $programs,
            'careerOpportunities' => $careerOpportunities,
            'schoolYears' => $schoolYears,
            'institutionCareerOpportunities' => $institutionCareerOpportunities,
            'selectedSchoolYear' => $filters['school_year_id'] ?? null,
            'selectedTerm' => $filters['term'] ?? null,
            'selectedGender' => $filters['gender'] ?? null,
            'topProgramsEmployment' => $programEmploymentStats,
        ]);
    }

    /* ==========================================================
     |  GRADUATE DASHBOARD
     ========================================================== */
    private function handleGraduateDashboard($user)
    {
        return [
            'hasReferralLetter' => $user->graduate && $user->graduate->referral_letter_submitted,
            'roles' => [
                'isGraduate' => true,
                'isCompany' => false,
                'isInstitution' => false,
            ],
        ];
    }

    /* ==========================================================
     |  ADMIN / DEFAULT DASHBOARD
     ========================================================== */

private function getAdminDashboardData()
{
    // KPIs
    $registeredEmployers = \App\Models\User::where('role', 'company')
        ->whereHas('company')
        ->count();

    $registeredJobSeekers = \App\Models\User::where('role', 'graduate')
        ->whereHas('graduate')
        ->count();

    $activeJobListings = \App\Models\Job::where('status', 'active')->count();

    $recentJobs = Job::where('status', 'active')
        ->with(['company', 'sector', 'category', 'locations'])
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get()
        ->map(function ($job) {
            return [
                'title' => $job->job_title,
                'sector' => $job->sector ? $job->sector->name : '-',
                'category' => $job->category ? $job->category->name : '-',
                'employer' => $job->company ? $job->company->company_name : '-',
                'location' => $job->locations->pluck('name')->join(', ') ?: '-',
                'date_posted' => $job->created_at->format('Y-m-d'),
            ];
        });

    return [
        'userNotApproved' => !Auth::user()->is_approved,
        'roles' => [
            'isGraduate' => false,
            'isCompany' => false,
            'isInstitution' => false,
        ],
        'kpi' => [
            'registeredEmployers' => $registeredEmployers,
            'activeJobListings' => $activeJobListings,
            'registeredJobSeekers' => $registeredJobSeekers,
            'referralsThisMonth' => 0,
            'successfulPlacements' => 0,
            'upcomingCareerGuidance' => 0,
            'pendingEmployerRegistrations' => 0,
        ],
        'recentJobs' => $recentJobs,
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
        'expiringJobs' => [
            ['title' => 'Warehouse Staff','employer' => 'LogiPro','expires_at' => '2025-08-10'],
            ['title' => 'IT Support','employer' => 'Techies Inc','expires_at' => '2025-08-12'],
        ],
        'topSectorsChartOption' => [
            'tooltip' => ['trigger' => 'item'],
            'legend' => ['top' => '5%'],
            'series' => [[
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
            ]],
        ],
        // ... keep the rest of your admin charts and alerts
    ];
}
}