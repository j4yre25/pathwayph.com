<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\InstitutionProgram;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Models\Job;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionSchoolYear;
use App\Models\SchoolYear;
use App\Models\InstitutionCareerOpportunity;
use App\Models\SeminarRequest;
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

            if (!$user->institution || !$user->has_completed_information) {
                return redirect()->route('institution.information');
            }

            $filters = $request->only(['school_year_id', 'term', 'gender']);
            $data = $this->handleInstitutionDashboard($user, $filters);
            return Inertia::render('Institutions/Dashboard/InstitutionDashboard', $data);
        }

        // ✅ Role: Graduate
        if ($user->hasRole('graduate')) {
            // Eager load the graduate (and user for fallback profile picture if needed)
            $user->load('graduate.user');

            if (!$user->graduate || !$user->has_completed_information) {
                return redirect()->route('graduate.information');
            }

            $data = $this->handleGraduateDashboard($user);
            return Inertia::render('Dashboard', $data);
        }

        // ✅ Default (Admin or others)
        return Inertia::render('Dashboard', $this->getAdminDashboardData($request));
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
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
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
     ==========================================================+ */
    private function handleInstitutionDashboard($user, $filters)
    {
        $institution = Institution::where('user_id', $user->id)->first();
        if (!$institution)
            return [];

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

        // School years
        $schoolYears = InstitutionSchoolYear::with('schoolYear')
            ->where('institution_id', $institutionId)
            ->get()
            ->map(fn($item) => [
                'id' => $item->schoolYear->id,
                'range' => $item->schoolYear->school_year_range,
                'term' => $item->term,
            ]);

        // Get selected school year range and term from filters
        $schoolYearId = $filters['school_year_id'] ?? null;
        $term = $filters['term'] ?? null;

        // Find the selected school year range value
        $selectedSchoolYearRange = null;
        if ($schoolYearId) {
            $selectedSchoolYear = $schoolYears->firstWhere('id', $schoolYearId);
            $selectedSchoolYearRange = $selectedSchoolYear ? $selectedSchoolYear['range'] : null;
        }

        // Graduates query with school year and term filter (like GraduateReports)
        $graduatesQuery = Graduate::where('institution_id', $institutionId)
            ->with(['schoolYear', 'institutionSchoolYear'])
            ->join('users', 'graduates.user_id', '=', 'users.id') // Join users table
            ->where('users.is_approved', true) // Only approved users
            ->whereNull('graduates.deleted_at'); // Not archived

        if ($selectedSchoolYearRange && $term) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($selectedSchoolYearRange, $term) {
                $q->whereHas('schoolYear', function ($q2) use ($selectedSchoolYearRange) {
                    $q2->where('school_year_range', $selectedSchoolYearRange);
                })->where('term', $term);
            });
        } elseif ($selectedSchoolYearRange) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($selectedSchoolYearRange) {
                $q->whereHas('schoolYear', function ($q2) use ($selectedSchoolYearRange) {
                    $q2->where('school_year_range', $selectedSchoolYearRange);
                });
            });
        } elseif ($term) {
            $graduatesQuery->whereHas('institutionSchoolYear', function ($q) use ($term) {
                $q->where('term', $term);
            });
        }

        if ($filters['gender'] ?? null) {
            $graduatesQuery->where('gender', $filters['gender']);
        }

        $graduates = $graduatesQuery->get()
            ->map(fn($g) => [
                'first_name' => $g->first_name,
                'middle_name' => $g->middle_name,
                'last_name' => $g->last_name,
                'program_id' => $g->program_id,
                'current_job_title' => $g->current_job_title,
                'employment_status' => ucfirst($g->employment_status),
                'gender' => $g->gender,
                'school_year_id' => $g->school_year_id,
                'school_year_range' => $g->institutionSchoolYear?->schoolYear?->school_year_range,
                'term' => $g->institutionSchoolYear?->term,
            ]);

        // Career opportunities from graduates
        $careerOpportunities = $graduates
            ->pluck('current_job_title')
            ->filter(fn($title) => $title && $title !== 'N/A')
            ->unique()
            ->values();

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

        $activeJobListings = \App\Models\Job::where('status', 'open')->count();

        $recentJobs = Job::where('status', 'open')
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

        return [
            'userNotApproved' => !$user->is_approved,
            'roles' => [
                'isGraduate' => false,
                'isCompany' => false,
                'isInstitution' => true,
            ],
            'registeredEmployers' => $registeredEmployers,
            'registeredJobSeekers' => $registeredJobSeekers,
            'activeJobListings' => $activeJobListings,
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
        ];
    }



    /* ==========================================================
     |  GRADUATE DASHBOARD
     ========================================================== */
    public function handleGraduateDashboard($user)
    {
        $graduate = $user->graduate;

        $applicationIds = \App\Models\JobApplication::where('graduate_id', $graduate->id)->pluck('id');

        // Detect action key column (supports either 'action_key' or 'action')
        $actionTableExists = Schema::hasTable('job_application_action_logs');
        $actionCol = $actionTableExists
            ? (Schema::hasColumn('job_application_action_logs', 'action_key') ? 'action_key'
                : (Schema::hasColumn('job_application_action_logs', 'action') ? 'action' : null))
            : null;

        // Example queries, adjust as needed
        $jobsApplied = \App\Models\JobApplication::where('graduate_id', $graduate->id)->count();
        $referralsMade = \App\Models\Referral::where('graduate_id', $graduate->id)->count();
        $interviewsScheduled = 0;
        if ($actionCol) {
            $interviewsScheduled = DB::table('job_application_action_logs')
                ->whereIn('job_application_id', $applicationIds)
                ->whereIn($actionCol, ['schedule_interview', 'reschedule_interview'])
                ->count();
        }
        // Jobs Aligned (based on recommendations)
        $graduateSkills = \App\Models\GraduateSkill::where('graduate_id', $graduate->id)
            ->with('skill')
            ->get()
            ->pluck('skill.name')
            ->filter()
            ->unique()
            ->toArray();

        $education = \App\Models\GraduateEducation::where('graduate_id', $graduate->id)->first();
        $program = $education ? $education->program : null;

        $experiences = \App\Models\Experience::where('graduate_id', $graduate->id)->get();
        $experienceTitles = $experiences->pluck('job_title')->filter()->unique()->toArray();

        $preferences = \App\Models\EmploymentPreference::where('graduate_id', $graduate->id)->first();
        $preferredJobTypes = $preferences && $preferences->job_type ? explode(',', $preferences->job_type) : [];
        $preferredLocations = $preferences && $preferences->location ? explode(',', $preferences->location) : [];
        $preferredWorkEnvironments = $preferences && $preferences->work_environment ? explode(',', $preferences->work_environment) : [];
        $minSalary = $preferences && $preferences->employment_min_salary ? $preferences->employment_min_salary : null;
        $maxSalary = $preferences && $preferences->employment_max_salary ? $preferences->employment_max_salary : null;
        $salaryType = $preferences && $preferences->salary_type ? $preferences->salary_type : null;

        $pastKeywords = \App\Models\JobSearchHistory::where('graduate_id', $graduate->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('keywords')
            ->unique()
            ->toArray();

        $dateFilter = request('date_filter', 'this_month');

        if ($dateFilter === 'this_year') {
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            $appsByMonth = \App\Models\JobApplication::where('graduate_id', $graduate->id)
                ->whereYear('created_at', now()->year)
                ->selectRaw('MONTH(created_at) as period, COUNT(*) as cnt')
                ->groupBy('period')
                ->pluck('cnt', 'period')
                ->all();

            $interviewsByMonth = [];
            $rejectedByMonth = [];
            if ($actionCol) {
                $interviewsByMonth = DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['schedule_interview', 'reschedule_interview'])
                    ->whereYear('created_at', now()->year)
                    ->selectRaw('MONTH(created_at) as period, COUNT(*) as cnt')
                    ->groupBy('period')
                    ->pluck('cnt', 'period')
                    ->all();

                $rejectedByMonth = DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['reject', 'reject_withdraw'])
                    ->whereYear('created_at', now()->year)
                    ->selectRaw('MONTH(created_at) as period, COUNT(*) as cnt')
                    ->groupBy('period')
                    ->pluck('cnt', 'period')
                    ->all();
            }

            $applicationSent = [];
            $interviews = [];
            $rejected = [];
            for ($m = 1; $m <= 12; $m++) {
                $applicationSent[] = (int) ($appsByMonth[$m] ?? 0);
                $interviews[] = (int) ($interviewsByMonth[$m] ?? 0);
                $rejected[] = (int) ($rejectedByMonth[$m] ?? 0);
            }

            $vacancyStats = [
                'labels' => $labels,
                'applicationSent' => $applicationSent,
                'interviews' => $interviews,
                'rejected' => $rejected,
            ];
        } elseif ($dateFilter === 'this_week') {
            $startOfWeek = now()->startOfWeek();
            $labels = [];
            $applicationSent = [];
            $interviews = [];
            $rejected = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $labels[] = $date->format('M d');

                $applicationSent[] = (int) \App\Models\JobApplication::where('graduate_id', $graduate->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->count();

                $interviews[] = $actionCol ? (int) DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['schedule_interview', 'reschedule_interview'])
                    ->whereDate('created_at', $date->toDateString())
                    ->count() : 0;

                $rejected[] = $actionCol ? (int) DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['reject', 'reject_withdraw'])
                    ->whereDate('created_at', $date->toDateString())
                    ->count() : 0;
            }

            $vacancyStats = [
                'labels' => $labels,
                'applicationSent' => $applicationSent,
                'interviews' => $interviews,
                'rejected' => $rejected,
            ];
        } else {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $labels = [];
            $applicationSent = [];
            $interviews = [];
            $rejected = [];

            $weekStart = $startOfMonth->copy();
            $weekIndex = 1;
            while ($weekStart->lte($endOfMonth) && $weekIndex <= 5) {
                $weekEnd = min($weekStart->copy()->endOfWeek(), $endOfMonth);
                $labels[] = 'Week ' . $weekIndex;

                $applicationSent[] = (int) \App\Models\JobApplication::where('graduate_id', $graduate->id)
                    ->whereBetween('created_at', [$weekStart->toDateTimeString(), $weekEnd->toDateTimeString()])
                    ->count();

                $interviews[] = $actionCol ? (int) DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['schedule_interview', 'reschedule_interview'])
                    ->whereBetween('created_at', [$weekStart->toDateTimeString(), $weekEnd->toDateTimeString()])
                    ->count() : 0;

                $rejected[] = $actionCol ? (int) DB::table('job_application_action_logs')
                    ->whereIn('job_application_id', $applicationIds)
                    ->whereIn($actionCol, ['reject', 'reject_withdraw'])
                    ->whereBetween('created_at', [$weekStart->toDateTimeString(), $weekEnd->toDateTimeString()])
                    ->count() : 0;

                $weekStart = $weekEnd->copy()->addDay()->startOfDay();
                $weekIndex++;
            }

            $vacancyStats = [
                'labels' => $labels,
                'applicationSent' => $applicationSent,
                'interviews' => $interviews,
                'rejected' => $rejected,
            ];
        }

        $jobs = Job::with(['company', 'jobTypes', 'locations', 'salary'])->get();

        $jobsAligned = 0;

        foreach ($jobs as $job) {
            $labels = [];
            $score = 0;
            $criteria = 0;

            // Skills
            $criteria++;
            $skillMatch = false;
            foreach ($graduateSkills as $skill) {
                if (stripos(json_encode($job->skills), $skill) !== false) {
                    $labels[] = 'Skills';
                    $skillMatch = true;
                    break;
                }
            }
            if ($skillMatch)
                $score++;

            // Education
            $criteria++;
            $educationMatch = $program && stripos($job->job_requirements, $program) !== false;
            if ($educationMatch) {
                $labels[] = 'Education';
                $score++;
            }

            // Experience
            $criteria++;
            $experienceMatch = false;
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) {
                    $labels[] = 'Experience';
                    $experienceMatch = true;
                    break;
                }
            }
            if ($experienceMatch)
                $score++;

            // Preferred Job Type
            $criteria++;
            $jobTypeMatch = in_array($job->job_type, $preferredJobTypes);
            if ($jobTypeMatch) {
                $labels[] = 'Preferred Job Type';
                $score++;
            }

            // Preferred Location
            $criteria++;
            $locationMatch = in_array($job->location, $preferredLocations);
            if ($locationMatch) {
                $labels[] = 'Preferred Location';
                $score++;
            }

            // Preferred Work Environment
            $criteria++;
            $workEnvMatch = in_array($job->work_environment, $preferredWorkEnvironments);
            if ($workEnvMatch) {
                $labels[] = 'Preferred Work Environment';
                $score++;
            }

            // Preferred Min Salary
            $criteria++;
            $minSalaryMatch = $minSalary && $job->job_min_salary >= $minSalary;
            if ($minSalaryMatch) {
                $labels[] = 'Preferred Min Salary';
                $score++;
            }

            // Preferred Max Salary
            $criteria++;
            $maxSalaryMatch = $maxSalary && $job->job_max_salary <= $maxSalary;
            if ($maxSalaryMatch) {
                $labels[] = 'Preferred Max Salary';
                $score++;
            }

            // Preferred Salary Type
            $criteria++;
            $salaryTypeMatch = $salaryType && stripos($job->job_salary_type, $salaryType) !== false;
            if ($salaryTypeMatch) {
                $labels[] = 'Preferred Salary Type';
                $score++;
            }

            // Past Search Keywords
            $criteria++;
            $pastKeywordMatch = false;
            foreach ($pastKeywords as $keyword) {
                if (
                    stripos($job->job_title, $keyword) !== false ||
                    stripos($job->job_description, $keyword) !== false
                ) {
                    $labels[] = 'Past Search';
                    $pastKeywordMatch = true;
                    break;
                }
            }
            if ($pastKeywordMatch)
                $score++;

            // Only include jobs with at least one label (i.e., a match)
            if (!empty($labels)) {
                $jobsAligned++;
            }
        }

        // $recommendedJobs [] = [
        //     'id' => $job->id,
        //     'job_title' => $job->job_title,
        //     'company' => [
        //         'company_name' => $job->company?->company_name,
        //         'id' => $job->company?->id,
        //         'logo' => $job->company?->logo,
        //     ],
        //     'job_description' => $job->job_description,
        //     'job_roles' => $job->job_roles,
        //     'job_responsibilities' => $job->job_responsibilities,
        //     'job_qualifications' => $job->job_qualifications,
        //     'job_benefits' => $job->job_benefits,
        //     'skills' => $job->skills,
        //     'job_experience_level' => $job->job_experience_level,
        //     'jobTypes' => $job->jobTypes ? $job->jobTypes->map(fn($jt) => ['type' => $jt->type])->toArray() : [],
        //     'salary' => $job->salary ? [
        //         'job_min_salary' => $job->salary->job_min_salary,
        //         'job_max_salary' => $job->salary->job_max_salary,
        //         'salary_type' => $job->salary->salary_type,
        //     ] : null,
        //     'locations' => $job->locations ? $job->locations->map(fn($loc) => ['address' => $loc->address])->toArray() : [],
        //     'posted_at' => $job->created_at->diffForHumans(),
        //     'match_labels' => $labels,
        //     'match_percentage' => isset($score, $criteria) && $criteria ? round(($score / $criteria) * 100) : null,
        // ];

        $recommendedJobs = [];
        foreach ($jobs as $job) {
            $labels = [];
            // Skills
            foreach ($graduateSkills as $skill) {
                if (stripos(json_encode($job->skills), $skill) !== false) {
                    $labels[] = 'Skills';
                    break;
                }
            }
            // Education
            if ($program && stripos($job->job_requirements, $program) !== false) {
                $labels[] = 'Education';
            }
            // Experience
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) {
                    $labels[] = 'Experience';
                    break;
                }
            }
            // Preferred Job Type
            if (in_array($job->job_type, $preferredJobTypes)) {
                $labels[] = 'Preferred Job Type';
            }
            // Preferred Location
            if (in_array($job->location, $preferredLocations)) {
                $labels[] = 'Preferred Location';
            }
            // Preferred Work Environment
            if (in_array($job->work_environment, $preferredWorkEnvironments)) {
                $labels[] = 'Preferred Work Environment';
            }
            // Preferred Min Salary
            if ($minSalary && $job->job_min_salary >= $minSalary) {
                $labels[] = 'Preferred Min Salary';
            }
            // Preferred Max Salary
            if ($maxSalary && $job->job_max_salary <= $maxSalary) {
                $labels[] = 'Preferred Max Salary';
            }
            // Preferred Salary Type
            if ($salaryType && stripos($job->job_salary_type, $salaryType) !== false) {
                $labels[] = 'Preferred Salary Type';
            }
            // Past Search Keywords
            foreach ($pastKeywords as $keyword) {
                if (
                    stripos($job->job_title, $keyword) !== false ||
                    stripos($job->job_description, $keyword) !== false
                ) {
                    $labels[] = 'Past Search';
                    break;
                }
            }

            if (!empty($labels)) {
                $recommendedJobs[] = [
                    'id' => $job->id,
                    'job_title' => $job->job_title,
                    'company' => [
                        'company_name' => $job->company?->company_name,
                        'id' => $job->company?->id,
                        'logo' => $job->company?->logo,
                    ],
                    'job_description' => $job->job_description,
                    'job_roles' => $job->job_roles,
                    'job_responsibilities' => $job->job_responsibilities,
                    'job_qualifications' => $job->job_qualifications,
                    'job_benefits' => $job->job_benefits,
                    'skills' => $job->skills,
                    'job_experience_level' => $job->job_experience_level,
                    'jobTypes' => $job->jobTypes ? $job->jobTypes->map(fn($jt) => ['type' => $jt->type])->toArray() : [],
                    'salary' => $job->salary ? [
                        'job_min_salary' => $job->salary->job_min_salary,
                        'job_max_salary' => $job->salary->job_max_salary,
                        'salary_type' => $job->salary->salary_type,
                    ] : null,
                    'location' => $job->locations->pluck('address')->join(', '),
                    'posted_at' => $job->created_at->diffForHumans(),
                    'match_labels' => $labels,
                    'match_percentage' => isset($score, $criteria) && $criteria ? round(($score / $criteria) * 100) : null,

                ];
            }
        }

        $featuredCompanies = collect();

        foreach ($jobs as $job) {
            $labels = [];
            // Skills
            foreach ($graduateSkills as $skill) {
                if (stripos(json_encode($job->skills), $skill) !== false) {
                    $labels[] = 'Skills';
                    break;
                }
            }
            // Education
            if ($program && stripos($job->job_requirements, $program) !== false) {
                $labels[] = 'Education';
            }
            // Experience
            foreach ($experienceTitles as $title) {
                if (stripos($job->job_title, $title) !== false) {
                    $labels[] = 'Experience';
                    break;
                }
            }
            // Preferred Job Type
            if (in_array($job->job_type, $preferredJobTypes)) {
                $labels[] = 'Preferred Job Type';
            }
            // Preferred Location
            if (in_array($job->location, $preferredLocations)) {
                $labels[] = 'Preferred Location';
            }
            // Preferred Work Environment
            if (in_array($job->work_environment, $preferredWorkEnvironments)) {
                $labels[] = 'Preferred Work Environment';
            }
            // Preferred Min Salary
            if ($minSalary && $job->job_min_salary >= $minSalary) {
                $labels[] = 'Preferred Min Salary';
            }
            // Preferred Max Salary
            if ($maxSalary && $job->job_max_salary <= $maxSalary) {
                $labels[] = 'Preferred Max Salary';
            }
            // Preferred Salary Type
            if ($salaryType && stripos($job->job_salary_type, $salaryType) !== false) {
                $labels[] = 'Preferred Salary Type';
            }
            // Past Search Keywords
            foreach ($pastKeywords as $keyword) {
                if (
                    stripos($job->job_title, $keyword) !== false ||
                    stripos($job->job_description, $keyword) !== false
                ) {
                    $labels[] = 'Past Search';
                    break;
                }
            }

            // If this job matches any criteria, add its company to featuredCompanies
            if (!empty($labels) && $job->company) {
                $featuredCompanies->put($job->company->id, [
                    'id' => $job->company->id,
                    'company_name' => $job->company->company_name,
                    'logo' => $job->company->logo,
                    'jobs_count' => $job->company->jobs()->where('status', 'open')->count(),
                    'description' => $job->company->description,
                    'location' => $job->company->location,
                ]);
            }
        }

        // Limit to 6 companies
        $featuredCompanies = $featuredCompanies->values()->take(6);

        \Log::info('Recommended jobs count:', ['count' => count($recommendedJobs)]);

        return [
            'graduate' => $graduate,
            'vacancyStats' => $vacancyStats,

            'roles' => [
                'isGraduate' => true,
                'isCompany' => false,
                'isInstitution' => false,
            ],
            'kpi' => [
                'jobsApplied' => $jobsApplied,
                'referralsMade' => $referralsMade,
                'jobsAligned' => $jobsAligned,
                'interviewsScheduled' => $interviewsScheduled,
            ],
            'recommendedJobs' => $recommendedJobs,
            'featuredCompanies' => $featuredCompanies,
            'recent_activities' => $user->notifications()->latest()->take(10)->get(),

        ];
    }

    /* ==========================================================
     |  ADMIN / DEFAULT DASHBOARD
     ========================================================== */

    private function getTopSectorsChartOption()
    {
        // Only count jobs that have a company (company_id is not null)
        $topSectors = \App\Models\Sector::select('sectors.id', 'sectors.name')
            ->leftJoin('jobs', function ($join) {
                $join->on('jobs.sector_id', '=', 'sectors.id')
                    ->whereNotNull('jobs.company_id'); // Only jobs posted by companies
            })
            ->whereNull('jobs.deleted_at')
            ->selectRaw('sectors.name, COUNT(jobs.id) as jobs_count')
            ->groupBy('sectors.id', 'sectors.name')
            ->orderByDesc('jobs_count')
            ->take(5)
            ->get()
            ->map(fn($sector) => [
                'name' => $sector->name,
                'value' => $sector->jobs_count,
            ])
            ->toArray();

        return [
            'tooltip' => ['trigger' => 'item'],
            'legend' => ['top' => '5%'],
            'series' => [
                [
                    'name' => 'Sectors',
                    'type' => 'pie',
                    'radius' => '60%',
                    'data' => $topSectors,
                ]
            ],
        ];
    }

    private function filterByDate($query, $year, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($year && !$dateFrom && !$dateTo) {
            $query->whereYear('created_at', $year);
        }
        return $query;
    }

    private function getAdminDashboardData(Request $request)
    {

        $year = $request->input('year', now()->year);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $registeredEmployers = $this->filterByDate(
            \App\Models\User::where('role', 'company')->whereHas('company'),
            $year,
            $dateFrom,
            $dateTo
        )->count();

        $registeredJobSeekers = $this->filterByDate(
            \App\Models\User::where('role', 'graduate')->whereHas('graduate'),
            $year,
            $dateFrom,
            $dateTo
        )->count();

        $activeJobListings = $this->filterByDate(
            \App\Models\Job::where('status', 'open'),
            $year,
            $dateFrom,
            $dateTo
        )->count();

        $graduates = $this->filterByDate(
            \App\Models\Graduate::select('id', 'employment_status'),
            $year,
            $dateFrom,
            $dateTo
        )->get();

        $employed = $graduates->where('employment_status', 'Employed')->count();
        $underemployed = $graduates->where('employment_status', 'Underemployed')->count();
        $unemployed = $graduates->where('employment_status', 'Unemployed')->count();

        $referralsThisMonth = \App\Models\Referral::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $seminarRequests = SeminarRequest::with('institution')->orderByDesc('created_at')->count();
        ;


        $companies = \App\Models\Company::select('id', 'company_name')->get();

        $companyId = $request->input('company_id'); // Get company_id from query

        $recentJobsQuery = Job::where('status', 'open')
            ->with(['company', 'sector', 'category', 'locations']);

        if ($companyId) {
            $recentJobsQuery->where('company_id', $companyId);
        }

        // Apply date filter here
        $recentJobsQuery = $this->filterByDate($recentJobsQuery, $year, $dateFrom, $dateTo);

        $recentJobs = $recentJobsQuery
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

        $referralExports = \App\Models\Referral::selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyCounts = array_fill(1, 12, 0);

        foreach ($referralExports as $row) {
            $monthlyCounts[$row->month] = $row->count;
        }

        $referralTrendOption = [
            'tooltip' => ['trigger' => 'axis'],
            'xAxis' => ['type' => 'category', 'data' => $months],
            'yAxis' => ['type' => 'value'],
            'series' => [
                [
                    'name' => 'Referrals Exported',
                    'type' => 'line',
                    'data' => array_values($monthlyCounts),
                ],
            ],
        ];

        $employerMetric = $request->input('employer_metric', 'openings'); // default to openings

        if ($employerMetric === 'referrals') {
            $topCompanies = \App\Models\Company::select('companies.id', 'companies.company_name')
                ->join('jobs', 'jobs.company_id', '=', 'companies.id')
                ->join('referral', 'referrals.job_invitation_id', '=', 'jobs.id')
                ->selectRaw('companies.company_name, COUNT(referral_exports.id) as referral_count')
                ->groupBy('companies.id', 'companies.company_name')
                ->orderByDesc('referral_count')
                ->take(5)
                ->get();

            $companyNames = $topCompanies->pluck('company_name')->toArray();
            $metricCounts = $topCompanies->pluck('referral_count')->toArray();
            $metricLabel = 'Referrals';
        } elseif ($employerMetric === 'hired') {
            $topCompanies = \App\Models\Company::select('companies.id', 'companies.company_name')
                ->join('jobs', 'jobs.company_id', '=', 'companies.id')
                ->join('job_applications', function ($join) {
                    $join->on('job_applications.job_id', '=', 'jobs.id')
                        ->where('job_applications.status', '=', 'hired');
                })
                ->selectRaw('companies.company_name, COUNT(job_applications.id) as hired_count')
                ->groupBy('companies.id', 'companies.company_name')
                ->orderByDesc('hired_count')
                ->take(5)
                ->get();

            $companyNames = $topCompanies->pluck('company_name')->toArray();
            $metricCounts = $topCompanies->pluck('hired_count')->toArray();
            $metricLabel = 'Hired';
        } else { // openings
            $topCompanies = \App\Models\Company::withCount([
                'jobs' => function ($q) {
                    $q->where('status', 'open');
                }
            ])
                ->orderByDesc('jobs_count')
                ->take(5)
                ->get();

            $companyNames = $topCompanies->pluck('company_name')->toArray();
            $metricCounts = $topCompanies->pluck('jobs_count')->toArray();
            $metricLabel = 'Openings';
        }

        $topEmployersOption = [
            'tooltip' => ['trigger' => 'axis'],
            'xAxis' => ['type' => 'category', 'data' => $companyNames],
            'yAxis' => ['type' => 'value'],
            'series' => [
                [
                    'name' => $metricLabel,
                    'type' => 'bar',
                    'data' => $metricCounts,
                ],
            ],
        ];

        $topSectors = \App\Models\Sector::select('sectors.id', 'sectors.name')
            ->leftJoin('jobs', function ($join) {
                $join->on('jobs.sector_id', '=', 'sectors.id')
                    ->whereNotNull('jobs.company_id'); // Only jobs posted by companies
            })
            ->whereNull('jobs.deleted_at')
            ->selectRaw('sectors.name, COUNT(jobs.id) as jobs_count')
            ->groupBy('sectors.id', 'sectors.name')
            ->orderByDesc('jobs_count')
            ->take(5)
            ->get()
            ->map(fn($sector) => [
                'name' => $sector->name,
                'value' => $sector->jobs_count,
            ])
            ->toArray();

        $topSectorsChartOption = [
            'tooltip' => ['trigger' => 'item'],
            'legend' => ['top' => '5%'],
            'series' => [
                [
                    'name' => 'Sectors',
                    'type' => 'pie',
                    'radius' => '60%',
                    'data' => $topSectors,
                ]
            ],
        ];


        $inDemandCategories = \App\Models\Category::select('categories.id', 'categories.name')
            ->leftJoin('jobs', 'jobs.category_id', '=', 'categories.id')
            ->whereNull('jobs.deleted_at')
            ->selectRaw('categories.name, COUNT(jobs.id) as jobs_count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('jobs_count')
            ->take(10)
            ->get()
            ->map(fn($cat) => [
                'name' => $cat->name,
                'count' => $cat->jobs_count,
            ])
            ->toArray();

        return [
            'userNotApproved' => !Auth::user()->is_approved,
            'companies' => $companies,
            'roles' => [
                'isGraduate' => false,
                'isCompany' => false,
                'isInstitution' => false,
            ],
            'kpi' => [
                'registeredEmployers' => $registeredEmployers,
                'activeJobListings' => $activeJobListings,
                'registeredJobSeekers' => $registeredJobSeekers,
                'referralsThisMonth' => $referralsThisMonth,
                'successfulPlacements' => 0,
                'upcomingCareerGuidance' => $seminarRequests,
                'pendingEmployerRegistrations' => 0,
                'unemployed' => $unemployed,
                'underemployed' => $underemployed,
                'employed' => $employed,
            ],
            'recentJobs' => $recentJobs,
            'referralTrendOption' => $referralTrendOption,
            'topEmployersOption' => $topEmployersOption,
            'employerMetric' => $employerMetric,
            'topSectorsChartOption' => $this->getTopSectorsChartOption(),
            'inDemandCategories' => $inDemandCategories,
            'topSectorsChartOption' => $topSectorsChartOption,

            // ... keep the rest of your admin charts and alerts
        ];
    }
}
