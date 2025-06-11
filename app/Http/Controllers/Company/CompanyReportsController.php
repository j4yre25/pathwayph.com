<?php


namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
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

        // KPI Metrics for jobs posted by this company
        $totalOpenings = Job::where('company_id', $companyId)
                        ->where('status', 'open')
                        ->count();

        $activeListings = Job::where('company_id', $companyId)
                            ->where('is_approved', 1)
                            ->where('status', 'open')
                            ->count();

        $rolesFilled = Job::where('company_id', $companyId)
                        ->withCount('applications')
                        ->where('status', 'filled')
                        ->count();

        // Job types for pie chart
        $types = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];
        $typeCounts = [];

        foreach ($types as $type) {
            $typeCounts[$type] = Job::where('company_id', $companyId)
                ->whereHas('jobTypes', function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->count();
        }


        return Inertia::render('Company/Reports/JobOverview', [
            'totalOpenings' => $totalOpenings,
            'activeListings' => $activeListings,
            'rolesFilled' => $rolesFilled,
            'typeCounts' => $typeCounts,
            
        ]);
    }

    public function department(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Bar Chart: Number of job openings per department
        $departmentCounts = Job::selectRaw('departments.name as department, COUNT(jobs.id) as total')
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->where('jobs.status', 'open')
            ->groupBy('departments.name')
            ->get();

        // Stacked Column Chart: Job postings by role level per department
        $roleLevels = ['Entry', 'Mid', 'Senior'];
        $stackedData = [];
        foreach ($roleLevels as $level) {
            $stackedData[$level] = Job::selectRaw('departments.name as department, COUNT(jobs.id) as total')
                ->join('departments', 'jobs.department_id', '=', 'departments.id')
                ->where('jobs.company_id', $companyId)
                ->where('jobs.status', 'open')
                ->where('jobs.role_level', $level)
                ->groupBy('departments.name')
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
        $areaData = Job::selectRaw("departments.name AS department, DATE_FORMAT(jobs.created_at, '%Y-%m') AS month, COUNT(jobs.id) AS total")
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->groupBy('departments.name', 'month')
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
        // $departments = Job::where('company_id', $companyId)
        //     ->join('departments', 'jobs.department_id', '=', 'departments.id')
        //     ->select('departments.name')
        //     ->groupBy('departments.name')
        //     ->pluck('departments.name')
        //     ->toArray();

        // $areaSeries = [];
        // foreach ($departments as $dept) {
        //     $data = [];
        //     foreach ($months as $month) {
        //         $data[] = JobApplication::whereHas('job', function($q) use ($companyId, $dept) {
        //                 $q->where('company_id', $companyId)
        //                 ->whereHas('department', function($q2) use ($dept) {
        //                     $q2->where('name', $dept);
        //                 });
        //             })
        //             ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
        //             ->count();
        //     }
        //     $areaSeries[] = [
        //         'name' => $dept,
        //         'type' => 'line',
        //         'areaStyle' => [],
        //         'stack' => 'total',
        //         'data' => $data,
        //     ];
        // }

        return Inertia::render('Company/Reports/ApplicationAnalysis', [
            'jobTitles' => $jobTitles,
            'stackedBarSeries' => $stackedBarSeries,
            'scatterData' => $scatterData,
            'months' => $months,
            'lineData' => $lineData,
            'allSkills' => $allSkills,
            // 'areaSeries' => $areaSeries,
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
        // $departments = \App\Models\Department::pluck('name');
        // $columnData = [];
        // foreach ($types as $type) {
        //     $columnData[$type] = [];
        //     foreach ($departments as $dept) {
        //         $columnData[$type][$dept] = Job::where('company_id', $companyId)
        //             ->where('employment_type', $type)
        //             ->whereHas('department', function($q) use ($dept) {
        //                 $q->where('name', $dept);
        //             })
        //             ->count();
        //     }
        // }

        return Inertia::render('Company/Reports/EmployType', [
            'typeCounts' => $typeCounts,
            // 'departments' => $departments,
            // 'columnData' => $columnData,
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

        // Define bins (e.g., 10k increments)
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
            'roles' => $roles,
            'histogramBins' => $bins,
        ]);
    }
}