<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\Job;
use App\Models\JobApplicationStage;
use App\Models\Institution;
use App\Models\GraduateSkill;
use App\Models\GraduateEducation;
use App\Models\Skill;
use App\Models\Sector;
use App\Models\Location;
use App\Models\JobInvitation;
use App\Models\Referral;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\ApplicantScreeningService;


class PesoReportsController extends Controller
{

    public function index()
    {
        return Inertia::render('Admin/Reports/Home');
    }

    public function overview()
    {
        return Inertia::render('Admin/Reports/SummaryOverview');
    }


    public function employmentData(Request $request)
    {
        $year = $request->input('year');
        $institutionId = $request->input('institution_id');
        $programIds = $request->input('program_ids', []);
        $programId = $request->input('program_id');
        $status = $request->input('status');

        if (!is_array($programIds)) {
            $programIds = [$programIds];
        }
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
        if (!empty($programIds)) {
            $graduatesQuery->whereIn('program_id', $programIds);
        } elseif ($programId) {
            $graduatesQuery->where('program_id', $programId);
        }
        if ($status) {
            $graduatesQuery->where('employment_status', $status);
        }

        \Log::info('Schoolwise SQL:', [
            'sql' => $graduatesQuery->toSql(),
            'bindings' => $graduatesQuery->getBindings()
        ]);
        \Log::info('Filtering by program_ids', ['program_ids' => $programIds]);
        \Log::info('Graduates count', ['count' => $graduatesQuery->count()]);

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
                    $q->whereHas('jobApplications.job.company', function ($q2) use ($sector) {
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
            $jobs = \App\Models\Job::whereHas('company', function ($q) use ($sector) {
                $q->where('sector_id', $sector->id);
            })
                ->where('is_approved', 1)
                ->get();
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
        if (!empty($programIds)) {
            $allGraduatesQuery->whereIn('program_id', $programIds);
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
        if (!empty($programIds)) {
            $hiredGraduatesQuery->whereIn('program_id', $programIds);
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
            ->whereNotNull('is_approved')
            ->with(['programs', 'category', 'sector'])
            ->orderByDesc('created_at')
            ->get();

        $roleGroups = $allJobs->groupBy('job_title');

        $inDemandJobs = $roleGroups->map(function ($jobs, $role) {
            $firstJob = $jobs->first();
            $programFields = [];
            foreach ($firstJob->programs as $program) {
                $fields = \App\Models\GraduateEducation::where('program', $program->name)
                    ->pluck('level_of_education')
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
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // --- KPIs (same logic as ManageJobReferralsController) ---
        $totalReferrals = $query->count();
        $successfulReferralsKPI = Referral::where('status', 'success')->count();
        $successRate = $totalReferrals > 0 ? round(($successfulReferralsKPI / $totalReferrals) * 100, 2) : 0;

        $successfulReferrals = Referral::with([
            'graduate.user',
            'job.company',
            'graduate.graduateSkills.skill',
            'job.programs',
            'graduate.experience',
            'graduate.employmentPreference',
            'graduate.jobSearchHistory',
        ])->where('status', 'success')->get();


        $matchDetailsWordCloud = [];
        $stackedFeedbackByScore = [
            'Low (0-30)' => [],
            'Medium (31-60)' => [],
            'High (61-100)' => [],
        ];

        foreach ($successfulReferrals as $ref) {
            $matchScore = 0;
            $matchDetails = [];
            $criteria = 0;
            $graduate = $ref->graduate;
            $job = $ref->job;

            if (!$graduate) continue;
            $preferences = $graduate->employmentPreference;
            if (!$preferences) {
                \Log::info('No employment preference for graduate', ['graduate_id' => $graduate->id]);
                continue;
            }
            \Log::info('Graduate preferences', [
                'graduate_id' => $graduate->id,
                'min_salary' => $preferences->employment_min_salary,
                'max_salary' => $preferences->employment_max_salary,
                'work_environment' => $preferences->work_environment,
                'job_type' => $preferences->job_type,
            ]);

            if ($graduate && $job) {
                $preferences = $graduate->employmentPreference;
                // Skills
                $criteria++;
                $graduateSkills = $graduate->graduateSkills->pluck('skill.name')->filter()->unique()->toArray();
                $jobSkills = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
                $skillMatch = false;
                foreach ($graduateSkills as $skill) {
                    if (stripos(json_encode($jobSkills), $skill) !== false) {
                        $matchDetails[] = 'Skills';
                        $skillMatch = true;
                        break;
                    }
                }
                if ($skillMatch) $matchScore++;

                // Education (Program)
                $criteria++;
                $program = $graduate->program_id ? \App\Models\Program::find($graduate->program_id)->name ?? null : null;
                $educationMatch = $program && stripos($job->job_requirements, $program) !== false;
                if ($educationMatch) {
                    $matchDetails[] = 'Education';
                    $matchScore++;
                }

                // Experience
                $criteria++;
                $experiences = $graduate->experience ? $graduate->experience->pluck('job_title')->filter()->unique()->toArray() : [];
                $experienceMatch = false;
                foreach ($experiences as $title) {
                    if (stripos($job->job_title, $title) !== false) {
                        $matchDetails[] = 'Experience';
                        $experienceMatch = true;
                        break;
                    }
                }
                if ($experienceMatch) $matchScore++;

                // Past Search Keywords
                $criteria++;
                $pastKeywords = $graduate->jobSearchHistory ? $graduate->jobSearchHistory->pluck('keywords')->unique()->toArray() : [];
                $pastKeywordMatch = false;
                foreach ($pastKeywords as $keyword) {
                    if (
                        stripos($job->job_title, $keyword) !== false ||
                        stripos($job->job_description, $keyword) !== false
                    ) {
                        $matchDetails[] = 'Past Search';
                        $pastKeywordMatch = true;
                        break;
                    }
                }
                if ($pastKeywordMatch) $matchScore++;
            }
            $match_percentage = $criteria > 0 ? round(($matchScore / $criteria) * 100) : 0;

            // Word Cloud: count each match detail
            foreach ($matchDetails as $detail) {
                $matchDetailsWordCloud[$detail] = ($matchDetailsWordCloud[$detail] ?? 0) + 1;
            }

            // Stacked Column: group feedback by match score bucket
            $bucket = 'Low (0-30)';
            if ($match_percentage > 60) $bucket = 'High (61-100)';
            elseif ($match_percentage > 30) $bucket = 'Medium (31-60)';
            $feedback = $ref->referral_feedback ?? null;
            if ($feedback) {
                $stackedFeedbackByScore[$bucket][$feedback] = ($stackedFeedbackByScore[$bucket][$feedback] ?? 0) + 1;
            }
        }

        // Format stackedFeedbackByScore for charting
        $stackedColumnData = [];
        foreach ($stackedFeedbackByScore as $bucket => $feedbacks) {
            $stackedColumnData[] = [
                'bucket' => $bucket,
                'feedbacks' => $feedbacks,
            ];
        }

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
        if ($request->date_from && $request->date_to) {
            $referralsQuery->whereBetween('created_at', [$request->date_from, $request->date_to]);
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

        if ($request->date_from && $request->date_to) {
            $applicationsQuery->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // Funnel Chart Data: Stages
        $applications = $applicationsQuery->get();

        $latestStages = JobApplicationStage::whereIn('job_application_id', $applications->pluck('id'))
            ->select('job_application_id', 'stage', 'changed_at')
            ->orderBy('changed_at', 'desc')
            ->get()
            ->groupBy('job_application_id')
            ->map(fn($stages) => $stages->first()->stage);


        $totalApplied = $applications->filter(function ($app) {
            return in_array(strtolower($app->stage), ['applied', 'applying']) ||
                in_array(strtolower($app->status), ['applied', 'applying']);
        })->count();
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


            return response()->json([
                'totalReferrals' => $totalReferrals,
                'successfulReferrals' => $successfulReferralsKPI,
                'successRate' => $successRate,
                'matchDetailsWordCloud' => $matchDetailsWordCloud,
                'stackedColumnData' => $stackedColumnData,

                'funnelData' => $funnelData,
                'barSuccessData' => $barSuccessData,
                'pieSourceData' => $pieSourceData,
                'treemapSourceData' => $treemapSourceData,
                'lineTrendData' => $lineTrendData,
                'areaTrendData' => $areaTrendData,
                'roleStats' => $roleStats,

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
            $jobs = \App\Models\Job::whereHas('company', function ($q) use ($industry) {
                $q->where('sector_id', $industry->id);
            })->where('is_approved', 1)->get();
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
        $jobSkills = \App\Models\Job::where('is_approved', 1)
            ->whereNotNull('is_approved')
            ->pluck('skills')->toArray();
        $skillsFlat = collect($jobSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy();

        $supplyCounts = \App\Models\GraduateSkill::select('skill_id')
            ->groupBy('skill_id')
            ->selectRaw('skill_id, COUNT(DISTINCT graduate_id) as supply')
            ->pluck('supply', 'skill_id');

        $skillNames = \App\Models\Skill::pluck('name', 'id');

        $allSkillCounts = collect($skillNames)->mapWithKeys(function ($name, $id) use ($supplyCounts) {
            return [strtolower(trim($name)) => $supplyCounts[$id] ?? 0];
        });

        $skillsFlat = collect($jobSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy();

        $bubbleData = [];
        foreach ($skillsFlat as $skill => $demand) {
            $supply = $allSkillCounts[$skill] ?? 0;
            $bubbleData[] = [
                'skill' => $skill,
                'demand' => $demand,
                'supply' => $supply,
                'size' => $demand,
            ];
        }

        // --- Word Cloud: Job Roles and Skills among Employed Graduates ---
        $employedGraduates = \App\Models\Graduate::where('employment_status', 'Employed')->get();
        $jobRoles = $employedGraduates->pluck('current_job_title')->filter()->countBy();
        $employedGraduateIds = $employedGraduates->pluck('id');
        $employedSkills = \App\Models\GraduateSkill::whereIn('graduate_id', $employedGraduateIds)->pluck('skill_id');
        $supplyCounts = \App\Models\GraduateSkill::whereIn('graduate_id', $employedGraduateIds)
            ->select('skill_id')
            ->groupBy('skill_id')
            ->selectRaw('skill_id, COUNT(DISTINCT graduate_id) as supply')
            ->pluck('supply', 'skill_id');

        $skillNames = \App\Models\Skill::pluck('name', 'id');

        $skillCounts = collect($skillNames)->mapWithKeys(function ($name, $id) use ($supplyCounts) {
            return [$name => $supplyCounts[$id] ?? 0];
        });

        // --- Bar Chart: Skill Demand by Job Title ---
        $skillDemandByRole = [];
        $jobs = \App\Models\Job::where('is_approved', 1)
            ->whereNotNull('is_approved')
            ->get();
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

    public function careers()
    {
        // For Jetstream/Material-style filter dropdowns, etc.
        $educationLevels = \App\Models\Education::pluck('name');
        $programs = \App\Models\Program::pluck('name');
        $industries = \App\Models\Sector::all()->map(fn($i) => [
            'id' => $i->id,
            'name' => $i->name,
        ]);
        $jobRoles = \App\Models\Job::distinct()->pluck('job_title')->filter()->values();
        $graduates = \App\Models\Graduate::with(['graduateEducations', 'schoolYear'])->get();
        $grad = Graduate::with('schoolYear')->first();

        return Inertia::render('Admin/Reports/Career', [
            'educationLevels' => $educationLevels,
            'institutions' => Institution::all()->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->institution_name,
            ]),
            'programs' => $programs,
            'jobRoles' => $jobRoles,
            'industries' => $industries,
            'graduates' => $grad,

        ]);
    }

    public function careerData(Request $request)
    {
        // --- Bar Chart: Employment rates by level of education ---

        $graduates = \App\Models\Graduate::with(['graduateEducations', 'schoolYear'])->get();
        // Get all education levels
        $educationLevels = \App\Models\Education::pluck('name');
        $barChartData = [];

        $allLevels = \App\Models\GraduateEducation::pluck('level_of_education')->unique();
        \Log::info('All GraduateEducation Levels', $allLevels->toArray());

        foreach ($educationLevels as $level) {
            // Get graduates who have this education level
            $baseLevel = Str::lower(str_replace(["'s", "'S"], '', $level));
            $graduatesWithLevel = $graduates->filter(function ($grad) use ($baseLevel) {
                return $grad->graduateEducations->contains(function ($edu) use ($baseLevel) {
                    return Str::contains(Str::lower($edu->level_of_education), $baseLevel);
                });
            });

            $total = $graduatesWithLevel->count();
            $employed = $graduatesWithLevel->where('employment_status', 'Employed')->count();

            \Log::info('Education Level Stats', [
                'level' => $level,
                'total' => $total,
                'employed' => $employed,
            ]);
            $employmentRate = $total ? round(($employed / $total) * 100, 2) : 0;

            $barChartData[] = [
                'education' => $level,
                'employment_rate' => $employmentRate,
                'total' => $total,
                'employed' => $employed,
                'year' => $graduatesWithLevel->first()?->schoolYear?->school_year_range ?? null,
                'program' => $graduatesWithLevel->first()?->program?->name ?? null,
                'institution_name' => $graduatesWithLevel->first()?->institution?->institution_name ?? null,
            ];
        }

        // --- Radar Chart: Skills development vs employability by program ---
        $programs = \App\Models\Program::pluck('name');
        $radarTypes = ['Technical Skills', 'Soft Skills', 'Language Skills']; // You can fetch distinct types from GraduateSkill if needed
        $radarData = [];

        foreach ($programs as $programName) {
            $program = \App\Models\Program::where('name', $programName)->first();
            if (!$program) continue;

            $programGraduates = \App\Models\Graduate::where('program_id', $program->id)->pluck('id');
            $typeScores = [];
            foreach ($radarTypes as $type) {
                $count = \App\Models\GraduateSkill::whereIn('graduate_id', $programGraduates)
                    ->where('type', $type)
                    ->distinct('graduate_id')
                    ->count('graduate_id');
                $percentage = $programGraduates->count() ? round(($count / $programGraduates->count()) * 100, 2) : 0;
                $typeScores[] = $percentage;
            }
            $radarData[] = [
                'value' => $typeScores,
                'name' => $programName,
            ];
        }



        // --- Box Plot: Salary ranges across industries ---
        $industries = \App\Models\Sector::all();
        $industrySalaryBoxData = [];
        foreach ($industries as $industry) {
            $jobs = \App\Models\Job::where('is_approved', 1)
                ->whereNotNull('is_approved')
                ->whereHas('company', function ($q) use ($industry) {
                    $q->where('sector_id', $industry->id);
                })
                ->get();

            $salaries = $jobs->pluck('salary')->filter()->map(function ($salary) {
                if (is_array($salary)) {
                    return [
                        'min' => $salary['job_min_salary'] ?? null,
                        'max' => $salary['job_max_salary'] ?? null,
                    ];
                } elseif (is_object($salary)) {
                    return [
                        'min' => $salary->job_min_salary ?? null,
                        'max' => $salary->job_max_salary ?? null,
                    ];
                }
                return null;
            })->filter(fn($s) => $s['min'] !== null && $s['max'] !== null);

            $allSalaries = $salaries->flatMap(fn($s) => [$s['min'], $s['max']])->sort()->values();
            if ($allSalaries->count() > 0) {
                $industrySalaryBoxData[] = [
                    'industry' => $industry->name,
                    'min' => $allSalaries->min(),
                    'q1' => $allSalaries->slice(0, intval($allSalaries->count() * 0.25))->last(),
                    'median' => $allSalaries->median(),
                    'q3' => $allSalaries->slice(0, intval($allSalaries->count() * 0.75))->last(),
                    'max' => $allSalaries->max(),
                    'all' => $allSalaries->toArray(),
                ];
            }
        }

        // --- Stacked Bar: Entry, Mid, Senior salary per industry ---
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
                $jobs = \App\Models\Job::where('is_approved', 1)
                    ->whereNotNull('is_approved')
                    ->whereHas('company', function ($q) use ($industry) {
                        $q->where('sector_id', $industry->id);
                    })
                    ->get();
                $salaries = $jobs->pluck('salary')->filter()->map(function ($salary) {
                    if (is_array($salary)) {
                        return ($salary['job_min_salary'] ?? 0 + $salary['job_max_salary'] ?? 0) / 2;
                    } elseif (is_object($salary)) {
                        return (($salary->job_min_salary ?? 0) + ($salary->job_max_salary ?? 0)) / 2;
                    }
                    return 0;
                })->filter();
                if ($salaries->count()) {
                    $levelSalaries[$group] = round($salaries->avg(), 2);
                }
            }
            $industryLevelSalaries[] = [
                'industry' => $industry->name,
                'Entry' => $levelSalaries['Entry'],
                'Mid' => $levelSalaries['Mid'],
                'Senior' => $levelSalaries['Senior'],
            ];
        }

        // --- Histogram: Salary expectations (graduates) vs. offered (jobs) ---
        $graduateMin = \App\Models\EmploymentPreference::pluck('employment_min_salary')->filter()->toArray();
        $graduateMax = \App\Models\EmploymentPreference::pluck('employment_max_salary')->filter()->toArray();
        $jobMin = \App\Models\Job::whereNotNull('job_min_salary')->pluck('job_min_salary')->filter()->toArray();
        $jobMax = \App\Models\Job::whereNotNull('job_max_salary')->pluck('job_max_salary')->filter()->toArray();

        $salaryExpectations = [
            'graduateMin' => $graduateMin,
            'graduateMax' => $graduateMax,
            'jobMin' => $jobMin,
            'jobMax' => $jobMax,
        ];



        return response()->json([
            'barChartData' => $barChartData,
            'radarSkills' => $radarTypes,
            'radarData' => $radarData,
            'industrySalaryBoxData' => $industrySalaryBoxData,
            'industryLevelSalaries' => $industryLevelSalaries,
            'salaryExpectations' => $salaryExpectations,
            'radarSkills' => $radarTypes,
            'radarData' => $radarData,
            'graduates' => $graduates,

        ]);
    }

    public function diversity()
    {
        $industries = \App\Models\Sector::all()->map(fn($i) => [
            'id' => $i->id,
            'name' => $i->name,
        ]);




        $employerTypes = ['Small Business', 'Medium Enterprise', 'Large Corporation']; // Adjust as needed
        return Inertia::render('Admin/Reports/Diversity', [
            'industries' => $industries,
            'employerTypes' => $employerTypes,
            'educationLevels' => GraduateEducation::all()->map(fn($e) => [
                'id' => $e->id,
                'level_of_education' => $e->level_of_education,
            ]),
        ]);
    }

    public function diversityData(Request $request)
    {
        // --- Gender & Diversity Metrics: Stacked Column Chart ---
        $genders = ['Male', 'Female', 'Other'];
        $diversityGroups = \App\Models\Graduate::whereNotNull('ethnicity')->pluck('ethnicity')->unique()->filter()->values()->toArray();
        $employmentByGender = [];
        foreach ($genders as $gender) {
            $total = \App\Models\Graduate::where('gender', $gender)->count();
            $employed = \App\Models\Graduate::where('gender', $gender)->where('employment_status', 'Employed')->count();
            $employmentByGender[] = [
                'gender' => $gender,
                'total' => $total,
                'employed' => $employed,
                'employment_rate' => $total ? round(($employed / $total) * 100, 2) : 0,
            ];
        }

        // --- Pie Chart: Distribution of Diverse Groups in Industries ---
        $industries = \App\Models\Sector::all();
        $diversityIndustryPie = [];
        foreach ($industries as $industry) {
            $graduates = \App\Models\Graduate::whereHas('company', function ($q) use ($industry) {
                $q->where('sector_id', $industry->id);
            })->get();
            $groupCounts = [];
            foreach ($diversityGroups as $ethnicity) {
                $groupCounts[$ethnicity] = $graduates->where('ethnicity', $ethnicity)->count();
            }
            $diversityIndustryPie[] = [
                'industry' => $industry->name,
                'groups' => $groupCounts,
            ];
        }

        // --- Clustered Bar Chart: Job Seekers by Age, Education, Experience ---
        $jobSeekers = \App\Models\Graduate::all();
        $ageGroups = ['18-24', '25-34', '35-44', '45-54', '55+'];
        $educationLevels = \App\Models\Education::pluck('name')->toArray();
        $experienceLevels = ['None', '1-2 years', '3-5 years', '6-10 years', '10+ years'];

        $ageCounts = array_fill_keys($ageGroups, 0);
        foreach ($jobSeekers as $grad) {
            $dob = $grad->dob ?? null;
            $age = null;
            if ($dob) {
                $age = Carbon::parse($dob)->age;
            }
            if ($age !== null) {
                if ($age < 25) $ageCounts['18-24']++;
                elseif ($age < 35) $ageCounts['25-34']++;
                elseif ($age < 45) $ageCounts['35-44']++;
                elseif ($age < 55) $ageCounts['45-54']++;
                else $ageCounts['55+']++;
            }
        }

        $educationCounts = array_fill_keys($educationLevels, 0);
        foreach ($educationLevels as $edu) {
            $baseEdu = \Illuminate\Support\Str::lower(str_replace(["'s", "'S"], '', $edu));
            $graduatesWithLevel = $jobSeekers->filter(function ($grad) use ($baseEdu) {
                return $grad->graduateEducations->contains(function ($ge) use ($baseEdu) {
                    return \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($ge->level_of_education), $baseEdu);
                });
            });
            $educationCounts[$edu] = $graduatesWithLevel->count();
        }

        $experienceCounts = array_fill_keys($experienceLevels, 0);
        foreach ($jobSeekers as $grad) {
            $totalYears = 0;
            if ($grad->experience && $grad->experience->count()) {
                foreach ($grad->experience as $exp) {
                    $start = $exp->start_date ? Carbon::parse($exp->start_date) : null;
                    $end = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                    if ($start && $end && $end->greaterThan($start)) {
                        $years = abs($end->diffInMonths($start)) / 12;
                        \Log::info('Experience calculation', [
                            'graduate_id' => $grad->id,
                            'start' => $start,
                            'end' => $end,
                            'years' => $years
                        ]);
                        $totalYears += $years;
                    } else {
                        \Log::warning('Experience skipped due to invalid dates', [
                            'graduate_id' => $grad->id,
                            'graduate_name' => $grad->first_name,
                            'start_date' => $exp->start_date,
                            'end_date' => $exp->end_date,
                        ]);
                    }
                }
            }
            $totalYears = round($totalYears, 2);
            \Log::info('Graduate Experience Years', [
                'graduate_id' => $grad->id,
                'totalYears' => $totalYears
            ]);
            if ($totalYears == 0) {
                $experienceCounts['None']++;
            } elseif ($totalYears > 0 && $totalYears < 3) {
                $experienceCounts['1-2 years']++;
            } elseif ($totalYears >= 3 && $totalYears < 6) {
                $experienceCounts['3-5 years']++;
            } elseif ($totalYears >= 6 && $totalYears < 11) {
                $experienceCounts['6-10 years']++;
            } else {
                $experienceCounts['10+ years']++;
            }
        }
        $clusteredBarData = [
            'age' => $ageCounts,
            'education' => $educationCounts,
            'experience' => $experienceCounts,
        ];

        // --- Line Chart: Job Applications Trends by Demographic Group ---
        $applications = \App\Models\JobApplication::with('graduate')->get();
        $monthlyTrends = [];
        foreach ($applications as $app) {
            $month = $app->created_at ? $app->created_at->format('Y-m') : 'Unknown';
            $gender = $app->graduate->gender ?? 'Unknown';
            if (!isset($monthlyTrends[$month])) $monthlyTrends[$month] = [];
            if (!isset($monthlyTrends[$month][$gender])) $monthlyTrends[$month][$gender] = 0;
            $monthlyTrends[$month][$gender]++;
        }
        $lineDemographicTrends = [];
        foreach ($monthlyTrends as $month => $genders) {
            foreach ($genders as $gender => $count) {
                $lineDemographicTrends[] = [
                    'month' => $month,
                    'group' => $gender,
                    'count' => $count,
                ];
            }
        }

        // --- Bar Chart: Referred Candidates by Demographics ---
        $referrals = \App\Models\Referral::with('graduate')->get();
        $barReferralDemographics = [
            'age' => array_fill_keys($ageGroups, 0),
            'gender' => ['Male' => 0, 'Female' => 0, 'Other' => 0],
            'education' => array_fill_keys($educationLevels, 0),
        ];
        foreach ($referrals as $ref) {
            $grad = $ref->graduate;
            if (!$grad) continue;
            // Age
            $dob = $grad->dob ?? null;
            $age = $dob ? Carbon::parse($dob)->age : null;
            if ($age !== null) {
                if ($age < 25) $barReferralDemographics['age']['18-24']++;
                elseif ($age < 35) $barReferralDemographics['age']['25-34']++;
                elseif ($age < 45) $barReferralDemographics['age']['35-44']++;
                elseif ($age < 55) $barReferralDemographics['age']['45-54']++;
                else $barReferralDemographics['age']['55+']++;
            }
            // Gender
            $gender = $grad->gender ?? 'Other';
            if (isset($barReferralDemographics['gender'][$gender])) $barReferralDemographics['gender'][$gender]++;
            // Education
            $level = $grad->graduateEducations->first()->level_of_education ?? null;
            if ($level) {
                foreach ($educationLevels as $edu) {
                    $baseEdu = \Illuminate\Support\Str::lower(str_replace(["'s", "'S"], '', $edu));
                    if ($grad->graduateEducations->contains(function ($ge) use ($baseEdu) {
                        return \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($ge->level_of_education), $baseEdu);
                    })) {
                        $barReferralDemographics['education'][$edu]++;
                        break;
                    }
                }
            }
        }

        // --- Radar Chart: Demographic Attributes vs Referral Success ---
        // AGE GROUPS
        $radarDemographicLabels = ['18-24', '25-34', '35-44', '45-54', '55+'];
        $radarDemographicData = [];
        foreach ($radarDemographicLabels as $ageGroup) {
            $total = $barReferralDemographics['age'][$ageGroup] ?? 0;
            $success = $referrals->filter(function ($ref) use ($ageGroup) {
                $dob = $ref->graduate->dob ?? null;
                $age = $dob ? Carbon::parse($dob)->age : null;
                if ($ageGroup == '18-24') return $age !== null && $age < 25 && $ref->status === 'success';
                if ($ageGroup == '25-34') return $age !== null && $age >= 25 && $age < 35 && $ref->status === 'success';
                if ($ageGroup == '35-44') return $age !== null && $age >= 35 && $age < 45 && $ref->status === 'success';
                if ($ageGroup == '45-54') return $age !== null && $age >= 45 && $age < 55 && $ref->status === 'success';
                if ($ageGroup == '55+') return $age !== null && $age >= 55 && $ref->status === 'success';
                return false;
            })->count();
            $rate = $total ? round(($success / $total) * 100, 2) : 0;
            $radarDemographicData[] = $rate;
        }

        // GENDER GROUPS
        $radarGenderLabels = array_keys($barReferralDemographics['gender']);
        $radarGenderData = [];
        foreach ($radarGenderLabels as $gender) {
            $total = $barReferralDemographics['gender'][$gender] ?? 0;
            $success = $referrals->filter(function ($ref) use ($gender) {
                return ($ref->graduate->gender ?? 'Other') === $gender && $ref->status === 'success';
            })->count();
            $rate = $total ? round(($success / $total) * 100, 2) : 0;
            $radarGenderData[] = $rate;
        }

        // EDUCATION LEVELS
        $radarEducationLabels = array_keys($barReferralDemographics['education']);
        $radarEducationData = [];
        foreach ($radarEducationLabels as $edu) {
            $total = $barReferralDemographics['education'][$edu] ?? 0;
            $success = $referrals->filter(function ($ref) use ($edu) {
                $baseEdu = \Illuminate\Support\Str::lower(str_replace(["'s", "'S"], '', $edu));
                return $ref->graduate
                    && $ref->graduate->graduateEducations
                    && $ref->graduate->graduateEducations->contains(function ($ge) use ($baseEdu) {
                        return \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($ge->level_of_education), $baseEdu);
                    })
                    && $ref->status === 'success';
            })->count();
            $rate = $total ? round(($success / $total) * 100, 2) : 0;
            $radarEducationData[] = $rate;
        }


        // --- Job Seeker and Role Alignment: Scatter Plot & Bar Chart ---
        // Scatter: Each point is a job seeker, x = skills matched, y = required skills for job
        $scatterData = [];
        $barAlignment = [
            'Meets' => 0,
            'Exceeds' => 0,
            'Falls Short' => 0,
        ];
        $jobApplications = \App\Models\JobApplication::with(['graduate.graduateSkills', 'job'])->get();
        foreach ($jobApplications as $app) {
            $graduateSkills = $app->graduate?->graduateSkills->pluck('skill.name')->filter()->unique()->toArray() ?? [];
            $jobSkills = is_array($app->job?->skills) ? $app->job->skills : (json_decode($app->job?->skills, true) ?: []);
            $matchedSkills = array_values(array_intersect($graduateSkills, $jobSkills));
            $matched = count($matchedSkills);
            $required = count($jobSkills);

            $scatterData[] = [
                'applicant' => $app->graduate?->first_name . ' ' . $app->graduate?->last_name,
                'matched' => $matched,
                'required' => $required,
                'matchedSkills' => $matchedSkills,
                'requiredSkills' => $jobSkills,
            ];

            if ($required > 0) {
                if ($matched == $required) $barAlignment['Meets']++;
                elseif ($matched > $required) $barAlignment['Exceeds']++;
                else $barAlignment['Falls Short']++;
            }
        }

        return response()->json([
            'employmentByGender' => $employmentByGender,
            'diversityIndustryPie' => $diversityIndustryPie,
            'scatterData' => $scatterData,
            'barAlignment' => $barAlignment,
            // 'employerTypePie' => $employerTypePie,
            // 'employerTypeBar' => $jobCreation
            'clusteredBarData' => $clusteredBarData,
            'lineDemographicTrends' => $lineDemographicTrends,
            'barReferralDemographics' => $barReferralDemographics,

            'radarDemographicLabels' => $radarDemographicLabels,
            'radarGenderLabels' => $radarGenderLabels,
            'radarEducationLabels' => $radarEducationLabels,

            'radarDemographicData' => $radarDemographicData,
            'radarGenderData' => $radarGenderData,
            'radarEducationData' => $radarEducationData,
        ]);
    }

    public function jobmarket()
    {
        $industries = \App\Models\Sector::pluck('name');
        return Inertia::render('Admin/Reports/JobMarket', [
            'industries' => $industries,

        ]);
    }

    public function jobMarketData(Request $request)
    {
        // --- Job Seeker and Role Alignment: Scatter Plot & Bar Chart ---
        // Scatter: Each point is a job seeker, x = skills matched, y = required skills for job
        $scatterData = [];
        $barAlignment = [
            'Meets' => 0,
            'Exceeds' => 0,
            'Falls Short' => 0,
        ];
        $jobApplications = \App\Models\JobApplication::with(['graduate.graduateSkills', 'job'])->get();
        foreach ($jobApplications as $app) {
            $graduateSkills = $app->graduate?->graduateSkills->pluck('skill.name')->filter()->unique()->toArray() ?? [];
            $jobSkills = is_array($app->job?->skills) ? $app->job->skills : (json_decode($app->job?->skills, true) ?: []);
            $matchedSkills = array_values(array_intersect($graduateSkills, $jobSkills));
            $matched = count($matchedSkills);
            $required = count($jobSkills);

            $scatterData[] = [
                'applicant' => $app->graduate?->first_name . ' ' . $app->graduate?->last_name,
                'matched' => $matched,
                'required' => $required,
                'matchedSkills' => $matchedSkills,
                'requiredSkills' => $jobSkills,
                'job_title' => $app->job?->job_title,
            ];

            if ($required > 0) {
                if ($matched == $required) $barAlignment['Meets']++;
                elseif ($matched > $required) $barAlignment['Exceeds']++;
                else $barAlignment['Falls Short']++;
            }
        }
        // --- Matching Success Rate ---
        // Funnel Chart Data

        $jobApplications = \App\Models\JobApplication::all();




        $funnelStages = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];
        $funnelData = [];
        foreach ($funnelStages as $stage) {
            if ($stage === 'applied') {
                // Count as applied if status or stage is 'applied' or 'applying'
                $count = $jobApplications->filter(function ($app) {
                    return in_array(strtolower($app->status), ['applied', 'applying']) ||
                        in_array(strtolower($app->stage), ['applied', 'applying']);
                })->count();
            } else {
                // For other stages, match by stage column
                $count = $jobApplications->filter(function ($app) use ($stage) {
                    return strtolower($app->stage) === $stage;
                })->count();
            }
            $funnelData[] = [
                'stage' => ucfirst($stage),
                'count' => $count,
            ];



            // Pie Chart: Successful vs Unmatched (using matching criteria)

            $matchedApplications = 0;
            $unmatchedApplications = 0;
            $matchThreshold = 60; // percent

            foreach ($jobApplications as $app) {
                $criteria = 0;
                $matchScore = 0;

                $graduate = $app->graduate;
                $job = $app->job;

                if (!$graduate || !$job) continue;

                $screening = (new ApplicantScreeningService())->screen($graduate, $job);



                $match_percentage = $screening['match_percentage'] ?? 0;

                if ($match_percentage >= $matchThreshold) {
                    $matchedApplications++;
                } else {
                    $unmatchedApplications++;
                }
            }

            $totalApplications = $matchedApplications + $unmatchedApplications;
            $matchingSuccessRate = $totalApplications > 0 ? round(($matchedApplications / $totalApplications) * 100, 2) : 0;

            $pieMatchData = [
                ['name' => 'Matched', 'value' => $matchedApplications],
                ['name' => 'Unmatched', 'value' => $unmatchedApplications],
            ];


            // --- Future Job Trends ---
            // Line & Area Chart: Project future job openings (simple projection based on past 12 months)
            $jobsByMonth = \App\Models\Job::where('is_approved', 1)

                ->whereNotNull('is_approved')
                ->orderBy('created_at')
                ->get()
                ->groupBy(function ($job) {
                    return $job->created_at->format('Y-m');
                });

            $lineTrendData = [];
            $areaTrendData = [];
            foreach ($jobsByMonth as $month => $jobs) {
                $lineTrendData[] = ['month' => $month, 'openings' => $jobs->count()];
                $areaTrendData[] = ['month' => $month, 'openings' => $jobs->count()];
            }

            // Stacked Area Chart: Job openings by industry and skills over time
            $jobs = \App\Models\Job::where('is_approved', 1)
                ->whereHas('company')
                ->with(['company'])
                ->orderBy('created_at')
                ->get(['id', 'company_id', 'created_at']);

            $sectors = \App\Models\Sector::pluck('name', 'id');

            // Group jobs by month and company sector
            $industryAreaData = [];
            foreach ($jobs as $job) {
                $month = $job->created_at->format('Y-m');
                $sectorId = $job->company ? $job->company->sector_id : null;
                $sector = $sectors[$sectorId] ?? 'Unknown';
                $industryAreaData[$sector][$month] = ($industryAreaData[$sector][$month] ?? 0) + 1;
            }

            // Format for chart: [{name: sector, data: [counts per month]}, ...]
            $months = collect($jobs)->pluck('created_at')->map(fn($d) => $d->format('Y-m'))->unique()->sort()->values();
            $industryAreaChart = [];
            foreach ($industryAreaData as $sector => $monthCounts) {
                $data = [];
                foreach ($months as $month) {
                    $data[] = $monthCounts[$month] ?? 0;
                }
                $industryAreaChart[] = [
                    'name' => $sector,
                    'type' => 'line',
                    'stack' => 'total',
                    'areaStyle' => [],
                    'data' => $data,
                ];
            }

            // Get all approved jobs with skills and created_at
            $jobs = \App\Models\Job::where('is_approved', 1)
                ->orderBy('created_at')
                ->get(['id', 'skills', 'created_at']);

            $skillAreaData = [];
            foreach ($jobs as $job) {
                $month = $job->created_at->format('Y-m');
                $skills = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
                foreach ($skills as $skill) {
                    $skillAreaData[$skill][$month] = ($skillAreaData[$skill][$month] ?? 0) + 1;
                }
            }

            // Get all months
            $months = collect($jobs)->pluck('created_at')->map(fn($d) => $d->format('Y-m'))->unique()->sort()->values();
            $skillAreaChart = [];
            foreach ($skillAreaData as $skill => $monthCounts) {
                $data = [];
                foreach ($months as $month) {
                    $data[] = $monthCounts[$month] ?? 0;
                }
                $skillAreaChart[] = [
                    'name' => $skill,
                    'type' => 'line',
                    'stack' => 'total',
                    'areaStyle' => [],
                    'data' => $data,
                ];
            }

            // --- Employer Preferences ---
            // Bar Chart: Most requested qualifications/certifications
            $jobs = \App\Models\Job::where('is_approved', 1)
                ->whereNotNull('is_approved')
                ->get(['job_requirements', 'skills']);

            // Collect all requirements and skills
            $allQualifications = collect();

            foreach ($jobs as $job) {
                // Job requirements (may be JSON, array, or string)
                $requirements = [];
                if (is_array($job->job_requirements)) {
                    $requirements = $job->job_requirements;
                } elseif (is_string($job->job_requirements) && $job->job_requirements !== '') {
                    $decoded = json_decode($job->job_requirements, true);
                    $requirements = is_array($decoded) ? $decoded : [$job->job_requirements];
                }

                // Skills (may be JSON, array, or string)
                $skills = [];
                if (is_array($job->skills)) {
                    $skills = $job->skills;
                } elseif (is_string($job->skills) && $job->skills !== '') {
                    $decoded = json_decode($job->skills, true);
                    $skills = is_array($decoded) ? $decoded : [$job->skills];
                }

                // Merge and normalize
                $allQualifications = $allQualifications->merge($requirements)->merge($skills);
            }

            // Count and sort
            $qualificationCounts = $allQualifications
                ->filter()
                ->map(fn($q) => trim($q))
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(10);

            $barQualificationData = [];
            foreach ($qualificationCounts as $qualification => $count) {
                $barQualificationData[] = [
                    'qualification' => $qualification,
                    'count' => $count,
                ];
            }

            // Radar Chart: Employer priorities (skills, experience, education)
            $radarPriorityLabels = ['Skills', 'Experience', 'Education'];
            $radarPriorityData = [];

            // Skills: jobs where skills is not null or not empty
            $skillsCount = \App\Models\Job::where('is_approved', 1)
                ->whereNotNull('skills')
                ->where('skills', '!=', '[]')
                ->count();
            $radarPriorityData[] = $skillsCount;

            // Experience: jobs where job_requirements contains 'experience' (case-insensitive)
            $experienceCount = \App\Models\Job::where('is_approved', 1)
                ->whereNotNull('job_requirements')
                ->where('job_requirements', 'like', '%experience%')
                ->count();
            $radarPriorityData[] = $experienceCount;

            // Education: jobs where job_requirements contains 'education' or 'degree' (case-insensitive)
            $educationCount = \App\Models\Job::where('is_approved', 1)
                ->whereNotNull('job_requirements')
                ->where(function ($q) {
                    $q->where('job_requirements', 'like', '%education%')
                        ->orWhere('job_requirements', 'like', '%degree%');
                })
                ->count();
            $radarPriorityData[] = $educationCount;


            return response()->json([

                'scatterData' => $scatterData,
                'barAlignment' => $barAlignment,

                // Matching Success Rate
                'matchingSuccessRate' => $matchingSuccessRate,
                'funnelData' => $funnelData,
                'pieMatchData' => $pieMatchData,

                // Future Job Trends
                'lineTrendData' => $lineTrendData,
                'areaTrendData' => $areaTrendData,
                'industryAreaChart' => $industryAreaChart,
                'skillAreaChart' => $skillAreaChart,

                // Employer Preferences
                'barQualificationData' => $barQualificationData,
                'radarPriorityLabels' => $radarPriorityLabels,
                'radarPriorityData' => $radarPriorityData,

            ]);
        }
    }
}
