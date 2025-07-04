<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Program;
use App\Models\Job;
use App\Models\GraduateSkill;
use App\Models\Skill;
use App\Models\Sector;
use App\Models\Location;
use App\Models\JobInvitation;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PesoReportsController extends Controller
{
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
        $referralByLocation = JobInvitation::where('status', 'hired')
            ->with('graduate')
            ->get()
            ->groupBy(fn($invite) => $invite->graduate->location ?? 'Unknown')
            ->map(fn($invites) => count($invites))
            ->toArray();

        // 5. Referral success rate heatmap (successful referrals / total referrals per location)
        $referralSuccessHeatmap = [];
        $allReferrals = JobInvitation::with('graduate')->get()->groupBy(fn($invite) => $invite->graduate->location ?? 'Unknown');
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
