<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\Job;
use App\Models\JobApplicationStage;
use App\Models\GraduateSkill;
use App\Models\Skill;
use App\Models\Sector;
use App\Models\Location;
use App\Models\JobInvitation;
use App\Models\Referral;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PesoReportsController extends Controller
{

    public function index()
    {
        return Inertia::render('Admin/Reports/Home');
    }


    public function employmentData(Request $request)
    {
        $year = $request->input('year');
        $institutionId = $request->input('institution_id');
        $programId = $request->input('program_id');
        $status = $request->input('status');
        $timeline = $request->input('timeline');

        $graduatesQuery = Graduate::with([
            'schoolYear',
            'program',
            'institution',
            'graduateEducations'
        ])->select(
            'id',
            'first_name',
            'last_name',
            'program_id',
            'employment_status',
            'school_year_id',
            'location',
            'institution_id'
        );

        if ($year) {
            $graduatesQuery->whereHas('schoolYear', function ($q) use ($year) {
                $q->where('school_year_range', $year);
            });
        }
        if ($institutionId) {
            $graduatesQuery->where('institution_id', $institutionId);
        }
        if ($programId) {
            $graduatesQuery->where('program_id', $programId);
        }
        if ($status) {
            $graduatesQuery->where('employment_status', $status);
        }

        $graduates = $graduatesQuery->get();

        $summary = [
            'total_graduates' => $graduates->count(),
            'employed' => $graduates->where('employment_status', 'Employed')->count(),
            'underemployed' => $graduates->where('employment_status', 'Underemployed')->count(),
            'unemployed' => $graduates->where('employment_status', 'Unemployed')->count(),
            'further_studies' => $graduates->where('employment_status', 'Further Studies')->count(),
        ];

        $statusCounts = [
            'Employed' => $summary['employed'],
            'Underemployed' => $summary['underemployed'],
            'Unemployed' => $summary['unemployed'],
        ];

        $programs = \App\Models\Program::all(['id', 'name']);
        $programNames = [];
        $employedByProgram = [];
        $unemployedByProgram = [];
        foreach ($programs as $program) {
            $programNames[] = $program->name;
            $employedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Employed')
                ->count();
            $unemployedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Unemployed')
                ->count();
        }

        $sectors = \App\Models\Sector::all();
        $industryGraduateCounts = [];
        foreach ($sectors as $sector) {
            $count = Graduate::where('employment_status', 'Employed')
                ->whereHas('user', function ($q) {
                    $q->where('role', 'graduate');
                })
                ->whereHas('user', function ($q) use ($sector) {
                    $q->whereHas('jobApplications.job', function ($q2) use ($sector) {
                        $q2->where('sector_id', $sector->id);
                    });
                })
                ->count();

            $industryGraduateCounts[] = [
                'name' => $sector->name,
                'value' => $count,
            ];
        }

        $industryJobRoles = [];
        $industryApplicants = [];
        foreach ($sectors as $sector) {
            $jobs = \App\Models\Job::where('sector_id', $sector->id)->where('is_approved', 1)->get();
            $jobRoles = $jobs->count();
            $applicants = \App\Models\JobApplication::whereIn('job_id', $jobs->pluck('id'))->distinct('graduate_id')->count();

            $industryJobRoles[] = $jobRoles;
            $industryApplicants[] = $applicants;
        }

        $industryNames = $sectors->pluck('name');

        // --- Unemployment Rate Over Time (for Unemployment Rate tab) ---
        $allGraduatesQuery = Graduate::with(['schoolYear'])
            ->select('id', 'employment_status', 'school_year_id');

        if ($year) {
            $allGraduatesQuery->whereHas('schoolYear', function ($q) use ($year) {
                $q->where('school_year_range', $year);
            });
        }
        if ($institutionId) {
            $allGraduatesQuery->where('institution_id', $institutionId);
        }
        if ($programId) {
            $allGraduatesQuery->where('program_id', $programId);
        }

        $allGraduates = $allGraduatesQuery->get();

        $schoolYears = $allGraduates->pluck('schoolYear')->filter()->unique('id')->sortBy('year')->values();

        $unemploymentOverTime = [];
        foreach ($schoolYears as $schoolYear) {
            $yearLabel = $schoolYear->school_year_range ?? $schoolYear->year ?? 'Unknown';
            $graduatesInYear = $allGraduates->where('schoolYear.id', $schoolYear->id);
            $total = $graduatesInYear->count();
            $unemployed = $graduatesInYear->where('employment_status', 'Unemployed')->count();
            $employed = $graduatesInYear->where('employment_status', 'Employed')->count();
            $unemploymentRate = $total ? round(($unemployed / $total) * 100, 2) : 0;
            $employmentRate = $total ? round(($employed / $total) * 100, 2) : 0;
            $unemploymentOverTime[] = [
                'year' => $yearLabel,
                'unemployment_rate' => $unemploymentRate,
                'employment_rate' => $employmentRate,
                'unemployed' => $unemployed,
                'employed' => $employed,
                'total' => $total,
            ];
        }
        \Log::info('Unemployment Over Time:', $unemploymentOverTime);


        $year = $request->input('year'); // e.g. '2024-01', '2024', etc.
        $institutionId = $request->input('institution_id');
        $programId = $request->input('program_id');

        // Query only currently employed graduates in General Santos City
        $hiredGraduatesQuery = Graduate::with(['user', 'institution', 'program', 'company'])
            ->whereRaw("TRIM(current_job_title) != ''")
            ->whereRaw("LOWER(TRIM(current_job_title)) NOT IN ('n/a', 'na', 'none', 'not applicable')")
            ->where('employment_status', '!=', 'Unemployed');
        // ->whereHas('company', function ($q) {
        //     $q->where('company_city', 'General Santos City');
        // });

        if ($institutionId) {
            $hiredGraduatesQuery->where('institution_id', $institutionId);
        }
        if ($programId) {
            $hiredGraduatesQuery->where('program_id', $programId);
        }
        if ($timeline) {
            $hiredGraduatesQuery->whereHas('jobApplications', function ($q) use ($timeline) {
                $q->where('status', 'hired')
                    ->where('updated_at', 'like', $timeline . '%');
            });
        }

        $hiredGraduates = $hiredGraduatesQuery->get()->filter(function ($grad) {
            $title = strtolower(trim($grad->current_job_title));
            return $title !== '' && !in_array($title, ['n/a', 'na', 'none', 'not applicable']);
        })->values();

        // Group and count by institution
        $schoolCounts = $hiredGraduates->groupBy(function ($grad) {
            return $grad->institution ? $grad->institution->institution_name : 'Unknown';
        })->map->count()->sortDesc();

        // For chart: labels and data
        $schoolEmployabilityLabels = $schoolCounts->keys()->toArray();
        $schoolEmployabilityData = $schoolCounts->values()->toArray();

        // For table: list of graduates with school, program, hire date, and company city
        $schoolEmployabilityList = $hiredGraduates->map(function ($grad) {
            $companyCity = $grad->company ? $grad->company->company_city : '';
            $companyName = $grad->company ? $grad->company->company_name : '';
            return [
                'name' => $grad->first_name . ' ' . $grad->last_name,
                'institution' => $grad->institution ? $grad->institution->institution_name : '',
                'program' => $grad->program ? $grad->program->name : '',
                'current_job_title' => $grad->current_job_title ?? '',
                'company_city' => $companyCity,
                'company_name' => $companyName,
                'hired_at' => optional($grad->jobApplications()->where('status', 'hired')->latest('updated_at')->first())->updated_at,
            ];
        });

        // --- Demand-Supply Career Gap Map Data ---

        $allJobs = \App\Models\Job::where('is_approved', 1)
            ->with(['programs', 'category', 'sector'])
            ->orderByDesc('created_at')
            ->get();

        $roleGroups = $allJobs->groupBy('job_title');

        $inDemandJobs = $roleGroups->map(function ($jobs, $role) {
            $firstJob = $jobs->first();
            $programFields = [];
            foreach ($firstJob->programs as $program) {
                $fields = \App\Models\GraduateEducation::where('program', $program->name)
                    ->pluck('field_of_study')
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                $programFields[] = [
                    'program' => $program->name,
                    'fields_of_study' => $fields,
                ];
            }
            $allFields = collect($programFields)
                ->flatMap(function ($pf) {
                    return $pf['fields_of_study'];
                })
                ->unique()
                ->values()
                ->toArray();

            return [
                'role' => $role,
                'demand' => $jobs->count(), // <-- correct: count jobs for this role
                'category' => $firstJob->category->name ?? null,
                'sector' => $firstJob->sector->name ?? null,
                'skills' => is_array($firstJob->skills) ? $firstJob->skills : (json_decode($firstJob->skills, true) ?: []),
                'programs' => $firstJob->programs->pluck('name')->toArray(),
                'program_ids' => $firstJob->programs ? $firstJob->programs->pluck('id')->toArray() : [],
                'program_fields' => $programFields,
                'fields_of_study' => $allFields,
                'year' => $firstJob->created_at ? $firstJob->created_at->format('Y') : null,
            ];
        });


        return response()->json([
            'graduates' => $graduates,
            'industryGraduateCounts' => $industryGraduateCounts,
            'industryNames' => $industryNames,
            'industryJobRoles' => $industryJobRoles,
            'industryApplicants' => $industryApplicants,
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'programNames' => $programNames,
            'employedByProgram' => $employedByProgram,
            'unemployedByProgram' => $unemployedByProgram,
            'unemploymentOverTime' => $unemploymentOverTime,
            'schoolEmployabilityLabels' => $schoolEmployabilityLabels,
            'schoolEmployabilityData' => $schoolEmployabilityData,
            'schoolEmployabilityList' => $schoolEmployabilityList, // <-- Added for Unemployment Rate tab
            'inDemandJobs' => $inDemandJobs->values()->toArray(),
            // Add more analytics data as needed
        ]);
    }

    public function employment(Request $request)
    {
        $institutions = \App\Models\Institution::all(['id', 'institution_name']);
        $programs = \App\Models\Program::all(['id', 'name']);
        return Inertia::render('Admin/Reports/Employment', [
            'institutions' => $institutions,
            'programs' => $programs,
            // No analytics data yet
        ]);
    }

    public function referral(Request $request)
    {
        // For filter dropdowns, etc.
        $companies = \App\Models\Company::all(['id', 'company_name']);
        $roles = \App\Models\Job::select('job_title')->distinct()->pluck('job_title');
        $sources = ['Employee', 'Partner', 'Alumni', 'Recruiter', 'Other'];

        return Inertia::render('Admin/Reports/Referral', [
            'companies' => $companies,
            'roles' => $roles,
            'sources' => $sources,
            // No analytics data yet, will be fetched via referralData
        ]);
    }


    public function referralData(Request $request)
    {
        // --- Filtering logic ---
        $query = \App\Models\Referral::query();

        if ($request->company_id) {
            $query->whereHas('job.company', function ($q) use ($request) {
                $q->where('id', $request->company_id);
            });
        }
        if ($request->role) {
            $query->whereHas('job', function ($q) use ($request) {
                $q->where('job_title', $request->role);
            });
        }
        if ($request->timeline) {
            $year = substr($request->timeline, 0, 4);
            $month = substr($request->timeline, 5, 2);
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        // --- KPIs (same logic as ManageJobReferralsController) ---
        $totalReferrals = $query->count();
        $successfulReferrals = Referral::where('status', 'success')->count();
        $successRate = $totalReferrals > 0 ? round(($successfulReferrals / $totalReferrals) * 100, 2) : 0;

        // --- Referral analytics for charts ---
        $referralsQuery = Referral::with([
            'graduate' => function ($q) {
                $q->with('user');
            },
            'job.company',
        ]);
        if ($request->company_id) {
            $referralsQuery->whereHas('job.company', function ($q) use ($request) {
                $q->where('id', $request->company_id);
            });
        }
        if ($request->role) {
            $referralsQuery->whereHas('job', function ($q) use ($request) {
                $q->where('job_title', $request->role);
            });
        }
        if ($request->timeline) {
            $referralsQuery->whereYear('created_at', substr($request->timeline, 0, 4));
            if (strlen($request->timeline) > 4) {
                $referralsQuery->whereMonth('created_at', substr($request->timeline, 5, 2));
            }
        }
        $referrals = $referralsQuery->get();


        $applicationsQuery = \App\Models\JobApplication::query();

        if ($request->company_id) {
            $applicationsQuery->whereHas('job.company', function ($q) use ($request) {
                $q->where('id', $request->company_id);
            });
        }
        if ($request->role) {
            $applicationsQuery->whereHas('job', function ($q) use ($request) {
                $q->where('job_title', $request->role);
            });
        }
        if ($request->timeline) {
            $year = substr($request->timeline, 0, 4);
            $month = substr($request->timeline, 5, 2);
            $applicationsQuery->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        // Funnel Chart Data: Stages
        $applications = $applicationsQuery->get();

        $latestStages = JobApplicationStage::whereIn('job_application_id', $applications->pluck('id'))
            ->select('job_application_id', 'stage', 'changed_at')
            ->orderBy('changed_at', 'desc')
            ->get()
            ->groupBy('job_application_id')
            ->map(fn($stages) => $stages->first()->stage);


        $totalApplied  = $applications->where('stage', 'applied')->count();
        $screened      = $applications->where('stage', 'screening')->count();
        $interviewed   = $applications->where('stage', 'interview')->count();
        $offered       = $applications->where('stage', 'offer')->count();
        $hired         = $applications->where('stage', 'hired')->count();
        $rejected      = $applications->where('stage', 'rejected')->count();

        $totalReferred = $applications->count(); // or use $query->count() if you want referrals count
        $screenedKPI    = $applications->where('stage', 'screening')->count();
        $interviewedKPI = $applications->where('stage', 'interview')->count();
        $hiredKPI       = $applications->where('stage', 'hired')->count();



        $funnelData = [
            ['name' => 'Referred',    'value' => (int) $totalReferred],
            ['name' => 'Applied',     'value' => (int) $totalApplied],
            ['name' => 'Screened',    'value' => (int) $screened],
            ['name' => 'Interviewed', 'value' => (int) $interviewed],
            ['name' => 'Offered',     'value' => (int) $offered],
            ['name' => 'Hired',       'value' => (int) $hired],
            ['name' => 'Rejected',    'value' => (int) $rejected],
        ];

        // Bar Chart: Success rates of referrals vs other sources
        $referralSuccess = $referrals->where('status', 'hired')->count();
        // $otherSourcesSuccess = \App\Models\JobApplication::where('source', '!=', 'referral')->where('status', 'hired')->count();
        $barSuccessData = [
            ['source' => 'Referral', 'success' => $referralSuccess],
            // ['source' => 'Other', 'success' => $otherSourcesSuccess],
        ];

        // Pie Chart & Treemap: Source of referrals
        $sourceCounts = $referrals->groupBy('referral_source')->map->count();
        $pieSourceData = $sourceCounts->map(function ($count, $src) {
            return ['name' => $src, 'value' => $count];
        })->values()->toArray();
        $treemapSourceData = $pieSourceData;

        // Line & Area Chart: Referral trends over time
        $monthlyReferrals = $referrals->groupBy(function ($ref) {
            return $ref->created_at->format('Y-m');
        })->map->count();
        $lineTrendData = [];
        foreach ($monthlyReferrals as $month => $count) {
            $lineTrendData[] = ['month' => $month, 'count' => $count];
        }
        $areaTrendData = $lineTrendData;

        // Network Graph & Bubble Chart: Referral network analysis
        $networkNodes = [];
        $networkLinks = [];
        $referrerCounts = [];
        foreach ($referrals as $ref) {
            $referrer = $ref->referrer ? $ref->referrer->name : 'Unknown';
            $candidate = $ref->graduate ? $ref->graduate->first_name . ' ' . $ref->graduate->last_name : 'Unknown';
            $networkNodes[$referrer] = true;
            $networkNodes[$candidate] = true;
            $networkLinks[] = ['source' => $referrer, 'target' => $candidate];
            $referrerCounts[$referrer] = ($referrerCounts[$referrer] ?? 0) + ($ref->status === 'hired' ? 1 : 0);
        }
        $bubbleData = [];
        foreach ($referrerCounts as $referrer => $count) {
            $bubbleData[] = ['name' => $referrer, 'value' => $count];
        }


        // Clustered Bar & Stacked Column: Referral performance by role
        $roleStats = [];
        $roles = $referrals->groupBy(fn($ref) => $ref->job ? $ref->job->job_title : 'Unknown');
        foreach ($roles as $roleName => $refs) {
            $jobIds = $refs->pluck('job_id')->unique();
            $jobApplications = \App\Models\JobApplication::whereIn('job_id', $jobIds)->get();

            $roleStats[] = [
                'role'        => $roleName,
                'referred'    => $refs->count(),
                'applied'     => $jobApplications->where('stage', 'applied')->count(),
                'screened'    => $jobApplications->where('stage', 'screening')->count(),
                'interviewed' => $jobApplications->where('stage', 'interview')->count(),
                'offered'     => $jobApplications->where('stage', 'offer')->count(),
                'hired'       => $jobApplications->where('stage', 'hired')->count(),
                'rejected'    => $jobApplications->where('stage', 'rejected')->count(),
            ];
        }
        // Scatter Plot & Histogram: Referral bonuses and outcomes
        $bonuses = $referrals->pluck('referral_bonus')->filter();
        $bonusSuccess = [];
        foreach ($referrals as $ref) {
            if ($ref->referral_bonus) {
                $bonusSuccess[] = [
                    'bonus' => $ref->referral_bonus,
                    'hired' => $ref->status === 'hired' ? 1 : 0,
                    'retained' => $ref->graduate && $ref->graduate->is_retained ? 1 : 0,
                ];
            }
        }
        $histogramData = $bonuses->toArray();

        // Word Cloud & Stacked Column: Reason for referral success
        $feedbacks = $referrals->pluck('referral_feedback')->filter();
        $wordCloudData = [];
        foreach ($feedbacks as $feedback) {
            foreach (explode(' ', strtolower($feedback)) as $word) {
                if (strlen($word) > 2) {
                    $wordCloudData[$word] = ($wordCloudData[$word] ?? 0) + 1;
                }
            }
        }
        $stackedFeedback = [];
        foreach ($feedbacks as $feedback) {
            $stackedFeedback[] = [
                'feedback' => $feedback,
                'count' => 1,
            ];
        }



        try {

            \Log::info('School Employability Labels:', $schoolEmployabilityLabels);
            \Log::info('School Employability Data:', $schoolEmployabilityData);
            \Log::info('School Employability List:', $schoolEmployabilityList->toArray());
            return response()->json([
                'totalReferrals' => $totalReferrals,
                'successfulReferrals' => $successfulReferrals,
                'successRate' => $successRate,
                'funnelData' => $funnelData,
                'barSuccessData' => $barSuccessData,
                'pieSourceData' => $pieSourceData,
                'treemapSourceData' => $treemapSourceData,
                'lineTrendData' => $lineTrendData,
                'areaTrendData' => $areaTrendData,
                'bubbleData' => $bubbleData,
                'roleStats' => $roleStats,
                'bonusSuccess' => $bonusSuccess,
                'histogramData' => $histogramData,
                'wordCloudData' => $wordCloudData,
                'stackedFeedback' => $stackedFeedback,
                'screenedKPI' => $screenedKPI,
                'interviewedKPI' => $interviewedKPI,
                'hiredKPI' => $hiredKPI,


            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }



    public function reports(Request $request)
    {
        // Employment Status Overview
        $year = $request->input('year');

        $graduates = Graduate::with([
            'schoolYear',
            'program',
            'institution',
            'graduateEducations' // <-- add this relation
        ])->select(
            'id',
            'first_name',
            'last_name',
            'program_id',
            'employment_status',
            'school_year_id',
            'location',
            'institution_id'
        )->get();

        $summary = [
            'total_graduates' => $graduates->count(),
            'employed' => $graduates->where('employment_status', 'Employed')->count(),
            'underemployed' => $graduates->where('employment_status', 'Underemployed')->count(),
            'unemployed' => $graduates->where('employment_status', 'Unemployed')->count(),
            'further_studies' => $graduates->where('employment_status', 'Further Studies')->count(),
        ];

        $statusCounts = [
            'Employed' => $summary['employed'],
            'Underemployed' => $summary['underemployed'],
            'Unemployed' => $summary['unemployed'],
        ];

        // Employment By Program
        // Get all programs
        $programs = Program::all();

        $programNames = [];
        $employedByProgram = [];
        $unemployedByProgram = [];

        foreach ($programs as $program) {
            $programNames[] = $program->name;
            $employedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Employed')
                ->count();
            $unemployedByProgram[] = $graduates
                ->where('program_id', $program->id)
                ->where('employment_status', 'Unemployed')
                ->count();
        }

        // --- Skills and Roles Analysis ---

        // 1. Most common job roles among employed graduates (word cloud)
        $employedGraduates = Graduate::where('employment_status', 'Employed')->get();
        $jobRoles = $employedGraduates->pluck('current_job_title')->filter()->countBy();

        // 2. Most common skills among employed graduates (word cloud)
        $employedGraduateIds = $employedGraduates->pluck('id');
        $employedSkills = GraduateSkill::whereIn('graduate_id', $employedGraduateIds)->pluck('skill_id');
        $skillCounts = Skill::whereIn('id', $employedSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($employedSkills) {
            return [$name => $employedSkills->where('id', $id)->count()];
        });

        // 3. Skill demand from job postings (bar chart)
        $jobSkills = Job::where('is_approved', 1)->pluck('skills')->flatten()->filter();
        $jobSkillCounts = [];
        foreach ($jobSkills as $skillArr) {
            if (is_array($skillArr)) {
                foreach ($skillArr as $skillName) {
                    $jobSkillCounts[$skillName] = ($jobSkillCounts[$skillName] ?? 0) + 1;
                }
            } elseif (is_string($skillArr)) {
                $jobSkillCounts[$skillArr] = ($jobSkillCounts[$skillArr] ?? 0) + 1;
            }
        }

        // 4. Skill supply from all graduates (bar chart)
        $allGraduateSkills = GraduateSkill::pluck('skill_id');
        $allSkillCounts = Skill::whereIn('id', $allGraduateSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($allGraduateSkills) {
            return [$name => $allGraduateSkills->where('id', $id)->count()];
        });

        // 5. Top skills (by demand and supply)
        $topSkillDemand = collect($jobSkillCounts)->sortDesc()->take(10);
        $topSkillSupply = $allSkillCounts->sortDesc()->take(10);


        // Area chart: Unemployment rate over time (by year)
        $schoolYears = $graduates->pluck('schoolYear')->filter()->unique('id')->sortBy('year')->values();

        $unemploymentOverTime = [];
        foreach ($schoolYears as $schoolYear) {
            $yearLabel = $schoolYear->school_year_range ?? $schoolYear->year ?? 'Unknown';
            $graduatesInYear = $graduates->where('schoolYear.id', $schoolYear->id);
            $total = $graduatesInYear->count();
            $unemployed = $graduatesInYear->where('employment_status', 'Unemployed')->count();
            $employed = $graduatesInYear->where('employment_status', 'Employed')->count();
            $unemploymentRate = $total ? round(($unemployed / $total) * 100, 2) : 0;
            $employmentRate = $total ? round(($employed / $total) * 100, 2) : 0;
            $unemploymentOverTime[] = [
                'year' => $yearLabel,
                'unemployment_rate' => $unemploymentRate,
                'employment_rate' => $employmentRate,
                'unemployed' => $unemployed,
                'employed' => $employed,
                'total' => $total,
            ];
        }

        // Employment Trend Over Time (Line Chart) & Job Placement Rate (Area Chart)
        $employmentTrend = [];
        $jobPlacementTrend = [];

        foreach ($schoolYears as $schoolYear) {
            $yearLabel = $schoolYear->school_year_range ?? $schoolYear->year ?? 'Unknown';
            $graduatesInYear = $graduates->where('schoolYear.id', $schoolYear->id);
            $total = $graduatesInYear->count();
            $employed = $graduatesInYear->where('employment_status', 'Employed')->count();
            $jobPlaced = $graduatesInYear->where('employment_status', 'Employed')->whereNotNull('current_job_title')->count();

            $employmentRate = $total ? round(($employed / $total) * 100, 2) : 0;
            $jobPlacementRate = $total ? round(($jobPlaced / $total) * 100, 2) : 0;

            $employmentTrend[] = [
                'year' => $yearLabel,
                'employment_rate' => $employmentRate,
            ];
            $jobPlacementTrend[] = [
                'year' => $yearLabel,
                'placement_rate' => $jobPlacementRate,
            ];
        }

        // 1. Employment rate by area (city/province)
        $locationStats = [];
        $locations = Location::all();

        foreach ($locations as $location) {
            $graduatesInLocation = Graduate::where('location', $location->address)->get();
            $total = $graduatesInLocation->count();
            $employed = $graduatesInLocation->where('employment_status', 'Employed')->count();
            $unemployed = $graduatesInLocation->where('employment_status', 'Unemployed')->count();
            $employmentRate = $total ? round(($employed / $total) * 100, 2) : 0;

            $locationStats[] = [
                'name' => $location->address,
                'total' => $total,
                'employed' => $employed,
                'unemployed' => $unemployed,
                'employment_rate' => $employmentRate,
            ];
        }

        // 2. Job openings by area
        $jobOpeningsByLocation = Job::where('is_approved', 1)
            ->with('locations')
            ->get()
            ->flatMap(function ($job) {
                return $job->locations->map(function ($location) use ($job) {
                    return [
                        'name' => $location->address,
                        'job_id' => $job->id,
                    ];
                });
            })
            ->groupBy('name')
            ->map(fn($jobs) => count($jobs))
            ->toArray();

        // 3. Job seekers by area
        $jobSeekersByLocation = Graduate::select('location')
            ->whereNotNull('location')
            ->get()
            ->groupBy('location')
            ->map(fn($grads) => count($grads))
            ->toArray();

        // 4. Referral activity by location (count of successful referrals)
        $referralByLocation = Referral::where('status', 'hired')
            ->with('graduate')
            ->get()
            ->groupBy(fn($invite) => $invite->graduate->location ?? 'Unknown')
            ->map(fn($invites) => count($invites))
            ->toArray();

        // 5. Referral success rate heatmap (successful referrals / total referrals per location)
        $referralSuccessHeatmap = [];
        $allReferrals = Referral::with('graduate')->get()->groupBy(fn($invite) => $invite->graduate->location ?? 'Unknown');
        foreach ($allReferrals as $loc => $invites) {
            $total = count($invites);
            $success = collect($invites)->where('status', 'hired')->count();
            $rate = $total ? round(($success / $total) * 100, 2) : 0;
            $referralSuccessHeatmap[] = [
                'name' => $loc,
                'rate' => $rate,
                'total' => $total,
                'success' => $success,
            ];
        }

        // --- Employment by Industry Data ---



        // Get all sectors (industries)
        $sectors = Sector::all();

        // 1. Graduates per industry (Bar/Clustered Column & Treemap)
        $industryGraduateCounts = [];
        foreach ($sectors as $sector) {
            $count = Graduate::where('employment_status', 'Employed')
                ->whereHas('user', function ($q) {
                    $q->where('role', 'graduate');
                })
                ->whereHas('user', function ($q) use ($sector) {
                    $q->whereHas('jobApplications.job', function ($q2) use ($sector) {
                        $q2->where('sector_id', $sector->id);
                    });
                })
                ->count();

            $industryGraduateCounts[] = [
                'name' => $sector->name,
                'value' => $count,
            ];
        }

        // 2. Job roles available & applicants per industry (Stacked Column)
        $industryJobRoles = [];
        $industryApplicants = [];
        foreach ($sectors as $sector) {
            $jobs = \App\Models\Job::where('sector_id', $sector->id)->where('is_approved', 1)->get();
            $jobRoles = $jobs->count();
            $applicants = \App\Models\JobApplication::whereIn('job_id', $jobs->pluck('id'))->distinct('graduate_id')->count();

            $industryJobRoles[] = $jobRoles;
            $industryApplicants[] = $applicants;
        }

        $industryNames = $sectors->pluck('name');

        // --- Salary Insights Data ---

        // 1. Box Plot Data: Salary ranges per industry
        $industries = \App\Models\Sector::all();
        $industrySalaryBoxData = [];
        foreach ($industries as $industry) {
            $jobs = \App\Models\Job::where('sector_id', $industry->id)
                ->where('is_approved', 1)
                ->with('salary')
                ->get();

            $salaries = $jobs->pluck('salary')->filter()->map(function ($salary) {
                return [
                    'min' => $salary->job_min_salary,
                    'max' => $salary->job_max_salary,
                ];
            })->filter(fn($s) => $s['min'] !== null && $s['max'] !== null);

            $allSalaries = $salaries->flatMap(fn($s) => [$s['min'], $s['max']])->sort()->values();
            if ($allSalaries->count() > 0) {
                $industrySalaryBoxData[] = [
                    'industry' => $industry->name,
                    'salaries' => $allSalaries->toArray(),
                ];
            }
        }

        // 2. Stacked Bar: Entry, Mid, Senior salary per industry
        $levels = [
            'Entry-level' => 'Entry',
            'Intermediate' => 'Mid',
            'Mid-level' => 'Mid',
            'Senior-level' => 'Senior',
            'Director' => 'Senior',
            'Executive' => 'Senior',
        ];

        $industryLevelSalaries = [];
        foreach ($industries as $industry) {
            $levelSalaries = ['Entry' => 0, 'Mid' => 0, 'Senior' => 0];
            foreach ($levels as $dbLevel => $group) {
                $jobs = \App\Models\Job::where('sector_id', $industry->id)
                    ->where('is_approved', 1)
                    ->where('job_experience_level', $dbLevel)
                    ->get();

                // Use job_min_salary and job_max_salary from jobs table directly
                $avg = $jobs->pluck('job_min_salary')->merge($jobs->pluck('job_max_salary'))->filter()->avg();
                if ($avg) {
                    // If multiple dbLevels map to the same group, average them
                    if ($levelSalaries[$group] === 0) {
                        $levelSalaries[$group] = $avg;
                    } else {
                        $levelSalaries[$group] = ($levelSalaries[$group] + $avg) / 2;
                    }
                }
            }
            $industryLevelSalaries[] = [
                'industry' => $industry->name,
                'Entry' => round($levelSalaries['Entry'], 2),
                'Mid' => round($levelSalaries['Mid'], 2),
                'Senior' => round($levelSalaries['Senior'], 2),
            ];
        }

        // 3. Histogram: Salary expectations (graduates) vs. offered (jobs)
        $graduateMin = \App\Models\EmploymentPreference::pluck('employment_min_salary')->filter()->toArray();
        $graduateMax = \App\Models\EmploymentPreference::pluck('employment_max_salary')->filter()->toArray();
        $jobMin = Salary::pluck('job_min_salary')->filter()->toArray();
        $jobMax = Salary::pluck('job_max_salary')->filter()->toArray();

        $educationLevels = ['Bachelor', 'Master'];

        $employmentByEducation = [];
        foreach ($educationLevels as $level) {
            $graduatesWithLevel = \App\Models\Education::where('education', $level)
                ->whereHas('graduate')
                ->get()
                ->pluck('graduate_id')
                ->unique();

            $total = $graduatesWithLevel->count();
            $employed = \App\Models\Graduate::whereIn('id', $graduatesWithLevel)
                ->where('employment_status', 'Employed')
                ->count();

            $rate = $total ? round(($employed / $total) * 100, 2) : 0;
            $employmentByEducation[] = $rate;
        }

        $radarPrograms = \App\Models\Program::pluck('name')->toArray();
        $radarSkills = ['Technical', 'Communication', 'Problem Solving', 'Teamwork']; // or fetch from Skill model

        $radarData = [];

        foreach ($radarPrograms as $programName) {
            $program = \App\Models\Program::where('name', $programName)->first();
            if (!$program) continue;

            $programGraduates = \App\Models\Graduate::where('program_id', $program->id)->pluck('id');

            $skillScores = [];
            foreach ($radarSkills as $skillName) {
                $skill = \App\Models\Skill::where('name', $skillName)->first();
                if (!$skill) {
                    $skillScores[] = 0;
                    continue;
                }
                $count = \App\Models\GraduateSkill::whereIn('graduate_id', $programGraduates)
                    ->where('skill_id', $skill->id)
                    ->count();
                $percentage = $programGraduates->count() ? round(($count / $programGraduates->count()) * 100, 2) : 0;
                $skillScores[] = $percentage;
            }
            $radarData[] = [
                'value' => $skillScores,
                'name' => $programName,
            ];
        }

        // In-demand jobs (top 10)
        $inDemandJobs = Job::where('is_approved', 1)
            ->with(['programs', 'category', 'sector'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($job) {
                // Build programFields: for each program, get all unique field_of_study values from graduate_educations
                $programFields = [];
                foreach ($job->programs as $program) {
                    $fields = \App\Models\GraduateEducation::where('program', $program->name)
                        ->pluck('field_of_study')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray();
                    $programFields[] = [
                        'program' => $program->name,
                        'fields_of_study' => $fields,
                    ];
                }

                // Flatten all fields_of_study for this job
                $allFields = collect($programFields)
                    ->flatMap(function ($pf) {
                        return $pf['fields_of_study'];
                    })
                    ->unique()
                    ->values()
                    ->toArray();

                return [
                    'role' => $job->job_title,
                    'category' => $job->category->name ?? null,
                    'sector' => $job->sector->name ?? null,
                    'skills' => is_array($job->skills) ? $job->skills : json_decode($job->skills, true),
                    'programs' => $job->programs->pluck('name')->toArray(),
                    'program_ids' => $job->programs ? $job->programs->pluck('id')->toArray() : [],
                    'program_fields' => $programFields,
                    'fields_of_study' => $allFields,  // <-- now this is correct!
                ];
            });

        // Top skills (by frequency in jobs)
        $allSkills = Job::where('is_approved', 1)->pluck('skills')->toArray();
        $skillsFlat = collect($allSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy()
            ->sortDesc()
            ->take(10);


        // Filters
        $timeline = $request->input('timeline'); // e.g. '2024-01', '2024', etc.
        $institutionId = $request->input('institution_id');
        $programId = $request->input('program_id');

        // Get all institutions for filter dropdown
        $institutions = \App\Models\Institution::all(['id', 'institution_name']);
        $programs = Program::all(['id', 'name']);

        // Query only currently employed graduates in General Santos City
        $hiredGraduatesQuery = Graduate::with(['user', 'institution', 'program', 'company'])
            ->whereRaw("TRIM(current_job_title) != ''")
            ->whereRaw("LOWER(TRIM(current_job_title)) NOT IN ('n/a', 'na', 'none', 'not applicable')")
            ->where('employment_status', '!=', 'Unemployed')
            ->whereHas('company', function ($q) {
                $q->where('company_city', 'General Santos City');
            });

        if ($institutionId) {
            $hiredGraduatesQuery->where('institution_id', $institutionId);
        }
        if ($programId) {
            $hiredGraduatesQuery->where('program_id', $programId);
        }


        $hiredGraduates = $hiredGraduatesQuery->get()->filter(function ($grad) {
            $title = strtolower(trim($grad->current_job_title));
            return $title !== '' && !in_array($title, ['n/a', 'na', 'none', 'not applicable']);
        })->values();

        // Group and count by institution
        $schoolCounts = $hiredGraduates->groupBy(function ($grad) {
            return $grad->institution ? $grad->institution->institution_name : 'Unknown';
        })->map->count()->sortDesc();

        // For chart: labels and data
        $chartLabels = $schoolCounts->keys()->toArray();
        $chartData = $schoolCounts->values()->toArray();

        // For table: list of graduates with school, program, hire date, and company city
        $graduateList = $hiredGraduates->map(function ($grad) {
            $companyCity = $grad->company ? $grad->company->company_city : '';
            $companyName = $grad->company ? $grad->company->company_name : '';

            return [
                'name' => $grad->first_name . ' ' . $grad->last_name, // <-- add a space here
                'institution' => $grad->institution ? $grad->institution->institution_name : '',
                'program' => $grad->program ? $grad->program->name : '',
                'current_job_title' => $grad->current_job_title ?? '',
                'company_city' => $companyCity,
                'company_name' => $companyName,

                'hired_at' => optional($grad->jobApplications()->where('status', 'hired')->latest('updated_at')->first())->updated_at,
            ];
        });

        return Inertia::render('Admin/Reports/Reports', [
            'graduates' => $graduates,
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'programNames' => $programNames,
            'employedByProgram' => $employedByProgram,
            'unemployedByProgram' => $unemployedByProgram,
            'jobRolesWordCloud' => $jobRoles,
            'skillsWordCloud' => $skillCounts,
            'topSkillDemand' => $topSkillDemand,
            'topSkillSupply' => $topSkillSupply,
            'unemploymentOverTime' => $unemploymentOverTime,
            'employmentTrend' => $employmentTrend,
            'jobPlacementTrend' => $jobPlacementTrend,
            'locationStats' => $locationStats,
            'jobOpeningsByLocation' => $jobOpeningsByLocation,
            'jobSeekersByLocation' => $jobSeekersByLocation,
            'referralByLocation' => $referralByLocation,
            'referralSuccessHeatmap' => $referralSuccessHeatmap,
            'industryGraduateCounts' => $industryGraduateCounts,
            'industryJobRoles' => $industryJobRoles,
            'industryApplicants' => $industryApplicants,
            'industryNames' => $industryNames,
            'industrySalaryBoxData' => $industrySalaryBoxData,
            'industryLevelSalaries' => $industryLevelSalaries,
            'salaryExpectations' => [
                'graduateMin' => $graduateMin,
                'graduateMax' => $graduateMax,
                'jobMin' => $jobMin,
                'jobMax' => $jobMax,
            ],
            'radarPrograms' => $radarPrograms,
            'radarSkills' => $radarSkills,
            'radarData' => $radarData,
            'educationLevels' => $educationLevels,
            'employmentByEducation' => $employmentByEducation,
            'inDemandJobs' => $inDemandJobs,
            'topSkills' => $skillsFlat,
            'institutions' => $institutions,
            'graduateList' => $graduateList,
            'programs' => $programs,
            'filters' => [
                'timeline' => $timeline,
                'institution_id' => $institutionId,
                'program_id' => $programId,
            ],
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'graduates' => $graduates,
            'statusCounts' => $statusCounts,
            'programNames' => $programNames,
            'employedByProgram' => $employedByProgram,
            'unemployedByProgram' => $unemployedByProgram,
            'industryGraduateCounts' => $industryGraduateCounts,
            'industryNames' => $industryNames,
            'industryJobRoles' => $industryJobRoles,
            'industryApplicants' => $industryApplicants,
        ]);
    }


    public function skills()
    {
        return Inertia::render('Admin/Reports/Skills');
    }

    public function skillsData()
    {
        // --- Skills Demand by Industry (Heatmap) ---
        $industries = \App\Models\Sector::all();
        $skills = \App\Models\Skill::pluck('name', 'id');
        $heatmap = [];

        foreach ($industries as $industry) {
            $jobs = \App\Models\Job::where('sector_id', $industry->id)->where('is_approved', 1)->get();
            $skillCounts = [];
            foreach ($jobs as $job) {
                // Normalize and decode skills
                $jobSkills = [];
                if (is_array($job->skills)) {
                    $jobSkills = $job->skills;
                } elseif (is_string($job->skills) && $job->skills !== '') {
                    $decoded = json_decode($job->skills, true);
                    $jobSkills = is_array($decoded) ? $decoded : [];
                }
                foreach ($jobSkills as $skillName) {
                    $normalized = strtolower(trim($skillName));
                    if ($normalized !== '') {
                        $skillCounts[$normalized] = ($skillCounts[$normalized] ?? 0) + 1;
                    }
                }
            }
            foreach ($skillCounts as $skillName => $count) {
                $heatmap[] = [
                    'industry' => $industry->name,
                    'skill' => $skillName,
                    'demand' => $count,
                ];
            }
        }

        // --- Bubble Chart: Skill Demand vs Supply ---
        $jobSkills = \App\Models\Job::where('is_approved', 1)->pluck('skills')->toArray();
        $skillsFlat = collect($jobSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy();

        $allGraduateSkills = \App\Models\GraduateSkill::pluck('skill_id');
        $allSkillCounts = \App\Models\Skill::whereIn('id', $allGraduateSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($allGraduateSkills) {
            return [$name => $allGraduateSkills->where('id', $id)->count()];
        });

        $bubbleData = [];
        foreach ($skillsFlat as $skill => $demand) {
            $supply = $allSkillCounts[$skill] ?? 0;
            $bubbleData[] = [
                'skill' => $skill,
                'demand' => $demand,
                'supply' => $supply,
                'size' => $demand, // bubble size = demand
            ];
        }

        // --- Word Cloud: Job Roles and Skills among Employed Graduates ---
        $employedGraduates = \App\Models\Graduate::where('employment_status', 'Employed')->get();
        $jobRoles = $employedGraduates->pluck('current_job_title')->filter()->countBy();
        $employedGraduateIds = $employedGraduates->pluck('id');
        $employedSkills = \App\Models\GraduateSkill::whereIn('graduate_id', $employedGraduateIds)->pluck('skill_id');
        $skillCounts = \App\Models\Skill::whereIn('id', $employedSkills)->pluck('name', 'id')->mapWithKeys(function ($name, $id) use ($employedSkills) {
            return [$name => $employedSkills->where('id', $id)->count()];
        });

        // --- Bar Chart: Skill Demand by Job Title ---
        $skillDemandByRole = [];
        $jobs = \App\Models\Job::where('is_approved', 1)->get();
        foreach ($jobs as $job) {
            $jobSkills = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
            foreach ($jobSkills as $skillName) {
                $skillDemandByRole[$skillName][$job->job_title] = ($skillDemandByRole[$skillName][$job->job_title] ?? 0) + 1;
            }
        }
        // Format for chart: [{skill, role, demand}]
        $barChartData = [];
        foreach ($skillDemandByRole as $skill => $roles) {
            foreach ($roles as $role => $count) {
                $barChartData[] = [
                    'skill' => $skill,
                    'role' => $role,
                    'demand' => $count,
                ];
            }
        }

        // --- Top Skills Ranking ---
        $topSkillDemand = $skillsFlat->sortDesc()->take(10);
        $topSkillSupply = $allSkillCounts->sortDesc()->take(10);

        return response()->json([
            'heatmap' => $heatmap,
            'bubbleData' => $bubbleData,
            'jobRolesWordCloud' => $jobRoles,
            'skillsWordCloud' => $skillCounts,
            'barChartData' => $barChartData,
            'topSkillDemand' => $topSkillDemand,
            'topSkillSupply' => $topSkillSupply,
        ]);
    }

    // public function employmentByProgram()
    // {
    //     // // Example: Get all programs and count employed/unemployed graduates per program
    //     // $programs = \App\Models\Program::withCount([
    //     //     'graduates as employed_count' => function ($q) {
    //     //         $q->whereHas('jobApplications', function($q2) {
    //     //             $q2->where('status', 'hired');
    //     //         });
    //     //     },
    //     //     'graduates as unemployed_count' => function ($q) {
    //     //         $q->whereDoesntHave('jobApplications', function($q2) {
    //     //             $q2->where('status', 'hired');
    //     //         });
    //     //     }
    //     // ])->get();

    //     // // Prepare data for chart
    //     // $programNames = $programs->pluck('name');
    //     // $employed = $programs->pluck('employed_count');
    //     // $unemployed = $programs->pluck('unemployed_count');

    //     $programNames = ['BSIT', 'BSBA', 'BSED', 'BSN'];
    //     $employed = [120, 80, 60, 90];
    //     $unemployed = [30, 40, 20, 10];


    //     return Inertia::render('Admin/Reports/Reports', [
    //         // ...other props
    //         'programNames' => $programNames,
    //         'employedByProgram' => $employed,
    //         'unemployedByProgram' => $unemployed,
    //     ]);
    // }
}
