<?php


namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\JobType;
use App\Models\EmploymentPreference;
use App\Models\Program;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobApplicationStage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyReportsController extends Controller
{

   public function list($userId)
    {
        // You can pass any data needed to the Vue page
        return Inertia::render('Company/Reports/ListOfReports');
        
    }

    public function overview(Request $request)
    {
        $user = auth()->user();
        
        // Get the company_id of the logged-in user via HR account
        $hr = $user->hr;
        $companyId = $hr->company_id;
        
        
        $jobTypes = JobType::select('id', 'type')->get();

        $jobs = Job::where('company_id', $companyId)
            ->select(['id', 'job_title', 'job_type', 'status'])
            ->with('jobTypes:id,type')
            ->get();

        // KPI Metrics for jobs posted by this company
        $totalOpenings = Job::where('company_id', $companyId)
                        ->count();

        $activeListings = Job::where('company_id', $companyId)
                            ->where('status', 'open')
                            ->count();

        $rolesFilled = Job::where('company_id', $companyId)
                        ->withCount('applications')
                        ->where('status', 'filled')
                        ->count();

        // Job types for pie chart

        foreach ($jobTypes as $type) {
        $typeCounts[$type->type] =
                // Count jobs where job_type string matches
                $jobs->where('job_type', $type->type)->count()
                // Plus jobs where jobTypes relation contains this type
                + $jobs->filter(function ($job) use ($type) {
                    return $job->jobTypes->contains('type', $type->type);
                })->count();
        }


        return Inertia::render('Company/Reports/JobOverview', [
            'totalOpenings' => $totalOpenings,
            'activeListings' => $activeListings,
            'rolesFilled' => $rolesFilled,
            'typeCounts' => $typeCounts,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            
        ]);
    }

    public function department(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Bar Chart: Number of job openings per department
        $departmentCounts = Job::selectRaw('departments.department_name as department, COUNT(jobs.id) as total')
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->where('jobs.status', 'open')
            ->groupBy('departments.department_name')
            ->get();

        // Stacked Column Chart: Job postings by role level per department
        $roleLevels = ['Entry-level','Intermediate', 'Mid-level', 'Senior/Executive'];
        $stackedData = [];
        foreach ($roleLevels as $level) {
            $stackedData[$level] = Job::selectRaw('departments.department_name as department, COUNT(jobs.id) as total')
                ->join('departments', 'jobs.department_id', '=', 'departments.id')
                ->where('jobs.company_id', $companyId)
                ->where('jobs.status', 'open')
                ->where('jobs.job_experience_level', $level)
                ->groupBy('departments.department_name')
                ->pluck('total', 'department')
                ->toArray();
        }

        return Inertia::render('Company/Reports/DeptWise', [
            'departmentCounts' => $departmentCounts,
            'stackedData' => $stackedData,
            'roleLevels' => $roleLevels,
        ]);
    }

    public function hiringFunnel(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Funnel Chart: Applications received to candidates hired per job
        $jobs = Job::where('company_id', $companyId)->get();
        $funnelData = [];
        foreach ($jobs as $job) {
            $funnelData[] = [
                'job_title' => $job->title,
                'applied' => $job->applications()->count(),
                'interviewed' => $job->applications()->where('status', 'interviewed')->count(),
                'offered' => $job->applications()->where('status', 'offered')->count(),
                'hired' => $job->applications()->where('status', 'hired')->count(),
            ];
        }

        // Line Chart: Time to progress through each stage
        $stages = ['applied', 'interviewed', 'offered', 'hired'];
        // Get all months where there are stage changes for this company
        $months = JobApplicationStage::whereHas('jobApplication.job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw("DATE_FORMAT(changed_at, '%Y-%m') as month")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        $series = [];

        // For each stage transition, calculate average days taken per month
        for ($i = 1; $i < count($stages); $i++) {
            $from = $stages[$i-1];
            $to = $stages[$i];
            $data = [];

            foreach ($months as $month) {
                // Get all "from" stage records for this month
                $fromStages = JobApplicationStage::where('stage', $from)
                    ->whereRaw("DATE_FORMAT(changed_at, '%Y-%m') = ?", [$month])
                    ->whereHas('jobApplication.job', function($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    })
                    ->get();

                $totalDiff = 0;
                $count = 0;

                foreach ($fromStages as $fromStage) {
                    // Find the next stage for the same application
                    $toStage = JobApplicationStage::where('job_application_id', $fromStage->job_application_id)
                        ->where('stage', $to)
                        ->where('changed_at', '>=', $fromStage->changed_at)
                        ->orderBy('changed_at')
                        ->first();

                    if ($toStage) {
                        $diff = Carbon::parse($fromStage->changed_at)->diffInDays(Carbon::parse($toStage->changed_at));
                        $totalDiff += $diff;
                        $count++;
                    }
                }

                $avg = $count > 0 ? round($totalDiff / $count, 2) : 0;
                $data[] = $avg;
            }

            $series[] = [
                'name' => ucfirst($from) . ' → ' . ucfirst($to),
                'type' => 'line',
                'data' => $data,
            ];
        }

        $stageTrends = [
            'labels' => $months,
            'series' => $series,
        ];

        return Inertia::render('Company/Reports/HiringFunnel', [
            'funnelData' => $funnelData,
            'stageTrends' => $stageTrends,
        ]);
    }

    public function trends(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Line Chart: Job postings over time
        $monthlyPostings = Job::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('company_id', $companyId)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Area Chart: Job posting activity by department
        $areaData = Job::selectRaw("departments.department_name AS department, DATE_FORMAT(jobs.created_at, '%Y-%m') AS month, COUNT(jobs.id) AS total")
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->groupBy('departments.department_name', 'month')
            ->orderBy('month')
            ->get();

        $grouped = $areaData->groupBy('department');
        $months = $areaData->pluck('month')->unique()->sort()->values();

        $areaChartSeries = $grouped->map(function ($entries, $dept) use ($months) {
            $monthMap = $entries->keyBy('month');
            $data = $months->map(fn($m) => $monthMap[$m]->total ?? 0);
            return [
                'name' => $dept,
                'type' => 'line',
                'areaStyle' => [],
                'stack' => 'total',
                'data' => $data,
            ];
        })->values();

        return Inertia::render('Company/Reports/JobPostTrends', [
            'jobPostingTrends' => $monthlyPostings,
            'areaChartLabels' => $months,
            'areaChartSeries' => $areaChartSeries,
        ]);
    }

    public function applicationAnalysis(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        $jobs = Job::where('company_id', $companyId)
        ->with(['applications'])
        ->get();
        
        $jobTitles = $jobs->pluck('job_title')->toArray();
        
        // 1. Stacked Bar Chart: Applications per job posting (by stage)
        $stages = ['applied', 'interviewed', 'offered', 'hired'];
        $stackedBarSeries = [];
        foreach ($stages as $stage) {
            $stackedBarSeries[] = [
                'name' => ucfirst($stage),
                'type' => 'bar',
                'stack' => 'total',
                'data' => $jobs->map(fn($job) => $job->applications->where('stage', $stage)->count())->toArray(),
            ];
        }

        // 2. Scatter Plot: Correlate years of experience with number of applications
        $allSkills = [];
        $scatterData = [];
        foreach ($jobs as $job) {
            $years = $job->years_experience ?? 0;
            $count = $job->applications->count();
            $skills = is_array($job->skills) ? $job->skills : [];
            $allSkills = array_merge($allSkills, $skills);
            $scatterData[] = [
                $years,
                $count,
                $job->job_title,
                $skills, // <-- skills array from JSON column
            ];
        }
        $allSkills = array_values(array_unique($allSkills));

        // 3. Line Chart: Applications received over time (monthly)
        $months = JobApplication::whereHas('job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        $lineData = [];
        foreach ($months as $month) {
            $lineData[] = JobApplication::whereHas('job', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                ->count();
        }

        // 4. Area Chart: Applications per department over time
        $departments = Job::where('company_id', $companyId)
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->select('departments.department_name')
            ->groupBy('departments.department_name')
            ->pluck('departments.department_name')
            ->toArray();

        $areaSeries = [];
        foreach ($departments as $dept) {
            $data = [];
            foreach ($months as $month) {
                $data[] = JobApplication::whereHas('job', function($q) use ($companyId, $dept) {
                        $q->where('company_id', $companyId)
                        ->whereHas('department', function($q2) use ($dept) {
                            $q2->where('name', $dept);
                        });
                    })
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                    ->count();
            }
            $areaSeries[] = [
                'name' => $dept,
                'type' => 'line',
                'areaStyle' => [],
                'stack' => 'total',
                'data' => $data,
            ];
        }

        return Inertia::render('Company/Reports/ApplicationAnalysis', [
            'jobTitles' => $jobTitles,
            'stackedBarSeries' => $stackedBarSeries,
            'scatterData' => $scatterData,
            'months' => $months,
            'lineData' => $lineData,
            'allSkills' => $allSkills,
            'areaSeries' => $areaSeries,
        ]);
    }

    public function skills(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // 1. Word Cloud: Most frequently required skills and certifications in job listings
        $jobs = Job::where('company_id', $companyId)->get();
        $skillCounts = [];
        $certCounts = [];
        foreach ($jobs as $job) {
            $skills = $job->skills;
            if (is_string($skills)) {
                $skills = json_decode($skills, true) ?? [];
            }
            if (!is_array($skills)) {
                $skills = [];
            }
            foreach ($skills as $skill) {
                $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
            }
            $certs = is_array($job->certifications) ? $job->certifications : [];
            foreach ($certs as $cert) {
                $certCounts[$cert] = ($certCounts[$cert] ?? 0) + 1;
            }
        }

        // 2. Bubble Chart: Skills in demand vs. available talent pool
        // Demand: from job listings, Supply: from graduates
        $graduates = \App\Models\Graduate::with('graduateSkills')->get();
        $talentPool = [];
        foreach ($graduates as $grad) {
            foreach ($grad->graduateSkills as $gs) {
                $name = $gs->skill->name ?? null;
                if ($name) {
                    $talentPool[$name] = ($talentPool[$name] ?? 0) + 1;
                }
            }
        }
        // Bubble chart data: [skill, demand, supply]
        $bubbleData = [];
        $allSkills = array_unique(array_merge(array_keys($skillCounts), array_keys($talentPool)));
        foreach ($allSkills as $skill) {
            $bubbleData[] = [
                'name' => $skill,
                'demand' => $skillCounts[$skill] ?? 0,
                'supply' => $talentPool[$skill] ?? 0,
            ];
        }

        // 3. Word Cloud: Most common skills among graduates
        $gradSkillCounts = [];
        foreach ($graduates as $grad) {
            foreach ($grad->graduateSkills as $gs) {
                $name = $gs->skill->name ?? null;
                if ($name) {
                    $gradSkillCounts[$name] = ($gradSkillCounts[$name] ?? 0) + 1;
                }
            }
        }

        // 4. Radar Chart: Compare graduates' skill sets vs. requirements for roles
        // For simplicity, compare top N skills in jobs vs. top N in graduates
        $topSkills = collect($skillCounts)->sortDesc()->take(6)->keys()->toArray();
        $radarIndicators = [];
        $jobSkillValues = [];
        $gradSkillValues = [];
        foreach ($topSkills as $skill) {
            $radarIndicators[] = ['name' => $skill, 'max' => max($skillCounts[$skill] ?? 1, $gradSkillCounts[$skill] ?? 1)];
            $jobSkillValues[] = $skillCounts[$skill] ?? 0;
            $gradSkillValues[] = $gradSkillCounts[$skill] ?? 0;
        }

        return Inertia::render('Company/Reports/SkillQuali', [
            'skillWordCloud' => $skillCounts,
            'certWordCloud' => $certCounts,
            'bubbleData' => $bubbleData,
            'gradSkillWordCloud' => $gradSkillCounts,
            'radarIndicators' => $radarIndicators,
            'jobSkillValues' => $jobSkillValues,
            'gradSkillValues' => $gradSkillValues,
        ]);
    }

    public function employmentType(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Pie Chart: Job openings by employment type
        $types = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];
        $typeCounts = [];
        foreach ($types as $type) {
            $typeCounts[$type] = Job::where('company_id', $companyId)
            ->whereHas('jobTypes', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->count();
        }

        // Column Chart: Job types across departments
        $departments = \App\Models\Department::pluck('department_name');
        $columnData = [];
        foreach ($types as $type) {
            $columnData[$type] = [];
            foreach ($departments as $dept) {
                $columnData[$type][$dept] = Job::where('company_id', $companyId)
                        ->whereHas('jobTypes', function ($query) use ($type) {
                            $query->where('type', $type);
                        })
                        ->whereHas('department', function($q) use ($dept) {
                            $q->where('department_name', $dept);
                        })
                        ->count();
            }
        }

        return Inertia::render('Company/Reports/EmployType', [
            'typeCounts' => $typeCounts,
            'departments' => $departments,
            'columnData' => $columnData,
            'types' => $types,
        ]);
    }
    
    public function salaryInsights(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // 1. Box Plot: Salary ranges for different job roles
        $roles = Job::where('company_id', $companyId)
            ->select('job_title')
            ->distinct()
            ->pluck('job_title')
            ->toArray();

        $boxPlotData = [];
        foreach ($roles as $role) {
            $jobs = Job::where('company_id', $companyId)
                ->where('job_title', $role)
                ->with('salary')
                ->get();

            $salaries = [];
            foreach ($jobs as $job) {
                if ($job->salary) {
                    if ($job->salary->job_min_salary) {
                        $salaries[] = $job->salary->job_min_salary;
                    }
                    if ($job->salary->job_max_salary) {
                        $salaries[] = $job->salary->job_max_salary;
                    }
                }
            }
            $salaries = collect($salaries)->filter()->sort()->values()->toArray();
            $count = count($salaries);
            if ($count > 0) {
                $min = $salaries[0];
                $max = $salaries[$count - 1];
                $q1 = $salaries[(int) floor($count * 0.25)];
                $median = $salaries[(int) floor($count * 0.5)];
                $q3 = $salaries[(int) floor($count * 0.75)];
                $boxPlotData[] = [
                    'role' => $role,
                    'values' => [$min, $q1, $median, $q3, $max],
                ];
            }
        }

        // 2. Histogram: Frequency of salary ranges
        $allJobs = Job::where('company_id', $companyId)->with('salary')->get();
        $allSalaries = $allJobs->map(function($job) {
            if ($job->salary) {
                if ($job->salary->job_min_salary && $job->salary->job_max_salary) {
                    return ($job->salary->job_min_salary + $job->salary->job_max_salary) / 2;
                } elseif ($job->salary->job_min_salary) {
                    return $job->salary->job_min_salary;
                } elseif ($job->salary->job_max_salary) {
                    return $job->salary->job_max_salary;
                }
            }
            return null;
        })->filter()->toArray();

        $binSize = 10000;
        $minSalary = floor(min($allSalaries) / $binSize) * $binSize;
        $maxSalary = ceil(max($allSalaries) / $binSize) * $binSize;
        $bins = [];
        for ($bin = $minSalary; $bin < $maxSalary; $bin += $binSize) {
            $bins[] = [
                'range' => ($bin) . '-' . ($bin + $binSize - 1),
                'count' => 0,
                'jobs' => [],
            ];
        }
        foreach ($allJobs as $job) {
            if ($job->salary) {
                if ($job->salary->job_min_salary && $job->salary->job_max_salary) {
                    $salary = ($job->salary->job_min_salary + $job->salary->job_max_salary) / 2;
                } elseif ($job->salary->job_min_salary) {
                    $salary = $job->salary->job_min_salary;
                } elseif ($job->salary->job_max_salary) {
                    $salary = $job->salary->job_max_salary;
                } else {
                    continue;
                }
                $index = (int) floor(($salary - $minSalary) / $binSize);
                if (isset($bins[$index])) {
                    $bins[$index]['count']++;
                    $bins[$index]['jobs'][] = $job->job_title; // or $job->id or any identifier
                }
            }
        }

        return Inertia::render('Company/Reports/SalaryInsights', [
            'boxPlotData' => $boxPlotData,
            'histogramBins' => $bins,
        ]);
    }

    public function diversity(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Example: Track inclusive language usage in job postings
        // Let's assume you have a boolean column 'uses_inclusive_language' in jobs table
        $inclusiveCounts = [
            'Inclusive Language' => Job::where('company_id', $companyId)->where('uses_inclusive_language', true)->count(),
            'Non-Inclusive Language' => Job::where('company_id', $companyId)->where('uses_inclusive_language', false)->count(),
        ];

        // Example: Diversity initiatives (e.g., tags or boolean columns)
        // Let's assume you have a 'diversity_tags' JSON column in jobs table
        $diversityTags = ['LGBTQ+', 'Women', 'PWD', 'Minorities'];
        $tagCounts = [];
        foreach ($diversityTags as $tag) {
            $tagCounts[$tag] = Job::where('company_id', $companyId)
                ->whereJsonContains('diversity_tags', $tag)
                ->count();
        }

        // Survey Results: Assume you have a CandidateSurvey model with 'question', 'answer', 'job_id'
        // $surveyResults = CandidateSurvey::whereHas('job', function($q) use ($companyId) {
        //         $q->where('company_id', $companyId);
        //     })
        //     ->select('question', 'answer')
        //     ->get()
        //     ->groupBy('question')
        //     ->map(function($answers) {
        //         return $answers->groupBy('answer')->map->count();
        //     });

        return Inertia::render('Company/Reports/DiversityInclusion', [
            'inclusiveCounts' => $inclusiveCounts,
            'tagCounts' => $tagCounts,
            // 'surveyResults' => $surveyResults,
            'diversityTags' => $diversityTags,
        ]);
    }

    public function applicantStatus(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Pie Chart: Proportion of candidates in different statuses
        $statuses = ['applied', 'screened', 'interviewed', 'hired', 'rejected'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $statusCounts[ucfirst($status)] = JobApplication::whereHas('job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('stage', $status)->count();
        }

        // KPI Cards
        $totalApplications = JobApplication::whereHas('job', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();

        $shortlisted = JobApplication::whereHas('job', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('stage', 'screened')->count();

        $rolesFilled = Job::where('company_id', $companyId)
            ->where('status', 'filled')
            ->count();

        return Inertia::render('Company/Reports/ApplicantStatusOverview', [
            'statusCounts' => $statusCounts,
            'totalApplications' => $totalApplications,
            'shortlisted' => $shortlisted,
            'rolesFilled' => $rolesFilled,
        ]);
    }

    public function screening(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Stacked Bar Chart: Candidates screened by qualification, skills, or experience level
        // Example assumes JobApplication has 'qualification', 'skills' (array), 'experience_level', and 'stage' fields

        // 1. By Qualification
        // $qualifications = ['Bachelor', 'Master', 'Doctorate', 'Diploma', 'High School'];
        // $qualificationCounts = [];
        // foreach ($qualifications as $q) {
        //     $qualificationCounts[$q] = \App\Models\JobApplication::whereHas('job', function($q2) use ($companyId) {
        //         $q2->where('company_id', $companyId);
        //     })->where('stage', 'screened')->where('qualification', $q)->count();
        // }

        // 2. By Experience Level
        $experienceLevels = ['Entry', 'Mid', 'Senior'];
        $experienceCounts = [];
        foreach ($experienceLevels as $level) {
            $experienceCounts[$level] = JobApplication::whereHas('job', function($q2) use ($companyId, $level) {
                $q2->where('company_id', $companyId)
                ->where('job_experience_level', $level);
            })->where('stage', 'screened')->count();
        }

        // 3. By Skill (top 5)
        $skills = Job::where('company_id', $companyId)
            ->whereHas('applications', function($q) {
                $q->where('stage', 'screened');
            })
            ->pluck('skills')
            ->map(function($s) {
                return is_string($s) ? json_decode($s, true) : (is_array($s) ? $s : []);
            })
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->toArray();

        // Clustered Column Chart: Screening results across departments or job roles
        $departments = \App\Models\Department::pluck('department_name')->toArray();
        $jobRoles = Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray();

        $departmentScreened = [];
        foreach ($departments as $dept) {
            $departmentScreened[$dept] = JobApplication::whereHas('job', function($q2) use ($companyId, $dept) {
                $q2->where('company_id', $companyId)
                    ->whereHas('department', function($q3) use ($dept) {
                        $q3->where('department_name', $dept);
                    });
            })->where('stage', 'screened')->count();
        }

        $jobRoleScreened = [];
        foreach ($jobRoles as $jobRole) {
            $jobRoleScreened[$jobRole] = JobApplication::whereHas('job', function($q2) use ($companyId, $jobRole) {
                $q2->where('company_id', $companyId)
                    ->where('job_title', $jobRole);
            })->where('stage', 'screened')->count();
        }

        return Inertia::render('Company/Reports/CandidateScreeningInsights', [
            // 'qualificationCounts' => $qualificationCounts,
            'experienceCounts' => $experienceCounts,
            'topSkills' => array_keys($skills),
            'skillCounts' => $skills,
            'departmentScreened' => $departmentScreened,
            'roleScreened' => $jobRoleScreened,
            'departments' => $departments,
        ]);
    }

    public function interviewProgress(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Funnel Chart: Count of applicants at each stage
        $stages = ['screened', 'interview_1', 'interview_2', 'final_selection'];
        $funnelData = [];
        foreach ($stages as $stage) {
            $funnelData[] = [
                'stage' => ucfirst(str_replace('_', ' ', $stage)),
                'count' => JobApplication::whereHas('job', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('stage', $stage)->count(),
            ];
        }

        // Bubble Chart: Candidates interviewed per job role
        $jobRoles = Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray();
        $bubbleData = [];
        foreach ($jobRoles as $jobRole) {
            $interviewedCount = JobApplication::whereHas('job', function($q) use ($companyId, $jobRole) {
                $q->where('company_id', $companyId)
                ->where('job_title', $jobRole);
            })->whereIn('stage', ['interview_1', 'interview_2', 'final_selection'])->count();

            $bubbleData[] = [
                'job_role' => $jobRole,
                'interviewed' => $interviewedCount,
                'size' => $interviewedCount, // Bubble size
            ];
        }

        return Inertia::render('Company/Reports/InterviewProgress', [
            'funnelData' => $funnelData,
            'bubbleData' => $bubbleData,
        ]);
    }

    public function skillsCompetency(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // 1. Radar Chart: Compare candidates’ skill levels against job requirements
        // Get top N required skills from jobs
        $jobs = Job::where('company_id', $companyId)->get();
        $requiredSkills = [];
        foreach ($jobs as $job) {
            $skills = $job->skills;
            if (is_string($skills)) {
                $skills = json_decode($skills, true) ?? [];
            }
            if (!is_array($skills)) $skills = [];
            foreach ($skills as $skill) {
                $requiredSkills[$skill] = ($requiredSkills[$skill] ?? 0) + 1;
            }
        }
        $topSkills = collect($requiredSkills)->sortDesc()->take(6)->keys()->toArray();

        // Get candidate skills (from resumes/applications)
        $applications = JobApplication::whereHas('job', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();
        $candidateSkills = [];
        foreach ($applications as $app) {
            $skills = $app->skills ?? [];
            if (is_string($skills)) {
                $skills = json_decode($skills, true) ?? [];
            }
            if (!is_array($skills)) $skills = [];
            foreach ($skills as $skill) {
                $candidateSkills[$skill] = ($candidateSkills[$skill] ?? 0) + 1;
            }
        }

        // Prepare radar data
        $radarIndicators = [];
        $jobSkillValues = [];
        $candidateSkillValues = [];
        foreach ($topSkills as $skill) {
            $maxVal = max($requiredSkills[$skill] ?? 1, $candidateSkills[$skill] ?? 1);
            $radarIndicators[] = ['name' => $skill, 'max' => $maxVal];
            $jobSkillValues[] = $requiredSkills[$skill] ?? 0;
            $candidateSkillValues[] = $candidateSkills[$skill] ?? 0;
        }

        // 2. Word Cloud: Frequently mentioned skills in resumes/applications
        $wordCloud = $candidateSkills;

        return Inertia::render('Company/Reports/SkillsCompetency', [
            'radarIndicators' => $radarIndicators,
            'jobSkillValues' => $jobSkillValues,
            'candidateSkillValues' => $candidateSkillValues,
            'wordCloud' => $wordCloud,
        ]);
    }

    public function efficiency(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // 1. Line Chart: Average time (in days) to progress through each hiring stage per month
        $stages = ['applied', 'screened', 'interview_1', 'interview_2', 'final_selection', 'hired'];
        $months = JobApplicationStage::whereHas('jobApplication.job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw("DATE_FORMAT(changed_at, '%Y-%m') as month")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        $series = [];
        for ($i = 1; $i < count($stages); $i++) {
            $from = $stages[$i-1];
            $to = $stages[$i];
            $data = [];

            foreach ($months as $month) {
                $fromStages = JobApplicationStage::where('stage', $from)
                    ->whereRaw("DATE_FORMAT(changed_at, '%Y-%m') = ?", [$month])
                    ->whereHas('jobApplication.job', function($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    })
                    ->get();

                $totalDiff = 0;
                $count = 0;

                foreach ($fromStages as $fromStage) {
                    $toStage = JobApplicationStage::where('job_application_id', $fromStage->job_application_id)
                        ->where('stage', $to)
                        ->where('changed_at', '>=', $fromStage->changed_at)
                        ->orderBy('changed_at')
                        ->first();

                    if ($toStage) {
                        $diff = Carbon::parse($fromStage->changed_at)->diffInDays(\Carbon\Carbon::parse($toStage->changed_at));
                        $totalDiff += $diff;
                        $count++;
                    }
                }

                $avg = $count > 0 ? round($totalDiff / $count, 2) : 0;
                $data[] = $avg;
            }

            $series[] = [
                'name' => ucfirst($from) . ' → ' . ucfirst($to),
                'type' => 'line',
                'data' => $data,
            ];
        }

        $lineChart = [
            'labels' => $months,
            'series' => $series,
        ];

        // 2. Scatter Plot: Recruitment efficiency vs. hiring success per job role
        $jobRoles = Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray();
        $scatterData = [];
        foreach ($jobRoles as $jobRole) {
            // Average days from applied to hired for this role
            $applications = JobApplication::whereHas('job', function($q) use ($companyId, $jobRole) {
                $q->where('company_id', $companyId)
                ->where('job_title', $jobRole);
            })->get();

            $totalDays = 0;
            $hiredCount = 0;
            foreach ($applications as $app) {
                $applied = JobApplicationStage::where('job_application_id', $app->id)
                    ->where('stage', 'applied')
                    ->orderBy('changed_at')
                    ->first();
                $hired = JobApplicationStage::where('job_application_id', $app->id)
                    ->where('stage', 'hired')
                    ->orderBy('changed_at')
                    ->first();
                if ($applied && $hired) {
                    $totalDays += Carbon::parse($applied->changed_at)->diffInDays(Carbon::parse($hired->changed_at));
                    $hiredCount++;
                }
            }
            $avgDays = $hiredCount > 0 ? round($totalDays / $hiredCount, 2) : 0;

            // Hiring success rate: hired / total applications for this role
            $totalApplications = $applications->count();
            $successRate = $totalApplications > 0 ? round($hiredCount / $totalApplications * 100, 2) : 0;

            $scatterData[] = [
                'job_role' => $jobRole,
                'avg_days' => $avgDays,
                'success_rate' => $successRate,
            ];
        }

        return Inertia::render('Company/Reports/RecruitmentEfficiency', [
            'lineChart' => $lineChart,
            'scatterData' => $scatterData,
        ]);
    }

    public function performance(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // 1. Pie Chart: Diversity of applicants (gender, ethnicity, etc.)
        // Assumes JobApplication has 'gender' and 'ethnicity' columns
        $genders = ['Male', 'Female'];
        $genderCounts = [];
        foreach ($genders as $gender) {
            $genderCounts[$gender] = JobApplication::whereHas('job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereHas('graduate', function($q) use ($gender) {
                $q->where('gender', $gender);
            })->count();
        }

        // For ethnicity
        $ethnicities = Graduate::select('ethnicity')->distinct()->pluck('ethnicity')->toArray();

        $ethnicityCounts = [];
        foreach ($ethnicities as $ethnicity) {
            $ethnicityCounts[$ethnicity] = JobApplication::whereHas('job', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereHas('graduate', function($q) use ($ethnicity) {
                $q->where('ethnicity', $ethnicity);
            })->count();
        }


        // 2. Stacked Column Chart: Diversity stats across job roles
        $jobRoles = array_values(Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray());
        $roleGenderData = [];
        foreach ($genders as $gender) {
            $roleGenderData[$gender] = [];
            foreach ($jobRoles as $jobRole) {
                $roleGenderData[$gender][$jobRole] = JobApplication::whereHas('job', function($q) use ($companyId, $jobRole) {
                    $q->where('company_id', $companyId)
                    ->where('job_title', $jobRole);
                })->whereHas('graduate', function($q) use ($gender) {
                    $q->where('gender', $gender);
                })->count();
            }
        }

        // You can do the same for departments if needed

        return Inertia::render('Company/Reports/JobPerformance', [
            'genderCounts' => $genderCounts,
            'ethnicityCounts' => $ethnicityCounts,
            'jobRoles' => $jobRoles,
            'roleGenderData' => $roleGenderData,
            'genders' => $genders,
            'ethnicities' => $ethnicities,
        ]);
    }

    public function feedback(Request $request)
    {
       
        return Inertia::render('Company/Reports/FeedbackSatisfaction', [
            
        ]);
    }

    public function graduatePool(Request $request)
    {
        // Example metrics
        $totalGraduates = Graduate::count();
        // $matchedGraduates = Graduate::where('is_matched', true)->count();
        // $avgQualification = Graduate::avg('qualification_score'); // adjust field as needed

        // Pie chart: distribution by field of study
        $fields = Program::pluck('name')->toArray();
        $fieldCounts = [];
        foreach ($fields as $field) {
            $fieldCounts[$field] = Graduate::whereHas('program', function($q) use ($field) {
                $q->where('name', $field);
            })->count();
        }

        return Inertia::render('Company/Reports/GraduatePool', [
            'totalGraduates' => $totalGraduates,
            // 'matchedGraduates' => $matchedGraduates,
            // 'avgQualification' => round($avgQualification, 2),
            'fieldCounts' => $fieldCounts,
        ]);
    }

    public function graduateDemographics(Request $request)
    {
        // 1. Bar Chart: Age, gender, and geographic distribution

        // Age distribution (group by age ranges)
        $ageRanges = [
            '18-24' => [18, 24],
            '25-34' => [25, 34],
            '35-44' => [35, 44],
            '45-54' => [45, 54],
            '55+'   => [55, 200],
        ];
        $ageCounts = [];
        foreach ($ageRanges as $label => [$min, $max]) {
            $ageCounts[$label] = Graduate::join('users', 'graduates.user_id', '=', 'users.id')
                ->whereRaw('TIMESTAMPDIFF(YEAR, graduates.dob, CURDATE()) BETWEEN ? AND ?', [$min, $max])
                ->count();
        }

        // Gender distribution
        $genders = ['Male', 'Female', 'Other'];
        $genderCounts = [];
        foreach ($genders as $gender) {
            $genderCounts[$gender] = Graduate::where('gender', $gender)->count();
        }

        // Geographic distribution (by location/city)
        $locations = Graduate::select('location')->distinct()->pluck('location')->toArray();
        $locationCounts = [];
        foreach ($locations as $loc) {
            $locationCounts[$loc] = Graduate::where('location', $loc)->count();
        }

        // 2. Stacked Column Chart: Demographics by education level
        // Assume education levels are in the Education model, related to Graduate
        $educationLevels = ['Bachelor', 'Master', 'PhD', 'Diploma'];
        $stackedData = [];
        foreach ($educationLevels as $level) {
            $stackedData[$level] = [];
            foreach ($genders as $gender) {
                $stackedData[$level][$gender] = \App\Models\Education::where('education', $level)
                    ->whereHas('graduate', function($q) use ($gender) {
                        $q->where('gender', $gender);
                    })->count();
            }
        }

        return Inertia::render('Company/Reports/GraduateDemographics', [
            'ageCounts' => $ageCounts,
            'genderCounts' => $genderCounts,
            'locationCounts' => $locationCounts,
            'educationLevels' => $educationLevels,
            'genders' => $genders,
            'stackedData' => $stackedData,
        ]);
    }

    public function academicPerformance(Request $request)
    {

        return Inertia::render('Company/Reports/AcademicPerformance', [

        ]);
    }
    
    public function preferences(Request $request)
    {
        // Pie Chart: Graduates’ preferences for job types
        $jobTypes = JobType::pluck('type')->toArray();
        $jobTypeCounts = [];

        foreach ($jobTypes as $type) {
            $jobTypeCounts[$type] = EmploymentPreference::whereHas('jobTypes', function ($q) use ($type) {
                $q->where('type', $type);
            })->count();
        }

        // Bar Chart: Preferred industries (assuming you have an 'industry' or similar field)
        // $industries = EmploymentPreference::select('industry')->distinct()->pluck('industry')->filter()->toArray();
        // $industryCounts = [];
        // foreach ($industries as $industry) {
        //     $industryCounts[$industry] = \App\Models\EmploymentPreference::where('industry', $industry)->count();
        // }
        info('Employment Preferences - jobTypeCounts:', $jobTypeCounts);
        return Inertia::render('Company/Reports/EmployPreference', [
            'jobTypeCounts' => $jobTypeCounts,
            // 'industryCounts' => $industryCounts,
            'jobTypes' => $jobTypes,
            // 'industries' => $industries,
        ]);
    }

    public function certificationTracking(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Filters
        $timeline = $request->input('timeline'); // e.g. '2024-01', '2024', etc.
        $institutionId = $request->input('institution_id');
        $programId = $request->input('program_id');

        // Query certifications of graduates hired by this company
        $certifications = \App\Models\Certification::with(['graduate.user', 'graduate.program', 'graduate.institution'])
            ->whereHas('graduate', function($q) use ($companyId, $institutionId, $programId) {
                $q->whereHas('jobApplications.job', function($q2) use ($companyId) {
                    $q2->where('company_id', $companyId);
                });
                if ($institutionId) {
                    $q->where('institution_id', $institutionId);
                }
                if ($programId) {
                    $q->where('program_id', $programId);
                }
            })
            ->when($timeline, function($q) use ($timeline) {
                // Example: filter by year or month
                $q->where('issue_date', 'like', $timeline . '%');
            })
            ->get();

        // For chart: group by month/year
        $chartData = $certifications->groupBy(function($cert) {
            return Carbon::parse($cert->issue_date)->format('Y-m');
        })->map(function($group) {
            return $group->count();
        });

        // For filters
        $institutions = \App\Models\Institution::all(['id', 'institution_name']);
        $programs = Program::all(['id', 'name']);

        return Inertia::render('Company/Reports/CertTracking', [
            'certifications' => $certifications,
            'chartData' => $chartData,
            'institutions' => $institutions,
            'programs' => $programs,
            'filters' => [
                'timeline' => $timeline,
                'institution_id' => $institutionId,
                'program_id' => $programId,
            ],
        ]);
    }
    
}