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
use App\Models\JobApplicationStageLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CompanyReportsController extends Controller
{

    public function list($userId)
    {
        // You can pass any data needed to the Vue page
        return Inertia::render('Company/Reports/Home');
    }

     public function listReports($userId)
    {
        // You can pass any data needed to the Vue page
        return Inertia::render('Company/Reports/ListOfReports');
    }

    private function uniqueJobOptions(int $companyId, ?string $department = null, ?int $programId = null)
    {
        return Job::where('company_id', $companyId)
            ->when($department, fn($q,$d)=>$q->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
            ->when($programId, fn($q,$pid)=>$q->whereHas('programs', fn($pq)=>$pq->where('programs.id',$pid)))
            ->select(DB::raw('MIN(jobs.id) as id'), 'job_title')
            ->groupBy('job_title')
            ->orderBy('job_title')
            ->get();
    }

    // Return unique program options (one per name; picks the smallest id)
    private function uniqueProgramOptionsForCompany(int $companyId, ?string $department = null, ?int $jobId = null)
    {
        return Program::whereHas('jobs', function ($q) use ($companyId,$department,$jobId) {
                $q->where('company_id', $companyId)
                  ->when($department, fn($qq,$d)=>$qq->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
                  ->when($jobId, fn($qq,$jid)=>$qq->where('jobs.id', $jid));
            })
            ->select(DB::raw('MIN(programs.id) as id'), 'name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();
    }

    public function jobs(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->hr->company_id;

        // --- Filters ---
        $datePreset      = $request->query('date_preset','last_30');
        $dateFromInput   = $request->query('date_from');
        $dateToInput     = $request->query('date_to');
        $experienceLevel = $request->query('experience_level');
        $workEnvironment = $request->query('work_environment');
        $departmentsSel  = $request->query('departments', []);

        $now = Carbon::now();
        $dateFrom = null; $dateTo = null;
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_7':     $dateFrom = $now->copy()->subDays(7)->startOfDay();  $dateTo = $now; break;
                case 'last_30':    $dateFrom = $now->copy()->subDays(30)->startOfDay(); $dateTo = $now; break;
                case 'this_month': $dateFrom = $now->copy()->startOfMonth(); $dateTo = $now->copy()->endOfMonth(); break;
                case 'last_month': $dateFrom = $now->copy()->subMonth()->startOfMonth(); $dateTo = $now->copy()->subMonth()->endOfMonth(); break;
                case 'overall': default: $dateFrom = null; $dateTo = null;
            }
        } else {
            $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : null;
            $dateTo   = $dateToInput ? Carbon::parse($dateToInput)->endOfDay()   : null;
        }

        $select = [
            'id',
            'job_title',
            'job_type',
            'status',
            'job_experience_level',
            'job_vacancies',
            'created_at',
            'department_id',      
            'job_min_salary',     
            'job_max_salary',     
        ];
        if (Schema::hasColumn('jobs','work_environment')) {
            $select[] = 'work_environment';
        }

        // --- Base Query with Filters ---
        $jobsQuery = Job::where('company_id',$companyId)
            ->select($select)
            ->with(['jobTypes:id,type','workEnvironments:id,environment_type','department:id,department_name', 'salary'])
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
            ->when($experienceLevel, fn($q,$lvl)=>$q->where('job_experience_level',$lvl))
            ->when($workEnvironment, function($q) use ($workEnvironment) {
                $vals = is_array($workEnvironment) ? array_filter($workEnvironment) : [$workEnvironment];
                if (!count($vals)) return;
                if (Schema::hasColumn('jobs','work_environment')) {
                    $q->where(function($qq) use ($vals) {
                        $qq->whereIn('work_environment', $vals);
                    });
                }
                $q->orWhereHas('workEnvironments', function($wq) use ($vals) {
                    $wq->whereIn('environment_type', $vals);
                });
            })
            ->when($departmentsSel, function($q) use ($departmentsSel) {
                $depts = is_array($departmentsSel) ? $departmentsSel : [$departmentsSel];
                $q->whereHas('department', fn($dq)=>$dq->whereIn('department_name', $depts));
            });

        $filteredJobs = $jobsQuery->get();

        $allJobs = Job::where('company_id',$companyId)
            ->select($select)
            ->with(['jobTypes:id,type',
                    'workEnvironments:id,environment_type',
                    'department:id,department_name',        
                    'salary'])
            ->get();

        $experienceLevels = $allJobs->pluck('job_experience_level')->filter()->unique()->sort()->values()->toArray();

        $workEnvironments = [];
        if (Schema::hasTable('work_environments')) {
            $workEnvironments = \App\Models\WorkEnvironment::select('id', 'environment_type')->get()->toArray();
        }
        if (Schema::hasColumn('jobs','work_environment')) {
            $legacy = $allJobs->pluck('work_environment')->filter()->unique()->map(fn($v) => ['id' => $v, 'environment_type' => $v])->values()->toArray();
            $workEnvironments = array_merge($workEnvironments, $legacy);
        }
        $workEnvironments = collect($workEnvironments)->unique('environment_type')->values()->toArray();

        $jobTypes = JobType::select('id','type')->get();

        $totalOpeningsFiltered   = $filteredJobs->count();
        $activeListingsFiltered  = $filteredJobs->where('status','open')->count();
        $rolesFilledFiltered     = $filteredJobs->where('status','filled')->count();

        $typeCounts = [];
        foreach ($jobTypes as $jt) {
            $typeCounts[$jt->type] =
                $filteredJobs->where('job_type',$jt->type)->count()
                + $filteredJobs->filter(fn($j)=>$j->jobTypes->contains('type',$jt->type))->count();
        }

        $jobsPaginated = $filteredJobs->sortByDesc('created_at')->values();

        $overallTotal  = $allJobs->count();
        $overallActive = $allJobs->where('status','open')->count();
        $overallFilled = $allJobs->where('status','filled')->count();

        $dateRangeLabel = $datePreset === 'custom'
            ? (($dateFrom?->format('Y-m-d') ?? '—').' to '.($dateTo?->format('Y-m-d') ?? '—'))
            : ucfirst(str_replace('_',' ',$datePreset));

        // --- Department Counts (Filtered) ---
        $departmentCounts = Job::selectRaw('departments.department_name as department, COUNT(jobs.id) as total')
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->when($departmentsSel, function($q) use ($departmentsSel) {
                $depts = is_array($departmentsSel) ? $departmentsSel : [$departmentsSel];
                $q->whereIn('departments.department_name', $depts);
            })
            ->where('jobs.status', 'open')
            ->groupBy('departments.department_name')
            ->get();

        // --- Role Levels (Filtered) ---
        $roleLevels = ['Entry-level', 'Intermediate', 'Mid-level', 'Senior/Executive'];
        $stackedData = [];
        foreach ($roleLevels as $level) {
            $stackedData[$level] = Job::selectRaw('departments.department_name as department, COUNT(jobs.id) as total')
                ->join('departments', 'jobs.department_id', '=', 'departments.id')
                ->where('jobs.company_id', $companyId)
                ->when($departmentsSel, function($q) use ($departmentsSel) {
                    $depts = is_array($departmentsSel) ? $departmentsSel : [$departmentsSel];
                    $q->whereIn('departments.department_name', $depts);
                })
                ->where('jobs.status', 'open')
                ->where('jobs.job_experience_level', $level)
                ->groupBy('departments.department_name')
                ->pluck('total', 'department')
                ->toArray();
        }
        $allJobsDept = Job::select([
            'jobs.id',
            'jobs.job_title',
            'departments.department_name as department',
            'jobs.job_experience_level',
            'jobs.status'
        ])
            ->join('departments', 'jobs.department_id', '=', 'departments.id')
            ->where('jobs.company_id', $companyId)
            ->get();
        $stackedData['allJobs'] = $allJobsDept;

        // --- Trends (Filtered) ---
        $departments = \App\Models\Department::whereHas('jobs', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->pluck('department_name')->filter()->unique()->values()->toArray();

        $jobsList = Job::with('department')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->job_title,
                    'created_at' => $job->created_at->format('Y-m-d'),
                    'department' => $job->department ? $job->department->department_name : '',
                    'status' => $job->status,
                ];
            });

        $monthlyPostings = Job::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('company_id', $companyId)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

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

        // --- Employment Type (Filtered) ---
        $types = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];
        $typeCountsEmp = [];
        foreach ($types as $type) {
            $typeCountsEmp[$type] = Job::where('company_id', $companyId)
                ->whereHas('jobTypes', function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->when($departmentsSel, function($q) use ($departmentsSel) {
                    $depts = is_array($departmentsSel) ? $departmentsSel : [$departmentsSel];
                    $q->whereHas('department', fn($dq)=>$dq->whereIn('department_name', $depts));
                })
                ->count();
        }
        $departmentsEmp = \App\Models\Department::pluck('department_name');
        $columnData = [];
        foreach ($types as $type) {
            $columnData[$type] = [];
            foreach ($departmentsEmp as $dept) {
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

        // --- Salary Distribution (Histogram only, no boxplot) ---
        $allJobsSalary = Job::where('company_id', $companyId)->with('salary')->get();
        $allSalaries = [];
        foreach ($allJobsSalary as $job) {
            $minS = $job->salary->job_min_salary ?? $job->job_min_salary;
            $maxS = $job->salary->job_max_salary ?? $job->job_max_salary;
            $minS = is_numeric($minS) ? (float)$minS : null;
            $maxS = is_numeric($maxS) ? (float)$maxS : null;
            if (!is_null($minS) && !is_null($maxS)) $allSalaries[] = ($minS+$maxS)/2;
            elseif (!is_null($minS)) $allSalaries[] = $minS;
            elseif (!is_null($maxS)) $allSalaries[] = $maxS;
        }
        $allSalaries = array_values(array_filter($allSalaries, fn($v)=>$v>0));
        $bins = [];
        if (count($allSalaries)) {
            $binSize = 10000;
            $minSalary = floor(min($allSalaries) / $binSize) * $binSize;
            $maxSalary = ceil(max($allSalaries) / $binSize) * $binSize;
            if ($minSalary === $maxSalary) $maxSalary = $minSalary + $binSize;
            for ($b = $minSalary; $b < $maxSalary; $b += $binSize) {
                $bins[] = ['range'=>$b.'-'.($b+$binSize-1), 'count'=>0, 'jobs'=>[]];
            }
            foreach ($allJobsSalary as $job) {
                $minS = $job->salary->job_min_salary ?? $job->job_min_salary;
                $maxS = $job->salary->job_max_salary ?? $job->job_max_salary;
                $minS = is_numeric($minS) ? (float)$minS : null;
                $maxS = is_numeric($maxS) ? (float)$maxS : null;
                if (is_null($minS) && is_null($maxS)) continue;
                $mid = (!is_null($minS) && !is_null($maxS)) ? (($minS+$maxS)/2) : (!is_null($minS) ? $minS : $maxS);
                if ($mid <= 0) continue;
                $index = (int) floor(($mid - $minSalary) / $binSize);
                if (!isset($bins[$index])) continue;
                $bins[$index]['count']++;
                $bins[$index]['jobs'][] = $job->job_title;
            }
        }

        // --- Performance (filtered applications) ---
        $filteredJobIds = $filteredJobs->pluck('id');
        $appsBase = JobApplication::whereIn('job_id', $filteredJobIds)
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo));

        $genders = ['Male','Female'];
        $genderCounts = [];
        foreach ($genders as $gender) {
            $genderCounts[$gender] = (clone $appsBase)
                ->whereHas('graduate', fn($q)=>$q->where('gender',$gender))
                ->count();
        }
        $ethnicities = Graduate::select('ethnicity')->distinct()->pluck('ethnicity')->toArray();
        $ethnicityCounts = [];
        foreach ($ethnicities as $ethnicity) {
            if (!$ethnicity) continue;
            $ethnicityCounts[$ethnicity] = (clone $appsBase)
                ->whereHas('graduate', fn($q)=>$q->where('ethnicity',$ethnicity))
                ->count();
        }

        // --- Return all data to the dashboard page ---
        return Inertia::render('Company/Reports/JobsReport', [
            'totalOpenings' => $totalOpeningsFiltered,
            'activeListings'=> $activeListingsFiltered,
            'rolesFilled'   => $rolesFilledFiltered,
            'typeCounts'    => $typeCounts,
            'jobTypes'      => $jobTypes,
            'jobs' => [
                'data' => $jobsPaginated->take(10)->values(),
                'total'=> $jobsPaginated->count()
            ],
            'allJobs' => $filteredJobs,
            'filters' => [
                'date_preset' => $datePreset,
                'date_from' => $dateFromInput,
                'date_to' => $dateToInput,
                'experience_level' => $experienceLevel,
                'work_environment' => $request->query('work_environment', ''),
                'departments' => $departmentsSel,
                'date_range_label' => $dateRangeLabel,
            ],
            'filterOptions' => [
                'experience_levels' => $experienceLevels,
                'work_environments' => $workEnvironments,
            ],
            'overall' => [
                'total' => $overallTotal,
                'active'=> $overallActive,
                'filled'=> $overallFilled,
            ],
            // Department
            'departmentCounts' => $departmentCounts,
            'stackedData' => $stackedData,
            'roleLevels' => $roleLevels,
            // Trends
            'jobsList' => $jobsList,
            'jobPostingTrends' => $monthlyPostings,
            'areaChartLabels' => $months,
            'areaChartSeries' => $areaChartSeries,
            'departments' => $departments,
            // Employment Type
            'typeCountsEmp' => $typeCountsEmp,
            'departmentsEmp' => $departmentsEmp,
            'columnData' => $columnData,
            'types' => $types,
            // Salary Distribution (histogram only)
            'histogramBins' => $bins,
            // Performance
            'genderCounts' => $genderCounts,
            'ethnicityCounts' => $ethnicityCounts,
        ]);
    }

    public function recruitment(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->hr->company_id;

        // Unified Filters
        $department      = $request->query('department');
        $jobId           = $request->query('job_id');
        $experienceLevel = $request->query('experience_level');
        $stageFilter     = $request->query('stage');          // canonical stages
        $outcomeFilter   = $request->query('outcome');        // pass|fail|under (for screening logic)
        $datePreset      = $request->query('date_preset', 'last_90');
        $dateFromInput   = $request->query('date_from');
        $dateToInput     = $request->query('date_to');

        // Date Range Resolution
        $now = Carbon::now();
        $dateFrom = null; $dateTo = null;
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_7':  $dateFrom = $now->clone()->subDays(6)->startOfDay(); $dateTo = $now->endOfDay(); break;
                case 'last_30': $dateFrom = $now->clone()->subDays(29)->startOfDay(); $dateTo = $now->endOfDay(); break;
                case 'last_90': $dateFrom = $now->clone()->subDays(89)->startOfDay(); $dateTo = $now->endOfDay(); break;
                case 'this_month': $dateFrom = $now->clone()->startOfMonth(); $dateTo = $now->clone()->endOfMonth(); break;
                case 'this_year':  $dateFrom = $now->clone()->startOfYear();  $dateTo = $now->clone()->endOfYear(); break;
                case 'overall': default: /* leave nulls */ break;
            }
        } else {
            $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : null;
            $dateTo   = $dateToInput ? Carbon::parse($dateToInput)->endOfDay()   : null;
        }
        $dateRangeLabel = $datePreset === 'custom'
            ? (($dateFrom?->format('Y-m-d') ?? '—').' to '.($dateTo?->format('Y-m-d') ?? '—'))
            : ucfirst(str_replace('_',' ',$datePreset));

        // Base Jobs
        $jobsQ = Job::where('company_id',$companyId)
            ->with(['department:id,department_name','jobTypes:id,type','salary'])
            ->when($department, fn($q,$d)=>$q->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
            ->when($jobId, fn($q,$jid)=>$q->where('id',$jid))
            ->when($experienceLevel, fn($q,$lvl)=>$q->where('job_experience_level',$lvl))
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo));

        $jobs = $jobsQ->get();

        // Applications base
        $appsQ = JobApplication::with(['job.department','graduate'])
            ->whereHas('job', function($q) use ($companyId,$department,$jobId,$experienceLevel,$dateFrom,$dateTo) {
                $q->where('company_id',$companyId)
                  ->when($department, fn($qq,$d)=>$qq->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
                  ->when($jobId, fn($qq,$jid)=>$qq->where('id',$jid))
                  ->when($experienceLevel, fn($qq,$lvl)=>$qq->where('job_experience_level',$lvl))
                  ->when($dateFrom, fn($qq)=>$qq->where('created_at','>=',$dateFrom))
                  ->when($dateTo, fn($qq)=>$qq->where('created_at','<=',$dateTo));
            })
            ->when($stageFilter, fn($q,$st)=>$q->where('stage',$st))
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo));

        $applications = $appsQ->get();

        $appIds = $applications->pluck('id');
        // Stage Logs (for funnel + durations)
        $logs = JobApplicationStageLog::whereIn('job_application_id',$appIds)
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
            ->orderBy('created_at')
            ->get(['job_application_id','from_stage','to_stage','created_at']);

        // Canonical stage ordering
        $canonicalStages = ['applied','screening','assessment','interview','offer','hired'];
        $variantMap = [
            'applied'   => ['applied'],
            'screening' => ['screening','review'],
            'assessment'=> ['assessment','exam','test'],
            'interview' => ['interview','interview_1','interview_2'],
            'offer'     => ['offer','offered'],
            'hired'     => ['hired']
        ];
        $canon = function($s) use ($variantMap) {
            $s = strtolower($s ?? '');
            foreach ($variantMap as $c=>$vars) if (in_array($s,$vars)) return $c;
            return $s;
        };

        /* ---------------- Hiring Funnel (Stage Counts & Durations) ---------------- */
        $stageReach = [];
        foreach ($applications as $app) {
            $stageReach['applied'][$app->id] = true;
        }
        foreach ($logs as $log) {
            $stageReach[$canon($log->to_stage)][$log->job_application_id] = true;
        }
        $stageCounts = collect($canonicalStages)->map(fn($st)=>[
            'slug'=>$st,
            'name'=>ucfirst($st),
            'count'=> isset($stageReach[$st]) ? count($stageReach[$st]) : 0
        ])->values();

        // Transition durations (simple average between ordered canonical stage arrivals)
        $entryTimes = [];
        foreach ($applications as $app) {
            $entryTimes[$app->id]['applied'] = $app->created_at;
        }
        foreach ($logs as $log) {
            $entryTimes[$log->job_application_id][$canon($log->to_stage)] = Carbon::parse($log->created_at);
        }
        $transitionPairs = [];
        for ($i=0;$i<count($canonicalStages)-1;$i++){
            $transitionPairs[] = [$canonicalStages[$i], $canonicalStages[$i+1]];
        }
        $transitionDurations = [];
        foreach ($entryTimes as $appId=>$et) {
            foreach ($transitionPairs as [$a,$b]) {
                if (isset($et[$a], $et[$b])) {
                    $days = $et[$a]->diffInDays($et[$b]);
                    $transitionDurations["$a->$b"][] = $days;
                }
            }
        }
        $lineChart = [
            'labels' => array_map(fn($p)=>str_replace('->',' → ',$p), array_keys($transitionDurations)),
            'series' => [
                [
                    'name' => 'Avg Days',
                    'data' => array_map(fn($arr)=> round(array_sum($arr)/max(1,count($arr)),2), $transitionDurations)
                ]
            ]
        ];

        /* ---------------- Applicant Status Overview ---------------- */
        $statusMapReadable = [
            'applied'=>'Applied','screening'=>'Screening','assessment'=>'Assessment',
            'interview'=>'Interview','offer'=>'Offer','hired'=>'Hired','rejected'=>'Rejected'
        ];
        $statusCounts = [];
        foreach ($applications as $app) {
            $label = $statusMapReadable[$canon($app->stage)] ?? ucfirst($app->stage ?? 'Unknown');
            $statusCounts[$label] = ($statusCounts[$label] ?? 0) + 1;
        }
        $totalApplications = $applications->count();
        $shortlisted = $applications->where('stage','offer')->count();
        $rolesFilled = Job::where('company_id',$companyId)
            ->whereIn('id',$jobs->pluck('id'))
            ->get()
            ->filter(function($job) {
                $vac = (int)$job->job_vacancies;
                if ($vac <= 0) return false;
                $hiredCount = JobApplication::where('job_id',$job->id)->where('stage','hired')->count();
                return $hiredCount >= $vac;
            })->count();

        $appStatusRows = $applications->map(fn($a)=>[
            'id'=>$a->id,
            'applicant_name'=>$a->graduate?->user?->name ?? '—',
            'job_title'=>$a->job?->job_title,
            'department'=>$a->job?->department?->department_name,
            'stage'=>$a->stage,
            'stage_label'=>$statusMapReadable[$canon($a->stage)] ?? ucfirst($a->stage ?? '—'),
            'applied_date'=>$a->created_at?->format('Y-m-d'),
            'reached_offer'=> (bool)$logs->where('job_application_id',$a->id)->first(fn($l)=>$canon($l->to_stage)==='offer')
        ])->values();

        /* ---------------- Screening Insights (Simplified) ---------------- */
        // Very lightweight heuristic: skill match vs job skills length
        $stackedCategories = [];
        $screeningBuckets = ['Qualification:Pass'=>0,'Qualification:Under'=>0,'Experience:Pass'=>0,'Experience:Under'=>0,'Skill Match:High'=>0,'Skill Match:Low'=>0];
        foreach ($applications as $app) {
            $expOk = true; // placeholder rule
            $qualOk = true;
            $jobSkills = [];
            if ($app->job && $app->job->skills) {
                $jobSkills = is_array($app->job->skills) ? $app->job->skills : (json_decode($app->job->skills,true) ?: []);
            }
            $gradSkills = $app->graduate?->graduateSkills?->pluck('skill.name')->filter()->values()->toArray() ?? [];
            $overlap = count(array_intersect(array_map('strtolower',$jobSkills), array_map('strtolower',$gradSkills)));
            $skillPct = count($jobSkills) ? ($overlap / count($jobSkills))*100 : 0;
            $screeningBuckets['Qualification:'.($qualOk?'Pass':'Under')]++;
            $screeningBuckets['Experience:'.($expOk?'Pass':'Under')]++;
            $screeningBuckets['Skill Match:'.($skillPct>=50?'High':'Low')]++;
        }
        $stackedCategories = array_keys($screeningBuckets);
        $stackedSeries = [[
            'name'=>'Count',
            'type'=>'bar',
            'data'=>array_values($screeningBuckets)
        ]];
        $deptCategories = $jobs->pluck('department.department_name')->filter()->unique()->values()->toArray();
        $deptSeries = [[
            'name'=>'Jobs',
            'type'=>'bar',
            'data'=>array_map(fn($d)=> $jobs->where('department.department_name',$d)->count(), $deptCategories)
        ]];
        $roleCategories = $jobs->pluck('job_title')->take(12)->values()->toArray();
        $roleSeries = [[
            'name'=>'Jobs',
            'type'=>'bar',
            'data'=>array_map(fn($t)=> $jobs->where('job_title',$t)->count(), $roleCategories)
        ]];
        $screeningSummary = [
            'total_candidates'=>$applications->count(),
            'pass_pct'=>0,
            'fail_pct'=>0,
            'under_pct'=>0,
            'date_range_label'=>$dateRangeLabel,
            'filters_active'=>(bool)($department||$jobId||$experienceLevel||$stageFilter||$outcomeFilter||$datePreset!=='overall')
        ];

        /* ---------------- Efficiency (Time-to-Hire & Stage Time by Dept) ---------------- */
        // Time-to-hire line (monthly avg)
        $hireLogs = $logs->filter(fn($l)=> $canon($l->to_stage)==='hired');
        $tthBuckets = [];
        foreach ($hireLogs as $hl) {
            $app = $applications->firstWhere('id',$hl->job_application_id);
            if (!$app) continue;
            $jobCreated = $app->job?->created_at;
            if (!$jobCreated) continue;
            $mKey = Carbon::parse($hl->created_at)->format('Y-m');
            $days = Carbon::parse($jobCreated)->diffInDays(Carbon::parse($hl->created_at));
            $tthBuckets[$mKey][] = $days;
        }
        ksort($tthBuckets);
        $tthLine = [
            'labels'=>array_keys($tthBuckets),
            'series'=>[[
                'name'=>'Avg TTH (days)',
                'data'=>array_map(fn($arr)=> round(array_sum($arr)/max(1,count($arr)),2), $tthBuckets)
            ]]
        ];
        // Offer Acceptance
        $offerApps = $applications->filter(fn($a)=> $a->stage==='offer' || $logs->where('job_application_id',$a->id)->contains(fn($l)=>$canon($l->to_stage)==='offer'));
        $hiredApps = $applications->filter(fn($a)=> $a->stage==='hired');
        $offerPie = [
            ['name'=>'Accepted','value'=>$hiredApps->count()],
            ['name'=>'Offered Only','value'=> max(0,$offerApps->count() - $hiredApps->count())]
        ];
        // Stage Time by Department (simplified sums)
        $stageTimeByDept = [
            'categories'=> $deptCategories,
            'series'=> collect($canonicalStages)->map(function($st) use ($deptCategories,$applications,$logs,$canon){
                return [
                    'name'=>ucfirst($st),
                    'type'=>'bar',
                    'stack'=>'time',
                    'data'=>array_map(function($dept) use ($applications,$logs,$st,$canon){
                        // Placeholder: count of entries instead of real durations
                        $appIds = $applications->filter(fn($a)=> $a->job?->department?->department_name === $dept)->pluck('id');
                        return $logs->whereIn('job_application_id',$appIds)->filter(fn($l)=>$canon($l->to_stage)===$st)->count();
                    }, $deptCategories)
                ];
            })->values()
        ];

        /* ---------------- Application Analysis (Jobs Summary & Charts) ---------------- */
        $summaryTable = $jobs->map(function($job){
            return [
                'jobTitle'=>$job->job_title,
                'department'=>$job->department?->department_name,
                'totalApplications'=> JobApplication::where('job_id',$job->id)->count(),
                'hired'=> JobApplication::where('job_id',$job->id)->where('stage','hired')->count(),
                'applicationYear'=> $job->created_at?->format('Y')
            ];
        })->values();

        // Stacked Bar (applications per stage per job - simplified)
        $stageNamesForBar = ['applied','interview','offer','hired'];
        $stackedBarSeries = collect($stageNamesForBar)->map(function($st) use ($jobs){
            return [
                'name'=>ucfirst($st),
                'type'=>'bar',
                'stack'=>'total',
                'data'=>$jobs->map(fn($j)=> JobApplication::where('job_id',$j->id)->where('stage',$st)->count())->values()
            ];
        })->values();
        $jobTitles = $jobs->pluck('job_title')->values();

        // Scatter (experience vs applications) placeholder
        $scatterData = $jobs->map(function($job){
            $apps = JobApplication::where('job_id',$job->id)->count();
            $yrs = rand(0,8); // placeholder
            return [$yrs,$apps,$job->job_title,[],$job->created_at?->format('Y')];
        })->values();

        // Applications line trend (monthly total)
        $monthlyApps = JobApplication::whereIn('job_id',$jobs->pluck('id'))
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as m, COUNT(*) as total")
            ->groupBy('m')->orderBy('m')->get();
        $lineData = $monthlyApps->pluck('total')->values();
        $months = $monthlyApps->pluck('m')->values();
        $areaSeries = $stackedBarSeries; // re-use (placeholder dept layering can be added)

        // Unified KPIs
        $kpis = [
            'total_jobs'=>$jobs->count(),
            'total_applications'=>$totalApplications,
            'shortlisted'=>$shortlisted,
            'roles_filled'=>$rolesFilled,
            'hired'=>$statusCounts['Hired'] ?? 0
        ];

        // Filter dropdown sources
        $departments = \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')->unique()->values();
        $jobOptions = Job::where('company_id',$companyId)
            ->select('id','job_title')->orderBy('job_title')->get();

        return Inertia::render('Company/Reports/RecruitmentReport', [
            'dateRangeLabel' => $dateRangeLabel,
            'filters'=>[
                'department'=>$department,
                'job_id'=>$jobId,
                'experience_level'=>$experienceLevel,
                'stage'=>$stageFilter,
                'outcome'=>$outcomeFilter,
                'date_preset'=>$datePreset,
                'date_from'=>$dateFromInput,
                'date_to'=>$dateToInput,
            ],
            'departments'=>$departments,
            'jobs'=>$jobOptions,
            'experienceLevels'=> Job::where('company_id',$companyId)->pluck('job_experience_level')->filter()->unique()->values(),
            // KPIs
            'kpis'=>$kpis,
            // Hiring Funnel
            'stageCounts'=>$stageCounts,
            'lineChart'=>$lineChart,
            // Applicant Status
            'statusCounts'=>$statusCounts,
            'totalApplications'=>$totalApplications,
            'shortlisted'=>$shortlisted,
            'rolesFilled'=>$rolesFilled,
            'appStatusRows'=>$appStatusRows,
            // Screening
            'stackedCategories'=>$stackedCategories,
            'stackedSeries'=>$stackedSeries,
            'deptCategories'=>$deptCategories,
            'deptSeries'=>$deptSeries,
            'roleCategories'=>$roleCategories,
            'roleSeries'=>$roleSeries,
            'screeningSummary'=>$screeningSummary,
            // Efficiency
            'tthLine'=>$tthLine,
            'offerPie'=>$offerPie,
            'stageTimeByDept'=>$stageTimeByDept,
            // Application Analysis
            'summaryTable'=>$summaryTable,
            'stackedBarSeries'=>$stackedBarSeries,
            'jobTitles'=>$jobTitles,
            'scatterData'=>$scatterData,
            'months'=>$months,
            'lineData'=>$lineData,
            'areaSeries'=>$areaSeries,
        ]);
    }

    public function overview(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->hr->company_id;

        $datePreset      = $request->query('date_preset','last_30');
        $dateFromInput   = $request->query('date_from');
        $dateToInput     = $request->query('date_to');
        $experienceLevel = $request->query('experience_level');
        $vacancyMin      = $request->query('vacancy_min');
        $vacancyMax      = $request->query('vacancy_max');
        $workEnvironment = $request->query('work_environment'); 

        $now = Carbon::now();
        $dateFrom = null; $dateTo = null;
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_7':     $dateFrom = $now->copy()->subDays(7)->startOfDay();  $dateTo = $now; break;
                case 'last_30':    $dateFrom = $now->copy()->subDays(30)->startOfDay(); $dateTo = $now; break;
                case 'this_month': $dateFrom = $now->copy()->startOfMonth(); $dateTo = $now->copy()->endOfMonth(); break;
                case 'last_month': $dateFrom = $now->copy()->subMonth()->startOfMonth(); $dateTo = $now->copy()->subMonth()->endOfMonth(); break;
                case 'overall': default: $dateFrom = null; $dateTo = null;
            }
        } else {
            $dateFrom = $dateFromInput ? Carbon::parse($dateFromInput)->startOfDay() : null;
            $dateTo   = $dateToInput ? Carbon::parse($dateToInput)->endOfDay()   : null;
        }

        // Dynamic select (avoid non-existent columns)
        $select = [
            'id','job_title','job_type','status',
            'job_experience_level','job_vacancies','created_at',
            'department_id','job_min_salary','job_max_salary'
        ];
        if (\Schema::hasColumn('jobs','work_environment')) {
            $select[] = 'work_environment';
        }

        $jobsQuery = Job::where('company_id',$companyId)
            ->select($select)
            ->with(['jobTypes:id,type','workEnvironments:id,environment_type', 'department:id,department_name',
                'salary'])
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
            ->when($experienceLevel, fn($q,$lvl)=>$q->where('job_experience_level',$lvl))
            ->when(is_numeric($vacancyMin), fn($q)=>$q->where('job_vacancies','>=',(int)$vacancyMin))
            ->when(is_numeric($vacancyMax), fn($q)=>$q->where('job_vacancies','<=',(int)$vacancyMax))
            ->when($workEnvironment, function($q) use ($workEnvironment) {
                // Accept string or array
                $vals = is_array($workEnvironment) ? array_filter($workEnvironment) : [$workEnvironment];
                if (!count($vals)) return;
                // 1) If jobs table has a direct column
                if (\Schema::hasColumn('jobs','work_environment')) {
                    $q->where(function($qq) use ($vals) {
                        $qq->whereIn('work_environment', $vals);
                    });
                }
                // 2) Also check many‑to‑many / pivot relation if it exists
                $q->orWhereHas('workEnvironments', function($wq) use ($vals) {
                    $wq->whereIn('environment_type', $vals);
                });
            });

        $filteredJobs = $jobsQuery->get();

        
        $allJobs = Job::where('company_id',$companyId)
            ->select($select)
            ->with([
                'jobTypes:id,type',
                'workEnvironments:id,environment_type',
                'department:id,department_name',
                'salary'
            ])
            ->get();

        $experienceLevels = $allJobs->pluck('job_experience_level')
            ->filter()->unique()->sort()->values()->toArray();

        // Distinct work environments: pivot + legacy
        $workEnvironments = [];
        if (\Schema::hasTable('work_environments')) {
            $workEnvironments = \App\Models\WorkEnvironment::select('id', 'environment_type')->get()->toArray();
        }
        if (\Schema::hasColumn('jobs','work_environment')) {
            // Add legacy string values if any
            $legacy = $allJobs->pluck('work_environment')->filter()->unique()->map(fn($v) => ['id' => $v, 'environment_type' => $v])->values()->toArray();
            $workEnvironments = array_merge($workEnvironments, $legacy);
        }
        $workEnvironments = collect($workEnvironments)->unique('environment_type')->values()->toArray();

        $vacancyMinActual = (int)($allJobs->min('job_vacancies') ?? 0);
        $vacancyMaxActual = (int)($allJobs->max('job_vacancies') ?? 0);

        $jobTypes = JobType::select('id','type')->get();

        $totalOpeningsFiltered   = $filteredJobs->count();
        $activeListingsFiltered  = $filteredJobs->where('status','open')->count();
        $rolesFilledFiltered     = $filteredJobs->where('status','filled')->count();

        $typeCounts = [];
        foreach ($jobTypes as $jt) {
            $typeCounts[$jt->type] =
                $filteredJobs->where('job_type',$jt->type)->count()
                + $filteredJobs->filter(fn($j)=>$j->jobTypes->contains('type',$jt->type))->count();
        }

        $jobsPaginated = $filteredJobs->sortByDesc('created_at')->values();

        $overallTotal  = $allJobs->count();
        $overallActive = $allJobs->where('status','open')->count();
        $overallFilled = $allJobs->where('status','filled')->count();

        $dateRangeLabel = $datePreset === 'custom'
            ? (($dateFrom?->format('Y-m-d') ?? '—').' to '.($dateTo?->format('Y-m-d') ?? '—'))
            : ucfirst(str_replace('_',' ',$datePreset));

        $filtersActive = (bool)(
            ($datePreset && $datePreset!=='last_30') ||
            $experienceLevel ||
            $workEnvironment ||
            is_numeric($vacancyMin) ||
            is_numeric($vacancyMax) ||
            $datePreset === 'custom'
        );

        // Normalize work_environment param to array for return
        $workEnvFilterArray = is_array($workEnvironment)
            ? array_filter($workEnvironment)
            : ($workEnvironment ? [$workEnvironment] : []);

        return Inertia::render('Company/Reports/JobOverview', [
            'totalOpenings' => $totalOpeningsFiltered,
            'activeListings'=> $activeListingsFiltered,
            'rolesFilled'   => $rolesFilledFiltered,
            'typeCounts'    => $typeCounts,
            'jobTypes'      => $jobTypes,
            'jobs' => [
                'data' => $jobsPaginated->take(10)->values(),
                'total'=> $jobsPaginated->count()
            ],
            'allJobs' => $filteredJobs,
            'filters' => [
                'date_preset' => $datePreset,
                'date_from' => $dateFromInput,
                'date_to' => $dateToInput,
                'experience_level' => $experienceLevel,
                'work_environment' => $workEnvironments,
                'date_range_label' => $dateRangeLabel,
                'filters_active' => $filtersActive,
            ],
            'filterOptions' => [
                'experience_levels' => $experienceLevels,
                'work_environments' => $workEnvironments,
                'vacancy_min_actual' => $vacancyMinActual,
                'vacancy_max_actual' => $vacancyMaxActual,
            ],
            'overall' => [
                'total' => $overallTotal,
                'active'=> $overallActive,
                'filled'=> $overallFilled,
            ],
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
                'departments' => \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
                    ->pluck('department_name')->unique()->values(),
                'jobs' => Job::where('company_id',$companyId)->select('id','job_title')->orderBy('job_title')->get(),
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

        // Filters
        $dept       = $request->query('department');
        $jobId      = $request->query('job_id');
        $programId  = $request->query('program_id');
        $datePreset = $request->query('date_preset', 'overall'); // default to overall
        $dateFrom   = $request->query('date_from');
        $dateTo     = $request->query('date_to');

        // Resolve date range
        $now = Carbon::now();
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_30':  $dateFrom = $now->copy()->subDays(30)->startOfDay(); $dateTo = $now; break;
                case 'last_90':  $dateFrom = $now->copy()->subDays(90)->startOfDay(); $dateTo = $now; break;
                case 'this_year':$dateFrom = $now->copy()->startOfYear();             $dateTo = $now; break;
                case 'overall':
                default:         $dateFrom = null; $dateTo = null;
            }
        } else {
            $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $dateTo   = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
        }

        // Jobs base query (filtered)
        $jobsQ = Job::where('company_id', $companyId)
            ->when($dept, fn($q,$d)=>$q->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
            ->when($jobId, fn($q,$jid)=>$q->where('id',$jid))
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo));
        
        $filteredJobsCount = (clone $jobsQ)->count();
        
        $jobs = $jobsQ->get();

        // Frequency-based skill aggregation (normalize: lowercase, trim)
        $skillCounts = [];
        $certCounts = [];

        $parseSkills = function ($skills) {
            // 1) Relation: Collection of Skill models
            if ($skills instanceof \Illuminate\Support\Collection) {
                $skills = $skills->map(function ($item) {
                    if (is_string($item)) return $item;
                    if (is_object($item)) {
                        return $item->name ?? $item->skill ?? '';
                    }
                    if (is_array($item)) {
                        return $item['name'] ?? $item['skill'] ?? reset($item) ?? '';
                    }
                    return '';
                })->filter()->values()->all();
            }

            // 2) JSON string or CSV string
            if (is_string($skills)) {
                $decoded = json_decode($skills, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $skills = $decoded;
                } else {
                    // Split by comma/semicolon/pipe
                    $skills = preg_split('/[,\|;]+/', $skills) ?: [];
                }
            }

            // 3) Array of strings/objects
            if (!is_array($skills)) $skills = [];
            $norm = [];
            foreach ($skills as $s) {
                if (is_object($s)) {
                    $s = $s->name ?? $s->skill ?? '';
                } elseif (is_array($s)) {
                    $s = $s['name'] ?? $s['skill'] ?? reset($s) ?? '';
                }
                $s = trim((string)$s);
                if ($s === '') continue;
                $norm[] = strtolower($s);
            }

            // Deduplicate
            return array_values(array_unique($norm));
        };

        foreach ($jobs as $job) {
            foreach ($parseSkills($job->skills) as $s) {
                $skillCounts[$s] = ($skillCounts[$s] ?? 0) + 1;
            }
            // Certifications (if present as array/JSON/CSV)
            $certs = $parseSkills($job->certifications ?? []);
            foreach ($certs as $c) {
                $certCounts[$c] = ($certCounts[$c] ?? 0) + 1;
            }
        }

        // Graduates pool (filtered by program if provided)
        $graduates = Graduate::with('graduateSkills.skill')
            ->when($programId, fn($q,$pid)=>$q->where('program_id',$pid))
            ->get();

        // Supply counts (talent pool)
        $talentPool = [];
        foreach ($graduates as $grad) {
            foreach ($grad->graduateSkills as $gs) {
                $name = strtolower(trim($gs->skill->name ?? ''));
                if ($name !== '') {
                    $talentPool[$name] = ($talentPool[$name] ?? 0) + 1;
                }
            }
        }

        // Bubble chart data (demand vs supply)
        $bubbleData = [];
        $allSkills = array_values(array_unique(array_merge(array_keys($skillCounts), array_keys($talentPool))));
        foreach ($allSkills as $skill) {
            $bubbleData[] = [
                'name' => $skill,
                'demand' => $skillCounts[$skill] ?? 0,
                'supply' => $talentPool[$skill] ?? 0,
            ];
        }

        // Graduate skill word cloud (supply only)
        $gradSkillCounts = $talentPool;

        // Radar: top N demanded skills
        $topSkills = collect($skillCounts)->sortDesc()->take(6)->keys()->toArray();
        $radarIndicators = [];
        $jobSkillValues = [];
        $gradSkillValues = [];
        foreach ($topSkills as $s) {
            $max = max($skillCounts[$s] ?? 1, $gradSkillCounts[$s] ?? 1);
            $radarIndicators[] = ['name' => $s, 'max' => $max];
            $jobSkillValues[] = $skillCounts[$s] ?? 0;
            $gradSkillValues[] = $gradSkillCounts[$s] ?? 0;
        }

        // Filter dropdown sources
        $departments = \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')
            ->filter()
            ->unique(fn($v) => mb_strtolower($v))
            ->sort()
            ->values();

        $jobOptions = $this->uniqueJobOptions($companyId, $dept, $programId);
        $programOptions = $this->uniqueProgramOptionsForCompany($companyId, $dept, $jobId);

        return Inertia::render('Company/Reports/SkillQuali', [
            'skillWordCloud' => $skillCounts,
            'certWordCloud' => $certCounts,
            'bubbleData' => $bubbleData,
            'gradSkillWordCloud' => $gradSkillCounts,
            'radarIndicators' => $radarIndicators,
            'jobSkillValues' => $jobSkillValues,
            'gradSkillValues' => $gradSkillValues,
            'filters' => [
                'department' => $dept,
                'job_id' => $jobId,
                'program_id' => $programId,
                'date_preset' => $datePreset,
                'date_from' => $request->query('date_from'),
                'date_to'   => $request->query('date_to'),
            ],
            'departments' => $departments,
            'jobs' => $jobOptions,
            'programs' => $programOptions,
            'totals' => [
                'jobs' => $filteredJobsCount,
            ],
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
        $companyId = $user->hr->company_id;

        // Filters
        $dept       = $request->query('department');
        $jobId      = $request->query('job_id');
        $outcome    = $request->query('outcome'); // pass | fail | under
        $expLevel   = $request->query('experience_level'); // fresh, entry, mid, intermediate, senior
        $datePreset = $request->query('date_preset','last_30');
        $dateFrom   = $request->query('date_from');
        $dateTo     = $request->query('date_to');

        // Resolve date range
        $now = Carbon::now();
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_7':
                    $dateFrom = $now->copy()->subDays(7)->startOfDay();
                    $dateTo   = $now;
                    break;
                case 'last_30':
                    $dateFrom = $now->copy()->subDays(30)->startOfDay();
                    $dateTo   = $now;
                    break;
                case 'last_90':
                    $dateFrom = $now->copy()->subDays(90)->startOfDay();
                    $dateTo   = $now;
                    break;
                case 'this_month':
                    $dateFrom = $now->copy()->startOfMonth();
                    $dateTo   = $now;
                    break;
                case 'this_year':
                    $dateFrom = $now->copy()->startOfYear();
                    $dateTo   = $now;
                    break;
                case 'overall':
                default:
                    $dateFrom = null;
                    $dateTo   = null;
            }
        } else {
            $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
        }

        // Fetch applications (include legacy stage variants, then normalize)
        $legacyAndCanonicalStages = [
            'applied','screening','assessment','interview','offer','hired','rejected',
            'screened','interview_1','interview_2','final_selection'
        ];

        $apps = JobApplication::with([
                'job.department',
                'graduate.education' => fn($q)=>$q->orderByDesc('end_date'),
                'graduate.experience',
                'graduate.graduateSkills.skill'
            ])
            ->whereHas('job', function($q) use ($companyId,$dept,$jobId){
                $q->where('company_id',$companyId)
                  ->when($dept, fn($qq,$d)=>$qq->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
                  ->when($jobId, fn($qq,$jid)=>$qq->where('id',$jid));
            })
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
            ->whereIn('stage',$legacyAndCanonicalStages)
            ->get();

        if ($apps->isEmpty()) {
            return Inertia::render('Company/Reports/CandidateScreeningInsights', [
                'stackedCategories'=>[],
                'stackedSeries'=>[],
                'deptCategories'=>[],
                'deptSeries'=>[],
                'roleCategories'=>[],
                'roleSeries'=>[],
                'filters'=>[
                    'department'=>$dept,
                    'job_id'=>$jobId,
                    'outcome'=>$outcome,
                    'experience_level'=>$expLevel,
                    'date_preset'=>$datePreset,
                    'date_from'=>$request->query('date_from'),
                    'date_to'=>$request->query('date_to'),
                ],
                'departments' => \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))->pluck('department_name')->unique()->values(),
                'jobs' => Job::where('company_id',$companyId)->select('id','job_title')->orderBy('job_title')->get(),
                'experienceLevels'=>['fresh','entry','mid','intermediate','senior'],
                'summary'=>[
                    'total_candidates'=>0,
                    'pass_pct'=>0,'fail_pct'=>0,'under_pct'=>0,
                    'filters_active' => (bool)($dept||$jobId||$outcome||$expLevel||$datePreset!=='last_30'||$datePreset==='custom'),
                    'date_range_label'=>$datePreset==='custom'
                        ? (($dateFrom?->format('Y-m-d')??'—').' to '.($dateTo?->format('Y-m-d')??'—'))
                        : ucfirst(str_replace('_',' ',$datePreset)),
                ],
            ]);
        }

        // Normalization map
        $normalizeStage = function($stage) {
            $s = strtolower($stage ?? '');
            return match($s) {
                'screened' => 'screening',
                'interview_1','interview_2','final_selection' => 'interview',
                default => $s
            };
        };

        // Helpers
        $calcYears = function($grad){
            if (!$grad) return 0;
            $expCollection = $grad->experience ?? collect();
            if (!$expCollection->count()) return 0;
            $months = 0;
            foreach ($expCollection as $exp) {
                if (!$exp->start_date) continue;
                $start = Carbon::parse($exp->start_date);
                $end   = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                if ($end->lt($start)) continue;
                $months += $start->diffInMonths($end);
            }
            return round($months/12,2);
        };

        $expBucket = fn($years)=> $years <= 0 ? 'fresh'
            : ($years < 2 ? 'entry'
            : ($years < 5 ? 'mid'
            : ($years < 10 ? 'intermediate' : 'senior')));

        $skillOverlapPercent = function($jobSkills,$candSkills){
            if (is_string($jobSkills)) {
                $jobSkills = array_filter(array_map('trim', explode(',', $jobSkills)));
            }
            if (!is_array($jobSkills)) $jobSkills = [];
            if (!is_array($candSkills)) $candSkills = [];
            $jobSkills = array_values(array_unique(array_filter($jobSkills)));
            $candSkills = array_values(array_unique(array_filter($candSkills)));
            if (!count($jobSkills) || !count($candSkills)) return null;
            $overlap = count(array_intersect(
                array_map('strtolower',$jobSkills),
                array_map('strtolower',$candSkills)
            ));
            return round(($overlap / count($jobSkills))*100,2);
        };

        $skillBand = function($pct){
            if ($pct === null) return 'no_data';
            if ($pct >= 80) return 'high';
            if ($pct >= 50) return 'medium';
            return 'low';
        };

        // Outcome classification
        $classifyOutcome = function($stage){
            if ($stage === 'rejected') return 'fail';
            if (in_array($stage, ['applied','screening'])) return 'under';
            if ($stage === 'hired') return 'pass';
            // progressed beyond screening (assessment, interview, offer)
            return 'pass';
        };

        // Aggregation containers
        $experienceCounts = [];
        $qualificationCounts = [];
        $skillMatchCounts = [];
        $deptCounts = [];
        $roleCounts = [];
        $uniqueOutcomeTotals = ['pass'=>0,'fail'=>0,'under'=>0];
        $processedAppIds = [];

        $inc = function (&$arr,$key,$out) {
            if (!isset($arr[$key])) $arr[$key] = ['pass'=>0,'fail'=>0,'under'=>0];
            $arr[$key][$out] = ($arr[$key][$out] ?? 0) + 1;
        };

        foreach ($apps as $app) {
            $stage = $normalizeStage($app->stage);
            $out = $classifyOutcome($stage);

            // Experience
            $years = $calcYears($app->graduate);
            $expB = $expBucket($years);

            // Qualification (latest education record)
            $qual = 'Unknown';
            $educs = $app->graduate?->education;
            if ($educs && $educs->count()) {
                $latest = $educs->first(); // already ordered desc
                $qual = $latest?->education ?: ($latest?->program ?? 'Unknown');
            }

            // Skills
            $jobSkills = $app->job?->skills;
            $candSkills = $app->graduate?->graduateSkills?->pluck('skill.name')->filter()->values()->toArray() ?? [];
            $pct = $skillOverlapPercent($jobSkills, $candSkills);
            $skillBandKey = $skillBand($pct);

            // Filters
            if ($expLevel && $expB !== $expLevel) continue;
            if ($outcome && $out !== $outcome) continue;

            $deptName = $app->job?->department?->department_name ?: 'Unassigned';
            $roleName = $app->job?->job_title ?: 'Unknown';

            if (!in_array($app->id, $processedAppIds)) {
                $uniqueOutcomeTotals[$out] = ($uniqueOutcomeTotals[$out] ?? 0) + 1;
                $processedAppIds[] = $app->id;
            }
            
            $inc($experienceCounts,$expB,$out);
            $inc($qualificationCounts,$qual,$out);
            $inc($skillMatchCounts,$skillBandKey,$out);
            $inc($deptCounts,$deptName,$out);
            $inc($roleCounts,$roleName,$out);
        }

        // Merge dimensions for stacked bar
        $categoryCounts = [];
        $mergeCat = function($source,$prefix) use (&$categoryCounts){
            foreach ($source as $label=>$vals){
                $key = $prefix.': '.$label;
                $categoryCounts[$key] = $vals;
            }
        };
        $mergeCat($qualificationCounts,'Qualification');
        $mergeCat($experienceCounts,'Experience');
        $mergeCat($skillMatchCounts,'Skill Match');

        // Sort by total desc
        $categoryCounts = collect($categoryCounts)
            ->sortByDesc(fn($v)=>($v['pass']+$v['fail']+$v['under']))
            ->toArray();

        $stackedCategories = array_keys($categoryCounts);
        $stackedSeries = [
            ['name'=>'Pass','type'=>'bar','stack'=>'total','data'=>array_map(fn($c)=>$c['pass'],$categoryCounts),'itemStyle'=>['color'=>'#16a34a']],
            ['name'=>'Fail','type'=>'bar','stack'=>'total','data'=>array_map(fn($c)=>$c['fail'],$categoryCounts),'itemStyle'=>['color'=>'#dc2626']],
            ['name'=>'Under Review','type'=>'bar','stack'=>'total','data'=>array_map(fn($c)=>$c['under'],$categoryCounts),'itemStyle'=>['color'=>'#f59e0b']],
        ];

        // Department clustered
        $deptCategories = array_keys($deptCounts);
        $deptSeries = [
            ['name'=>'Pass','type'=>'bar','data'=>array_map(fn($d)=>$d['pass'],$deptCounts),'itemStyle'=>['color'=>'#16a34a']],
            ['name'=>'Fail','type'=>'bar','data'=>array_map(fn($d)=>$d['fail'],$deptCounts),'itemStyle'=>['color'=>'#dc2626']],
            ['name'=>'Under','type'=>'bar','data'=>array_map(fn($d)=>$d['under'],$deptCounts),'itemStyle'=>['color'=>'#f59e0b']],
        ];

        // Role clustered (top 12)
        $roleCountsSorted = collect($roleCounts)
            ->sortByDesc(fn($v)=>$v['pass']+$v['fail']+$v['under'])
            ->slice(0,12)->toArray();
        $roleCategories = array_keys($roleCountsSorted);
        $roleSeries = [
            ['name'=>'Pass','type'=>'bar','data'=>array_map(fn($d)=>$d['pass'],$roleCountsSorted),'itemStyle'=>['color'=>'#16a34a']],
            ['name'=>'Fail','type'=>'bar','data'=>array_map(fn($d)=>$d['fail'],$roleCountsSorted),'itemStyle'=>['color'=>'#dc2626']],
            ['name'=>'Under','type'=>'bar','data'=>array_map(fn($d)=>$d['under'],$roleCountsSorted),'itemStyle'=>['color'=>'#f59e0b']],
        ];

        // Summary
        $totalUnique = array_sum($uniqueOutcomeTotals);
        if ($totalUnique === 0) {
            $summary = [
                'total_candidates'=>0,
                'pass_pct'=>0,'fail_pct'=>0,'under_pct'=>0,
                'filters_active' => (bool)($dept||$jobId||$outcome||$expLevel||$datePreset!=='last_30'||$datePreset==='custom'),
                'date_range_label'=>$datePreset==='custom'
                    ? (($dateFrom?->format('Y-m-d')??'—').' to '.($dateTo?->format('Y-m-d')??'—'))
                    : ucfirst(str_replace('_',' ',$datePreset)),
            ];
        } else {
            $summary = [
                'total_candidates'=>$totalUnique,
                'pass_pct'=>round($uniqueOutcomeTotals['pass'] / $totalUnique * 100, 2),
                'fail_pct'=>round($uniqueOutcomeTotals['fail'] / $totalUnique * 100, 2),
                'under_pct'=>round($uniqueOutcomeTotals['under'] / $totalUnique * 100, 2),
                'filters_active' => (bool)($dept||$jobId||$outcome||$expLevel||$datePreset!=='last_30'||$datePreset==='custom'),
                'date_range_label'=>$datePreset==='custom'
                    ? (($dateFrom?->format('Y-m-d')??'—').' to '.($dateTo?->format('Y-m-d')??'—'))
                    : ucfirst(str_replace('_',' ',$datePreset)),
            ];
        }

        // Sources
        $departments = \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')->unique()->values();
        $jobs = Job::where('company_id',$companyId)
            ->select('id','job_title')
            ->orderBy('job_title')
            ->get();
        $experienceLevels = ['fresh','entry','mid','intermediate','senior'];

          // --- Normalization / Safety ---
        $normalizeSeries = function(array $categories, array $series) {
            $count = count($categories);
            foreach ($series as &$s) {
                $data = $s['data'] ?? [];
                // Reindex & pad/trim
                $data = array_values($data);
                if (count($data) !== $count) {
                    // Pad or trim to match
                    if (count($data) < $count) {
                        $data = array_pad($data, $count, 0);
                    } else {
                        $data = array_slice($data, 0, $count);
                    }
                }
                // Force numeric
                $data = array_map(fn($v)=> (is_numeric($v) ? (int)$v : 0), $data);
                $s['data'] = $data;
            }
            return array_values($series);
        };

        $stackedSeries = $normalizeSeries($stackedCategories, $stackedSeries);
        $deptSeries    = $normalizeSeries($deptCategories, $deptSeries);
        $roleSeries    = $normalizeSeries($roleCategories, $roleSeries);

        return Inertia::render('Company/Reports/CandidateScreeningInsights', [
            'stackedCategories'=>$stackedCategories,
            'stackedSeries'=>$stackedSeries,
            'deptCategories'=>$deptCategories,
            'deptSeries'=>$deptSeries,
            'roleCategories'=>$roleCategories,
            'roleSeries'=>$roleSeries,
            'filters'=>[
                'department'=>$dept,
                'job_id'=>$jobId,
                'outcome'=>$outcome,
                'experience_level'=>$expLevel,
                'date_preset'=>$datePreset,
                'date_from'=>$request->query('date_from'),
                'date_to'=>$request->query('date_to'),
            ],
            'departments'=>$departments,
            'jobs'=>$jobs,
            'experienceLevels'=>$experienceLevels,
            'summary'=>$summary,
        ]);
    }

    public function interviewProgress(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->hr->company_id;

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
        $companyId = $user->hr->company_id;

        // Filters
        $dept       = $request->query('department'); // department name
        $jobId      = $request->query('job_id');     // job id
        $programId  = $request->query('program_id'); // graduate program id
        $stageSel   = $request->query('stage');      // applied|screening|assessment|interview|offer|hired
        $datePreset = $request->query('date_preset','last_90');
        $dateFrom   = $request->query('date_from');
        $dateTo     = $request->query('date_to');

        // Date range
        $now = Carbon::now();
        if ($datePreset !== 'custom') {
            switch ($datePreset) {
                case 'last_7':   $dateFrom = $now->copy()->subDays(7)->startOfDay();  $dateTo = $now; break;
                case 'last_30':  $dateFrom = $now->copy()->subDays(30)->startOfDay(); $dateTo = $now; break;
                case 'last_90':  $dateFrom = $now->copy()->subDays(90)->startOfDay(); $dateTo = $now; break;
                case 'this_month': $dateFrom = $now->copy()->startOfMonth(); $dateTo = $now; break;
                case 'this_year':  $dateFrom = $now->copy()->startOfYear();  $dateTo = $now; break;
                case 'overall': default: $dateFrom = null; $dateTo = null;
            }
        } else {
            $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
        }

        // Stage normalization
        $variantsByCanon = [
            'applied'    => ['applied'],
            'screening'  => ['screening','screened'],
            'assessment' => ['assessment'],
            'interview'  => ['interview','interview_1','interview_2','final_selection'],
            'offer'      => ['offer','offered'],
            'hired'      => ['hired'],
        ];
        $canonOrder = ['applied','screening','assessment','interview','offer','hired'];
        $allVariants = array_values(array_unique(array_merge(...array_values($variantsByCanon))));
        $canon = function ($s) use ($variantsByCanon) {
            $s = strtolower((string)$s);
            foreach ($variantsByCanon as $c => $arr) {
                if (in_array($s, $arr, true)) return $c;
            }
            return $s;
        };

        // Base job/application scope
        $jobScope = function($q) use ($companyId,$dept,$jobId) {
            $q->where('company_id',$companyId)
            ->when($dept, fn($qq,$d)=>$qq->whereHas('department', fn($dq)=>$dq->where('department_name',$d)))
            ->when($jobId, fn($qq,$jid)=>$qq->where('id',$jid));
        };

        // Base applications (for fallbacks, joins, etc.)
        $appsBase = JobApplication::with(['job.department','graduate'])
            ->whereHas('job', $jobScope)
            ->when($programId, fn($q,$pid)=>$q->whereHas('graduate', fn($g)=>$g->where('program_id',$pid)));

        $appIds = (clone $appsBase)->pluck('id');

        // Load logs (JobApplicationStageLog) as events
        $logsQuery = \App\Models\JobApplicationStageLog::whereIn('job_application_id', $appIds)
            ->whereIn('to_stage', $allVariants)
            ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
            ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
            ->orderBy('job_application_id')
            ->orderBy('created_at');

        $logs = $logsQuery->get(['job_application_id','to_stage','created_at']);

        // If a stage filter is selected, limit to apps that reached that canonical stage
        $filteredLogs = $logs;
        if ($stageSel) {
            $stageApps = $logs->filter(fn($e)=>$canon($e->to_stage) === $stageSel)->pluck('job_application_id')->unique();
            $filteredLogs = $logs->whereIn('job_application_id', $stageApps->all())->values();
        }

        // Fallback: if no logs in range but applications exist, synthesize "applied" from applications
        if ($filteredLogs->isEmpty() && $appIds->isNotEmpty()) {
            $appsForFallback = (clone $appsBase)
                ->when($dateFrom, fn($q)=>$q->where('created_at','>=',$dateFrom))
                ->when($dateTo, fn($q)=>$q->where('created_at','<=',$dateTo))
                ->get(['id','created_at']);
            foreach ($appsForFallback as $fa) {
                $filteredLogs->push((object)[
                    'job_application_id' => $fa->id,
                    'to_stage' => 'applied',
                    'created_at' => $fa->created_at,
                ]);
            }
        }

        // 1) Time-to-Hire (TTH): from job posted to hired log date; fallback to application.updated_at if stage is hired
        $hireLogs = (clone $logsQuery)->whereIn('to_stage', $variantsByCanon['hired'])->get(['job_application_id','to_stage','created_at']);
        if ($stageSel) {
            $allowedApps = $filteredLogs->pluck('job_application_id')->unique();
            $hireLogs = $hireLogs->whereIn('job_application_id', $allowedApps->all())->values();
        }

        // If no hire logs, but have apps with stage=hired in range, synthesize hired events
        if ($hireLogs->isEmpty()) {
            $hiredAppsQ = (clone $appsBase)->where('stage','hired');
            if ($dateFrom) $hiredAppsQ->where('updated_at','>=',$dateFrom);
            if ($dateTo)   $hiredAppsQ->where('updated_at','<=',$dateTo);
            $hiredApps = $hiredAppsQ->get(['id','updated_at']);
            foreach ($hiredApps as $ha) {
                $hireLogs->push((object)[
                    'job_application_id' => $ha->id,
                    'to_stage' => 'hired',
                    'created_at' => $ha->updated_at ?: $ha->created_at,
                ]);
            }
        }

        // Preload jobs for posting date
        $appsWithJob = JobApplication::with(['job:id,created_at,department_id,job_title,company_id'])
            ->whereIn('id', $hireLogs->pluck('job_application_id')->unique())->get()->keyBy('id');

        $tthByMonth = []; $tthCounts = [];
        foreach ($hireLogs as $hl) {
            $job = $appsWithJob[$hl->job_application_id]->job ?? null;
            if (!$job || !$job->created_at || !$hl->created_at) continue;
            $posted = Carbon::parse($job->created_at);
            $hiredAt = Carbon::parse($hl->created_at);
            if ($hiredAt->lt($posted)) continue;
            $days = $posted->diffInDays($hiredAt);
            $month = $hiredAt->format('Y-m');
            $tthByMonth[$month] = ($tthByMonth[$month] ?? 0) + $days;
            $tthCounts[$month] = ($tthCounts[$month] ?? 0) + 1;
        }
        ksort($tthByMonth);
        $tthLabels = array_keys($tthByMonth);
        $tthData = array_map(fn($m)=> round(($tthByMonth[$m] ?? 0) / max(1, $tthCounts[$m] ?? 1), 2), $tthLabels);
        $tthLine = [
            'labels' => $tthLabels,
            'series' => [[ 'name'=>'Avg TTH (days)', 'type'=>'line', 'smooth'=>true, 'data'=>$tthData ]]
        ];

        // 2) Stage Conversion Funnel
        $events = $filteredLogs->map(fn($r)=>['app'=>$r->job_application_id, 'stage'=>$canon($r->to_stage)]);
        $appsByStage = [];
        foreach ($events as $r) {
            $appsByStage[$r['stage']] = $appsByStage[$r['stage']] ?? [];
            $appsByStage[$r['stage']][$r['app']] = true; // unique by app
        }
        // Ensure "Applied" has all apps even if missing logs (fallback)
        if (!isset($appsByStage['applied'])) {
            $appsByStage['applied'] = [];
        }
        foreach ($filteredLogs->pluck('job_application_id')->unique() as $aid) {
            $appsByStage['applied'][$aid] = true;
        }

        $funnelData = [];
        foreach ($canonOrder as $c) {
            $funnelData[] = ['name'=>\Str::title($c), 'value'=> isset($appsByStage[$c]) ? count($appsByStage[$c]) : 0];
        }

        // 3) Offer Acceptance Pie
        $offersSet = collect(array_keys($appsByStage['offer'] ?? []));
        $hiredSet  = collect(array_keys($appsByStage['hired'] ?? []));
        $accepted = $offersSet->intersect($hiredSet)->count();
        $totalOffers = $offersSet->count();
        $offerPie = [
            ['name'=>'Accepted','value'=>$accepted],
            ['name'=>'Declined/No Response','value'=>max(0, $totalOffers - $accepted)],
        ];

        // 4) Avg time in each stage by department (stacked horizontal)
        // Build first entry time per canonical stage per app
        $entryTimesByApp = []; // appId => [canonStage => Carbon]
        foreach ($filteredLogs as $ev) {
            $aid = $ev->job_application_id;
            $cst = $canon($ev->to_stage);
            if (!in_array($cst, $canonOrder, true)) continue;
            $entryTimesByApp[$aid] = $entryTimesByApp[$aid] ?? [];
            if (!isset($entryTimesByApp[$aid][$cst])) {
                $entryTimesByApp[$aid][$cst] = Carbon::parse($ev->created_at);
            }
        }
        // App/job dept index
        $appsIdx = JobApplication::with(['job.department'])
            ->whereIn('id', array_keys($entryTimesByApp))->get()->keyBy('id');

        $sumDur = []; $cntDur = [];
        foreach ($entryTimesByApp as $aid => $entries) {
            $app = $appsIdx[$aid] ?? null;
            $deptName = $app?->job?->department?->department_name ?: 'Unassigned';
            for ($i=0; $i < count($canonOrder)-1; $i++) {
                $from = $canonOrder[$i]; $to = $canonOrder[$i+1];
                if (!isset($entries[$from]) || !isset($entries[$to])) continue;
                $days = max(0, $entries[$from]->diffInDays($entries[$to]));
                $sumDur[$deptName][$from] = ($sumDur[$deptName][$from] ?? 0) + $days;
                $cntDur[$deptName][$from] = ($cntDur[$deptName][$from] ?? 0) + 1;
            }
        }
        $deptTotals = [];
        foreach ($sumDur as $deptName=>$byStage) { $deptTotals[$deptName] = array_sum($byStage); }
        arsort($deptTotals);
        $topDepts = array_slice(array_keys($deptTotals), 0, 10);
        $seriesByStage = [];
        foreach ($canonOrder as $st) {
            if ($st === 'hired') continue;
            $seriesByStage[$st] = [];
            foreach ($topDepts as $dName) {
                $sum = $sumDur[$dName][$st] ?? 0; $cnt = $cntDur[$dName][$st] ?? 0;
                $seriesByStage[$st][] = $cnt ? round($sum/$cnt,2) : 0;
            }
        }
        $stageTimeByDept = [
            'categories' => $topDepts,
            'series' => [
                ['name'=>'Applied','type'=>'bar','stack'=>'time','data'=>$seriesByStage['applied'] ?? []],
                ['name'=>'Screening','type'=>'bar','stack'=>'time','data'=>$seriesByStage['screening'] ?? []],
                ['name'=>'Assessment','type'=>'bar','stack'=>'time','data'=>$seriesByStage['assessment'] ?? []],
                ['name'=>'Interview','type'=>'bar','stack'=>'time','data'=>$seriesByStage['interview'] ?? []],
                ['name'=>'Offer','type'=>'bar','stack'=>'time','data'=>$seriesByStage['offer'] ?? []],
            ],
        ];

        // Filter dropdown sources
        $departments = \App\Models\Department::whereHas('jobs',fn($q)=>$q->where('company_id',$companyId))
            ->pluck('department_name')->unique()->values();
        $jobs = Job::where('company_id',$companyId)->select('id','job_title')->orderBy('job_title')->get();
        $programs = Program::select('id','name')->orderBy('name')->get();
        $stages = collect($canonOrder)->map(fn($s)=>['value'=>$s,'label'=>\Str::title($s)])->values();

        return Inertia::render('Company/Reports/RecruitmentEfficiency', [
            'tthLine' => $tthLine,
            'stageFunnel' => $funnelData,
            'offerPie' => $offerPie,
            'stageTimeByDept' => $stageTimeByDept,
            'filters' => [
                'department'=>$dept,
                'job_id'=>$jobId,
                'program_id'=>$programId,
                'stage'=>$stageSel,
                'date_preset'=>$datePreset,
                'date_from'=>$request->query('date_from'),
                'date_to'=>$request->query('date_to'),
            ],
            'departments' => $departments,
            'jobs' => $jobs,
            'programs' => $programs,
            'stages' => $stages,
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
