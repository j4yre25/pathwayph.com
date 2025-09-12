<?php


namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $allJobs = Job::where('company_id', $companyId)
            ->select(['id', 'job_title', 'job_type', 'status'])
            ->with('jobTypes:id,type')
            ->get();


        // KPI Metrics for jobs posted by this company
        $totalOpenings = $allJobs->count();
        $activeListings = $allJobs->where('status', 'open')->count();
        $rolesFilled = $allJobs->where('status', 'filled')->count();

        // Job types for pie chart

        $typeCounts = [];
        foreach ($jobTypes as $type) {
            $typeCounts[$type->type] =
                // Count jobs where job_type string matches
                $allJobs->where('job_type', $type->type)->count()
                // Plus jobs where jobTypes relation contains this type
                + $allJobs->filter(function ($job) use ($type) {
                    return $job->jobTypes->contains('type', $type->type);
                })->count();
        }

        $jobs = Job::where('company_id', $companyId)
            ->select(['id', 'job_title', 'job_type', 'status'])
            ->with('jobTypes:id,type')
            ->paginate(10);

        return Inertia::render('Company/Reports/JobOverview', [
            'totalOpenings' => $totalOpenings,
            'activeListings' => $activeListings,
            'rolesFilled' => $rolesFilled,
            'typeCounts' => $typeCounts,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'allJobs' => $allJobs,

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
        $roleLevels = ['Entry-level', 'Intermediate', 'Mid-level', 'Senior/Executive'];
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

        // All jobs for filtering and listing (with department and role level)
        $allJobs = Job::select([
            'jobs.id',
            'jobs.job_title',
            'departments.department_name as department',
            'jobs.job_experience_level',
            'jobs.status'
        ])
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->get();

        // Attach allJobs to stackedData for frontend filtering
        $stackedData['allJobs'] = $allJobs;

        return Inertia::render('Company/Reports/DeptWise', [
            'departmentCounts' => $departmentCounts,
            'stackedData' => $stackedData,
            'roleLevels' => $roleLevels,
        ]);
    }

    public function hiringFunnel(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->hr->company_id;

        // Filter params (affect ONLY table, line chart, interpretation)
        $dept     = $request->query('department');
        $jobId    = $request->query('job_id');
        $band     = $request->query('duration_band'); // fast | moderate | slow
        // Duration band thresholds (days)
        $bands = [
            'fast'     => [0, 4],      // <5
            'moderate' => [5, 10],     // 5–10
            'slow'     => [11, null],  // >10
        ];

        // Ordered pipeline stages (company specific or global)
        $companyStageQuery = \App\Models\JobPipelineStage::where('owner_type','company')
            ->where('owner_id',$companyId)
            ->orderBy('position');

        $stages = ($companyStageQuery->exists()
                ? $companyStageQuery
                : \App\Models\JobPipelineStage::whereNull('owner_type')->whereNull('owner_id')->orderBy('position'))
            ->get(['id','slug','name','position']);

        // Exclude terminal/result stages from analytics
        $excludeSlugs = ['hired','rejected'];
        $stages = $stages->reject(fn($s) => in_array(strtolower($s->slug), $excludeSlugs))->values();

        if ($stages->isEmpty()) {
            return Inertia::render('Company/Reports/HiringFunnel', [
                'stageCounts'=>[],
                'stageTable'=>[],
                'lineChart'=>['labels'=>[],'series'=>[]],
                'summaryStats'=>[],
                'stages'=>[],
                'filters'=>[
                    'department'=>$dept,'job_id'=>$jobId,'duration_band'=>$band
                ],
                'departments'=>[],
                'jobs'=>[],
            ]);
        }

        $orderedSlugs = $stages->pluck('slug');

        // Funnel (UNFILTERED) counts: how many applications reached each stage
        $stageCounts = $orderedSlugs->map(function($slug) use ($companyId){
            $count = JobApplication::whereHas('job', fn($q)=>$q->where('company_id',$companyId))
                ->where('stage',$slug)
                ->count();
            return [
                'slug'=>$slug,
                'name'=>\Str::title(str_replace('_',' ',$slug)),
                'count'=>$count
            ];
        })->values();

        // Build filtered application base for table + durations
        $filteredAppIds = JobApplication::whereHas('job', function($q) use ($companyId,$dept,$jobId){
                $q->where('company_id',$companyId)
                ->when($dept, fn($qq,$d)=>$qq->whereHas('department', fn($dqq)=>$dqq->where('department_name',$d)))
                ->when($jobId, fn($qq,$jid)=>$qq->where('id',$jid));
            })
            ->pluck('id');

        if ($filteredAppIds->isEmpty()) {
            return Inertia::render('Company/Reports/HiringFunnel', [
                'stageCounts'=>$stageCounts,
                'stageTable'=>[],
                'lineChart'=>['labels'=>[],'series'=>[]],
                'summaryStats'=>[],
                'stages'=>$stages->map(fn($s)=>['slug'=>$s->slug,'name'=>$s->name]),
                'filters'=>[
                    'department'=>$dept,'job_id'=>$jobId,'duration_band'=>$band
                ],
                'departments'=>\App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
                    ->pluck('department_name')->unique()->values(),
                'jobs'=>Job::where('company_id',$companyId)->select('id','job_title')->orderBy('job_title')->get(),
            ]);
        }

        // Fetch logs for filtered applications
        // NOTE: user stated JobApplicationStageLog has columns: from_stage, to_stage, created_at
        $logs = \App\Models\JobApplicationStageLog::whereIn('job_application_id',$filteredAppIds)
            ->orderBy('job_application_id')
            ->orderBy('created_at')
            ->get(['job_application_id','from_stage','to_stage','created_at']);

        // Group logs per application (sequential transitions)
        $logsByApp = $logs->groupBy('job_application_id');

        // For each application reconstruct time spent in each stage:
        // When an application transitions A -> B at time T, that means: it ENTERED B at T; duration in A ends at T.
        // Need entry time for first stage: we infer from earliest log where to_stage = first stage.
        $stageDurations = []; // stage_slug => [durations in days ...]
        $transitionDurations = []; // "A→B" => [days...]

        foreach ($logsByApp as $appLogs) {
            $appLogs = $appLogs->values();
            if ($appLogs->isEmpty()) continue;

            // Build ordered events: each row denotes leaving from_stage and entering to_stage at created_at
            // Determine entry times: entry[to_stage] = timestamp
            $entryTimes = [];
            foreach ($appLogs as $log) {
                // First occurrence of to_stage sets entry time if not already set
                if (!isset($entryTimes[$log->to_stage])) {
                    $entryTimes[$log->to_stage] = Carbon::parse($log->created_at);
                }
            }

            // Sort stages by pipeline order
            $sortedReached = $orderedSlugs->filter(fn($slug)=>isset($entryTimes[$slug]))->values();

            for ($i=0; $i < $sortedReached->count(); $i++) {
                $stageSlug = $sortedReached[$i];
                $entry = $entryTimes[$stageSlug];
                $exit = null;
                if ($i+1 < $sortedReached->count()) {
                    $nextStage = $sortedReached[$i+1];
                    $exit = $entryTimes[$nextStage]; // left when entering next
                    $duration = max(0, $entry->diffInDays($exit)); // stage duration
                    $stageDurations[$stageSlug][] = $duration;

                    // Transition duration stageSlug -> nextStage (same value)
                    $key = $stageSlug.'→'.$nextStage;
                    $transitionDurations[$key][] = $duration;
                } else {
                    // Last stage reached: no next
                    // Duration undefined; skip unless needed
                }
            }
        }

        // Compute per-stage average days to NEXT stage, classify speed
        $avgToNext = []; // stageSlug => avg days
        foreach ($transitionDurations as $key=>$arr) {
            [$from,$to] = explode('→',$key);
            if (count($arr)) {
                $avgToNext[$from] = round(array_sum($arr)/count($arr),2);
            }
        }

        // Candidate counts per stage within filtered set
        $stageCandidateCounts = [];
        foreach ($orderedSlugs as $slug) {
            $stageCandidateCounts[$slug] = 0;
        }
        // If an application has entry time to a stage -> counted
        foreach ($logsByApp as $appLogs) {
            $reached = $appLogs->pluck('to_stage')->unique();
            foreach ($reached as $slug) {
                if (isset($stageCandidateCounts[$slug])) {
                    $stageCandidateCounts[$slug]++;
                }
            }
        }

        $firstStageSlug = $orderedSlugs->first();
        $firstStageCount = max(1, $stageCandidateCounts[$firstStageSlug] ?? 1);

        // Department & job distributions per stage (filtered)
        $appStageDept = []; // stage => dept_name => count
        $appStageJob  = []; // stage => job_title => count
        // Preload applications with job + dept
        $appsIndexed = JobApplication::with(['job.department'])
            ->whereIn('id',$filteredAppIds)
            ->get()
            ->keyBy('id');

        foreach ($logsByApp as $appId=>$appLogs) {
            $app = $appsIndexed[$appId] ?? null;
            if (!$app) continue;
            $deptName = optional($app->job->department)->department_name ?: 'Unassigned';
            $jobTitle = $app->job->job_title ?? 'Unknown';

            $reachedStages = $appLogs->pluck('to_stage')->unique();
            foreach ($reachedStages as $s) {
                $appStageDept[$s][$deptName] = ($appStageDept[$s][$deptName] ?? 0) + 1;
                $appStageJob[$s][$jobTitle]  = ($appStageJob[$s][$jobTitle]  ?? 0) + 1;
            }
        }

        // Helper classify transition feedback
        $classify = function($days){
            if ($days === null) return 'No Data';
            if ($days < 5) return 'Fast (<5d)';
            if ($days <= 10) return 'Moderate (5–10d)';
            return 'Slow (>10d)';
        };
        // Apply duration_band filter to transitions (table + line chart)
        $bandRange = $bands[$band] ?? null;

        $tableRows = [];
        foreach ($orderedSlugs as $index=>$slug) {
            $count = $stageCandidateCounts[$slug] ?? 0;
            $avgDays = $avgToNext[$slug] ?? null;
            $feedback = $classify($avgDays);

            // Skip if duration band filter active and this row has avgDays outside band (except last stage with null)
            if ($bandRange && $avgDays !== null) {
                [$min,$max] = $bandRange;
                $ok = $avgDays >= $min && ($max === null || $avgDays <= $max);
                if (!$ok) continue;
            }

            $deptCounts = $appStageDept[$slug] ?? [];
            arsort($deptCounts);
            $topDept = array_key_first($deptCounts) ?: '';

            $jobCounts = $appStageJob[$slug] ?? [];
            arsort($jobCounts);
            $topJob = array_key_first($jobCounts) ?: '';

            $conversion = round(($count / $firstStageCount) * 100, 2);

            $tableRows[] = [
                'slug' => $slug,
                'name' => $stages->firstWhere('slug',$slug)->name ?? \Str::title(str_replace('_',' ',$slug)),
                'count' => $count,
                'top_department' => $topDept,
                'top_job' => $topJob,
                'conversion_percent' => $conversion,
                'avg_days_to_next' => $avgDays,
                'transition_feedback' => $feedback,
            ];
        }

        // Line chart (filtered transitions, respect duration band)
        $lineLabels = [];
        $lineData = [];
        foreach ($orderedSlugs as $i=>$slug) {
            if ($i+1 >= $orderedSlugs->count()) break;
            $next = $orderedSlugs[$i+1];
            $key = $slug.'→'.$next;
            if (!isset($transitionDurations[$key]) || !count($transitionDurations[$key])) {
                $lineLabels[] = $slug.'→'.$next;
                $lineData[] = 0;
                continue;
            }
            $avg = round(array_sum($transitionDurations[$key]) / count($transitionDurations[$key]),2);
            // Band filter
            if ($bandRange) {
                [$min,$max] = $bandRange;
                $ok = $avg >= $min && ($max === null || $avg <= $max);
                if (!$ok) continue;
            }
            $lineLabels[] = $slug.'→'.$next;
            $lineData[] = $avg;
        }

        $lineChart = [
            'labels' => $lineLabels,
            'series' => [[
                'name' => 'Avg Days',
                'type' => 'line',
                'smooth' => true,
                'data' => $lineData
            ]]
        ];

        // Summary
        $bottleneck = collect($tableRows)->filter(fn($r)=>$r['avg_days_to_next'] !== null)
            ->sortByDesc('avg_days_to_next')->first();
        $summaryStats = [
            'total_candidates_first_stage' => $stageCandidateCounts[$firstStageSlug] ?? 0,
            'bottleneck_stage' => $bottleneck['name'] ?? null,
            'bottleneck_avg_days' => $bottleneck['avg_days_to_next'] ?? null,
            'filtered_rows' => count($tableRows),
            'duration_band' => $band,
        ];

        // Filter dropdown sources
        $departments = \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')->unique()->values();
        $jobs = Job::where('company_id',$companyId)
            ->select('id','job_title')
            ->orderBy('job_title')
            ->get();

        return Inertia::render('Company/Reports/HiringFunnel', [
            'stageCounts' => $stageCounts,          
            'stageTable' => $tableRows,
            'lineChart' => $lineChart,
            'summaryStats' => $summaryStats,
            'stages' => $stages->map(fn($s)=>['slug'=>$s->slug,'name'=>$s->name]),
            'filters' => [
                'department'=>$dept,
                'job_id'=>$jobId,
                'duration_band'=>$band,
            ],
            'departments' => $departments,
            'jobs' => $jobs,
        ]);
    }

    public function trends(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;


        $selectedDepartment = $request->input('department', '');
        $status = $request->input('status', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $perPage = 10;

        // Get all departments for filter dropdown
        $departments = \App\Models\Department::whereHas('jobs', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->pluck('department_name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Filter jobs by department if selected
        $jobsQuery = Job::with('department')
            ->where('company_id', $companyId)
            ->when($selectedDepartment, function ($q) use ($selectedDepartment) {
                $q->whereHas('department', function ($q2) use ($selectedDepartment) {
                    $q2->where('department_name', $selectedDepartment);
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy('created_at', 'desc');

        $jobsList = $jobsQuery->paginate($perPage)->through(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->job_title,
                'created_at' => $job->created_at->format('Y-m-d'),
                'department' => $job->department ? $job->department->department_name : '',
                'status' => $job->status,
            ];
        });
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
            'jobsList' => $jobsList,
            'filters' => [
                'department' => $selectedDepartment,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statuses' => ['open', 'closed', 'draft'],
            'jobPostingTrends' => $monthlyPostings,
            'areaChartLabels' => $months,
            'areaChartSeries' => $areaChartSeries,
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
        ]);
    }

    public function applicationAnalysis(Request $request)
    {
        $user = auth()->user();
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // Get all years present in applications for this company
        $years = JobApplication::whereHas('job', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw("YEAR(created_at) as year")
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        // For dropdown and filtering
        $applicationYears = $years;
        $selectedYear = $request->input('application_year', end($applicationYears)); // default to latest

        $jobs = Job::where('company_id', $companyId)
            ->with(['applications'])
            ->get();

        $jobTitles = $jobs->pluck('job_title')->toArray();

        $jobDepartments = [];
        foreach ($jobs as $job) {
            $jobDepartments[$job->job_title] = $job->department->department_name ?? '';
        }

        // 1. Stacked Bar Chart: Applications per job posting (by stage, per year)
        $stages = ['applied', 'interviewed', 'offered', 'hired'];
        $stackedBarSeries = [];
        foreach ($stages as $stage) {
            $data = [];
            foreach ($applicationYears as $year) {
                $count = JobApplication::whereHas('job', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    })
                    ->where('stage', $stage)
                    ->whereYear('created_at', $year)
                    ->count();
                $data[] = $count;
            }
            $stackedBarSeries[] = [
                'name' => ucfirst($stage),
                'type' => 'bar',
                'stack' => 'total',
                'data' => $data,
            ];
        }

        // 2. Scatter Plot: Correlate years of experience with number of applications (add year info)
        $allSkills = [];
        $scatterData = [];
        foreach ($jobs as $job) {
            foreach ($applicationYears as $year) {
                $apps = $job->applications->filter(function ($app) use ($year) {
                    return $app->created_at->format('Y') == $year;
                });
                $years = $job->years_experience ?? 0;
                $count = $apps->count();
                $skills = is_array($job->skills) ? $job->skills : [];
                $allSkills = array_merge($allSkills, $skills);
                $scatterData[] = [
                    $years,
                    $count,
                    $job->job_title,
                    $skills,
                    $year, // Application year for filtering
                ];
            }
        }
        $allSkills = array_values(array_unique($allSkills));

        // 3. Line Chart: Applications received per year
        $lineData = [];
        foreach ($applicationYears as $year) {
            $lineData[] = JobApplication::whereHas('job', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->whereYear('created_at', $year)
                ->count();
        }

        // 4. Area Chart: Applications per department per year
        $departments = Job::where('jobs.company_id', $companyId)
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->select('departments.department_name')
            ->groupBy('departments.department_name')
            ->pluck('departments.department_name')
            ->toArray();

        $areaSeries = [];
        foreach ($departments as $dept) {
            $data = [];
            foreach ($applicationYears as $year) {
                $data[] = JobApplication::whereHas('job', function ($q) use ($companyId, $dept) {
                        $q->where('company_id', $companyId)
                            ->whereHas('department', function ($q2) use ($dept) {
                                $q2->where('department_name', $dept);
                            });
                    })
                    ->whereYear('created_at', $year)
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

        // --- SUMMARY TABLE LOGIC ---
        $summaryTable = [];
        foreach ($jobs as $job) {
            foreach ($applicationYears as $year) {
                $apps = $job->applications->filter(function ($app) use ($year) {
                    return $app->created_at->format('Y') == $year;
                });
                $summaryTable[] = [
                    'jobTitle' => $job->job_title,
                    'department' => $job->department->department_name ?? '',
                    'totalApplications' => $apps->count(),
                    'hired' => $apps->where('stage', 'hired')->count(),
                    'applicationYear' => $year,
                ];
            }
        }

        return Inertia::render('Company/Reports/ApplicationAnalysis', [
            'jobTitles' => $jobTitles,
            'jobDepartments' => $jobDepartments,
            'stackedBarSeries' => $stackedBarSeries,
            'scatterData' => $scatterData,
            'years' => $applicationYears,
            'lineData' => $lineData,
            'allSkills' => $allSkills,
            'areaSeries' => $areaSeries,
            'summaryTable' => $summaryTable,
            'departments' => $departments,
            'selectedYear' => $selectedYear,
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
        $graduates = Graduate::with('graduateSkills')->get();
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
                    ->whereHas('department', function ($q) use ($dept) {
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

        // Distinct roles
        $roles = Job::where('company_id', $companyId)
            ->select('job_title')
            ->distinct()
            ->pluck('job_title')
            ->toArray();

        $boxPlotData = [];

        foreach ($roles as $role) {
            $jobsForRole = Job::where('company_id', $companyId)
                ->where('job_title', $role)
                ->with('salary')
                ->get();

            $salaries = [];

            foreach ($jobsForRole as $job) {
                // Prefer related salary record
                if ($job->salary) {
                    if (!is_null($job->salary->job_min_salary)) $salaries[] = (float)$job->salary->job_min_salary;
                    if (!is_null($job->salary->job_max_salary)) $salaries[] = (float)$job->salary->job_max_salary;
                } else {
                    // Fallback: direct columns on jobs table
                    if (!is_null($job->job_min_salary)) $salaries[] = (float)$job->job_min_salary;
                    if (!is_null($job->job_max_salary)) $salaries[] = (float)$job->job_max_salary;
                }
            }

            $salaries = collect($salaries)->filter(fn($v) => $v > 0)->sort()->values();
            $n = $salaries->count();
            if ($n === 0) continue;

            // Helper for percentile index
            $p = function($percent) use ($n) {
                if ($n === 1) return 0;
                $idx = ($n - 1) * $percent;
                return (int)round($idx);
            };

            $min   = $salaries->first();
            $max   = $salaries->last();
            $q1    = $salaries->get($p(0.25));
            $median= $salaries->get($p(0.50));
            $q3    = $salaries->get($p(0.75));

            $boxPlotData[] = [
                'role' => $role,
                'values' => [$min, $q1, $median, $q3, $max],
            ];
        }

        // Histogram (use midpoint of range; fallback to job columns too)
        $allJobs = Job::where('company_id', $companyId)->with('salary')->get();

        $allSalaries = [];
        foreach ($allJobs as $job) {
            $minS = null;
            $maxS = null;

            if ($job->salary) {
                $minS = $job->salary->job_min_salary;
                $maxS = $job->salary->job_max_salary;
            } else {
                $minS = $job->job_min_salary;
                $maxS = $job->job_max_salary;
            }

            // Normalize numeric
            $minS = is_numeric($minS) ? (float)$minS : null;
            $maxS = is_numeric($maxS) ? (float)$maxS : null;

            if (!is_null($minS) && !is_null($maxS)) {
                $allSalaries[] = ($minS + $maxS) / 2;
            } elseif (!is_null($minS)) {
                $allSalaries[] = $minS;
            } elseif (!is_null($maxS)) {
                $allSalaries[] = $maxS;
            }
        }

        $allSalaries = array_values(array_filter($allSalaries, fn($v) => $v > 0));

        if (count($allSalaries) === 0) {
            \Log::info('SalaryInsights: no salary data (including fallback) for company', ['company_id' => $companyId]);
            return Inertia::render('Company/Reports/SalaryInsights', [
                'boxPlotData' => $boxPlotData,
                'histogramBins' => [],
            ]);
        }

        $binSize = 10000;
        $minSalary = floor(min($allSalaries) / $binSize) * $binSize;
        $maxSalary = ceil(max($allSalaries) / $binSize) * $binSize;
        if ($minSalary === $maxSalary) {
            $maxSalary = $minSalary + $binSize;
        }

        $bins = [];
        for ($b = $minSalary; $b < $maxSalary; $b += $binSize) {
            $bins[] = [
                'range' => $b . '-' . ($b + $binSize - 1),
                'count' => 0,
                'jobs' => [],
            ];
        }

        // Populate bins (use same salary midpoint logic)
        foreach ($allJobs as $job) {
            $minS = $job->salary->job_min_salary ?? $job->job_min_salary;
            $maxS = $job->salary->job_max_salary ?? $job->job_max_salary;

            $minS = is_numeric($minS) ? (float)$minS : null;
            $maxS = is_numeric($maxS) ? (float)$maxS : null;

            if (is_null($minS) && is_null($maxS)) continue;

            if (!is_null($minS) && !is_null($maxS)) {
                $mid = ($minS + $maxS) / 2;
            } else {
                $mid = !is_null($minS) ? $minS : $maxS;
            }

            if ($mid <= 0) continue;

            $index = (int) floor(($mid - $minSalary) / $binSize);
            if (!isset($bins[$index])) continue;
            $bins[$index]['count']++;
            $bins[$index]['jobs'][] = $job->job_title;
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
        $companyId = $user->hr->company_id;

        // Filters
        $jobId        = $request->query('job_id');
        $department   = $request->query('department');
        $statusFilter  = $request->query('status'); 
        $datePreset   = $request->query('date_preset', 'overall'); 
        $dateFrom     = $request->query('date_from');
        $dateTo       = $request->query('date_to');

        // Resolve preset to dates (if not custom)
        if ($datePreset !== 'custom') {
            $now = Carbon::now();
            switch ($datePreset) {
                case 'last_7':      $dateFrom = $now->copy()->subDays(7)->startOfDay();  $dateTo = $now; break;
                case 'last_30':     $dateFrom = $now->copy()->subDays(30)->startOfDay(); $dateTo = $now; break;
                case 'last_90':     $dateFrom = $now->copy()->subDays(90)->startOfDay(); $dateTo = $now; break;
                case 'this_month':  $dateFrom = $now->copy()->startOfMonth(); $dateTo = $now->copy()->endOfMonth(); break;
                case 'last_month':
                    $dateFrom = $now->copy()->subMonth()->startOfMonth();
                    $dateTo   = $now->copy()->subMonth()->endOfMonth();
                    break;
                case 'this_year':   $dateFrom = $now->copy()->startOfYear(); $dateTo = $now->copy()->endOfYear(); break;
                case 'last_year':
                    $dateFrom = $now->copy()->subYear()->startOfYear();
                    $dateTo   = $now->copy()->subYear()->endOfYear();
                    break;
                case 'last_2_years':
                    $dateFrom = $now->copy()->subYears(2)->startOfDay();
                    $dateTo   = $now;
                    break;
                case 'overall':
                default:
                    $dateFrom = null;
                    $dateTo   = null;
            }
        } else {
            // Custom: ensure Carbon objects or null
            $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $dateTo   = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
        }

         $statusMap = [
        'screening' => 'screening',
        'offered'   => 'offer',     
        'rejected'  => 'rejected',
        'hired'     => 'hired',
    ];

    $baseQuery = JobApplication::with(['job.department','graduate'])
        ->whereHas('job', function($q) use ($companyId, $department, $jobId) {
            $q->where('company_id', $companyId)
              ->when($department, fn($qq,$d)=>$qq->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
              ->when($jobId, fn($qq,$jid)=>$qq->where('id',$jid));
        })
        ->when($statusFilter && isset($statusMap[$statusFilter]),
            fn($q)=>$q->where('stage', $statusMap[$statusFilter]))
        ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
        ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo));

        $applications = $baseQuery->get();

        // Track which rejected applicants had previously reached the Offer stage
        $offerStage = \App\Models\JobPipelineStage::whereIn('slug', ['offer','offered'])->first();

        $appsReachedOffer = collect();
        if ($offerStage && $applications->count()) {
            $logsQuery = \App\Models\JobApplicationStageLog::whereIn(
                'job_application_id',
                $applications->pluck('id')
            );

            if (Schema::hasColumn('job_application_stage_logs', 'to_stage_id')) {
                // Numeric FK column exists
                $logsQuery->where('to_stage_id', $offerStage->id);
            } elseif (Schema::hasColumn('job_application_stage_logs', 'to_stage')) {
                // Fallback string / slug column
                $logsQuery->where('to_stage', $offerStage->slug);
            } else {
                // Neither column present; skip
                $logsQuery = null;
            }

            if ($logsQuery) {
                $appsReachedOffer = $logsQuery->pluck('job_application_id')->unique();
            }
        }


        // Status (stage) counts for filtered dataset
        $relevantStages = ['applied','screening','offer','hired','rejected'];
        $statusCounts = [];
        foreach ($relevantStages as $st) {
            $statusCounts[ucfirst($st)] = $applications->where('stage',$st)->count();
        }

        $totalApplications = $applications->count();

        // KPI: Shortlisted = in offer stage
        $shortlisted = $applications->where('stage','offer')->count();

        // KPI: Roles Filled = jobs where hired count >= vacancies (and vacancies > 0), respecting job/department filters
        $jobsForRoleFill = Job::where('company_id',$companyId)
            ->when($department, fn($q,$d)=>$q->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
            ->when($jobId, fn($q,$jid)=>$q->where('id',$jid))
            ->get(['id','job_vacancies']);
        $jobIds = $jobsForRoleFill->pluck('id');
        $hiredCountsPerJob = JobApplication::whereIn('job_id',$jobIds)
            ->where('stage','hired')
            ->select('job_id', DB::raw('COUNT(*) as hired'))
            ->groupBy('job_id')
            ->pluck('hired','job_id');
        $rolesFilled = 0;
        foreach ($jobsForRoleFill as $job) {
            $vac = (int)($job->job_vacancies ?? 0);
            if ($vac > 0 && ($hiredCountsPerJob[$job->id] ?? 0) >= $vac) {
                $rolesFilled++;
            }
        }

        // Table rows
         $tableRows = $applications->map(function($app) use ($appsReachedOffer) {
            $grad = $app->graduate;
            $name = trim(($grad->first_name ?? '').' '.($grad->last_name ?? '')) ?: 'Applicant #'.$app->id;
            $dept = $app->job?->department?->department_name;
            $reachedOffer = $appsReachedOffer->contains($app->id);
            return [
                'id' => $app->id,
                'applicant_name' => $name,
                'job_title' => $app->job?->job_title,
                'department' => $dept,
                'stage' => $app->stage,
                'stage_label' => ucfirst(str_replace('_',' ',$app->stage)),
                'applied_date' => optional($app->created_at)->format('Y-m-d'),
                'reached_offer' => $reachedOffer,
            ];
        })->values();

        // Top job & department (filtered)
        $topJob = $tableRows->groupBy('job_title')->map->count()->sortDesc()->keys()->first();
        $topDept = $tableRows->groupBy('department')->map->count()->sortDesc()->keys()->first();

        $hired = $statusCounts['Hired'] ?? 0;
        $rejected = $statusCounts['Rejected'] ?? 0;
        $offer = $statusCounts['Offer'] ?? 0;

        
        $rejectedAfterOffer = $tableRows->where('stage','rejected')->where('reached_offer', true)->count();
        $rejectedBeforeOffer = $tableRows->where('stage','rejected')->where('reached_offer', false)->count();
        $postOfferRejectionRate = $offer > 0 ? round($rejectedAfterOffer / $offer * 100, 2) : 0;


        $offerToHireRate = $offer > 0 ? round($hired / $offer * 100, 2) : 0;
        $rejectionRate   = $totalApplications > 0 ? round($rejected / $totalApplications * 100, 2) : 0;

        // Summary stats for interpretation
        $summary = [
            'total' => $totalApplications,
            'shortlisted' => $shortlisted,
            'hired' => $hired,
            'rejected' => $rejected,
            'offer' => $offer,
            'offer_to_hire_rate' => $offerToHireRate,
            'rejection_rate' => $rejectionRate,
            'roles_filled' => $rolesFilled,
            'roles_filled_pct' => $jobsForRoleFill->count() ? round($rolesFilled / $jobsForRoleFill->count() * 100, 2) : 0,
            'top_job' => $topJob,
            'top_department' => $topDept,
            'filters_active' => (bool)($jobId || $department || $statusFilter || ($datePreset !== 'overall' && $datePreset) || ($datePreset === 'custom' && ($dateFrom || $dateTo))),
            'date_range_label' => $datePreset === 'custom'
                ? (($dateFrom?->format('Y-m-d') ?? '—').' to '.($dateTo?->format('Y-m-d') ?? '—'))
                : ucfirst(str_replace('_',' ', $datePreset)),
            'rejected_after_offer' => $rejectedAfterOffer,
            'rejected_before_offer' => $rejectedBeforeOffer,
            'post_offer_rejection_rate' => $postOfferRejectionRate,
        ];

        // Filter dropdown sources
        $departments = \App\Models\Department::whereHas('jobs', fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')->unique()->values();
        $jobs = Job::where('company_id',$companyId)
            ->select('id','job_title')
            ->orderBy('job_title')
            ->get();
        $distinctStages = JobApplication::whereHas('job', fn($q)=>$q->where('company_id',$companyId))
            ->distinct()->pluck('stage')->filter()->values();

        return Inertia::render('Company/Reports/ApplicantStatusOverview', [
            'statusCounts' => $statusCounts,
            'totalApplications' => $totalApplications,
            'shortlisted' => $shortlisted,
            'rolesFilled' => $rolesFilled,
            'tableRows' => $tableRows,
            'summary' => $summary,
            'filters' => [
                'job_id'=>$jobId,
                'department'=>$department,
                'status'=>$statusFilter,
                'date_preset'=>$datePreset,
                'date_from'=>$request->query('date_from'),
                'date_to'=>$request->query('date_to'),
            ],
            'jobs' => $jobs,
            'departments' => $departments,
            'stages' => $distinctStages,
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
            $experienceCounts[$level] = JobApplication::whereHas('job', function ($q2) use ($companyId, $level) {
                $q2->where('company_id', $companyId)
                    ->where('job_experience_level', $level);
            })->where('stage', 'screened')->count();
        }

        // 3. By Skill (top 5)
        $skills = Job::where('company_id', $companyId)
            ->whereHas('applications', function ($q) {
                $q->where('stage', 'screened');
            })
            ->pluck('skills')
            ->map(function ($s) {
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
            $departmentScreened[$dept] = JobApplication::whereHas('job', function ($q2) use ($companyId, $dept) {
                $q2->where('company_id', $companyId)
                    ->whereHas('department', function ($q3) use ($dept) {
                        $q3->where('department_name', $dept);
                    });
            })->where('stage', 'screened')->count();
        }

        $jobRoleScreened = [];
        foreach ($jobRoles as $jobRole) {
            $jobRoleScreened[$jobRole] = JobApplication::whereHas('job', function ($q2) use ($companyId, $jobRole) {
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
                'count' => JobApplication::whereHas('job', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('stage', $stage)->count(),
            ];
        }

        // Bubble Chart: Candidates interviewed per job role
        $jobRoles = Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray();
        $bubbleData = [];
        foreach ($jobRoles as $jobRole) {
            $interviewedCount = JobApplication::whereHas('job', function ($q) use ($companyId, $jobRole) {
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
        $applications = JobApplication::whereHas('job', function ($q) use ($companyId) {
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
        $months = JobApplicationStage::whereHas('jobApplication.job', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->selectRaw("DATE_FORMAT(changed_at, '%Y-%m') as month")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        $series = [];
        for ($i = 1; $i < count($stages); $i++) {
            $from = $stages[$i - 1];
            $to = $stages[$i];
            $data = [];

            foreach ($months as $month) {
                $fromStages = JobApplicationStage::where('stage', $from)
                    ->whereRaw("DATE_FORMAT(changed_at, '%Y-%m') = ?", [$month])
                    ->whereHas('jobApplication.job', function ($q) use ($companyId) {
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

        $lineChart = [
            'labels' => $months,
            'series' => $series,
        ];

        // 2. Scatter Plot: Recruitment efficiency vs. hiring success per job role
        $jobRoles = Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray();
        $scatterData = [];
        foreach ($jobRoles as $jobRole) {
            // Average days from applied to hired for this role
            $applications = JobApplication::whereHas('job', function ($q) use ($companyId, $jobRole) {
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
            $genderCounts[$gender] = JobApplication::whereHas('job', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereHas('graduate', function ($q) use ($gender) {
                $q->where('gender', $gender);
            })->count();
        }

        // For ethnicity
        $ethnicities = Graduate::select('ethnicity')->distinct()->pluck('ethnicity')->toArray();

        $ethnicityCounts = [];
        foreach ($ethnicities as $ethnicity) {
            $ethnicityCounts[$ethnicity] = JobApplication::whereHas('job', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereHas('graduate', function ($q) use ($ethnicity) {
                $q->where('ethnicity', $ethnicity);
            })->count();
        }


        // 2. Stacked Column Chart: Diversity stats across job roles
        $jobRoles = array_values(Job::where('company_id', $companyId)->pluck('job_title')->unique()->toArray());
        $roleGenderData = [];
        foreach ($genders as $gender) {
            $roleGenderData[$gender] = [];
            foreach ($jobRoles as $jobRole) {
                $roleGenderData[$gender][$jobRole] = JobApplication::whereHas('job', function ($q) use ($companyId, $jobRole) {
                    $q->where('company_id', $companyId)
                        ->where('job_title', $jobRole);
                })->whereHas('graduate', function ($q) use ($gender) {
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

        return Inertia::render('Company/Reports/FeedbackSatisfaction', []);
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
            $fieldCounts[$field] = Graduate::whereHas('program', function ($q) use ($field) {
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
                    ->whereHas('graduate', function ($q) use ($gender) {
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

        return Inertia::render('Company/Reports/AcademicPerformance', []);
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
        $perPage = 10;

        // Query certifications of graduates hired by this company
        $certificationsQuery = \App\Models\Certification::with(['graduate.user', 'graduate.program', 'graduate.institution'])
            ->whereHas('graduate', function ($q) use ($institutionId, $programId) {
                if ($institutionId) {
                    $q->where('institution_id', $institutionId);
                }
                if ($programId) {
                    $q->where('program_id', $programId);
                }
            })
            ->when($timeline, function ($q) use ($timeline) {
                $q->where('issue_date', 'like', $timeline . '%');
            })
            ->orderByDesc('issue_date');

        $certifications = $certificationsQuery->get();

        // For the chart (unfiltered, always all data)
        $allCertsForChart = \App\Models\Certification::with(['graduate.user', 'graduate.program', 'graduate.institution'])
            ->orderByDesc('issue_date')
            ->get();
        // Chart data (all data)
        $chartData = $allCertsForChart->groupBy(function ($cert) {
            return Carbon::parse($cert->issue_date)->format('Y-m');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys();;

        // For filters
        $institutions = \App\Models\Institution::all(columns: ['id', 'institution_name']);
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

    public function schoolEmployability(Request $request)
    {

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


        return Inertia::render('Company/Reports/SchoolEmployability', [
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
}
