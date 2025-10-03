<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobInvitation;
use App\Models\JobType;
use App\Models\Location;
use App\Models\Program;
use App\Models\Salary;
use App\Models\Sector;
use App\Models\Skill;
use App\Models\User;
use App\Models\WorkEnvironment;
use App\Notifications\JobInviteNotification;
use App\Notifications\NewJobPostingNotification;
use App\Notifications\NewCompanyJobPostedNotification;
use App\Services\JobCreationService;
use App\Services\ApplicantScreeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class CompanyJobsController extends Controller
{
    public function index(User $user)
    {
        $jobs = Job::with(['salary', 'locations', 'workEnvironments', 'jobTypes'])
            ->where('company_id', $user->hr->company_id)
            ->orderByRaw('is_approved DESC')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get overall counts (not paginated)
        $allJobs = Job::where('company_id', $user->hr->company_id);
        $totalJobs = $allJobs->count();
        $openJobs = $allJobs->where('status', 'open')->count();
        $closedJobs = $allJobs->where('status', 'closed')->count();
        $expiredJobs = $allJobs->where('status', 'expired')->count();

        return Inertia::render('Company/Jobs/Index/Index', [
            'jobs' => $jobs,
            'totalJobs' => $totalJobs,
            'openJobs' => $openJobs,
            'closedJobs' => $closedJobs,
            'expiredJobs' => $expiredJobs,
        ]);
    }

    protected function abortIfOnHold($user = null)
    {
        $user = $user ?: auth()->user();
        if ($user->status === 'on_hold') {
            abort(403, 'Your company account is currently on hold. Please contact PESO for assistance.');
        }
    }



    public function create(User $user)
    {
        $authUser = Auth::user()->load('hr');

        $sectors = Sector::with('categories')->get();
        $programs = Program::select('id', 'name')->get();
        $skills = Skill::orderBy('name')->pluck('name');

        $departments = [];
        if ($authUser->hr && $authUser->hr->company_id) {
            $departments = Department::where('company_id', $authUser->hr->company_id)
                ->select('id', 'department_name')
                ->orderBy('department_name')
                ->get();
        }


        return Inertia::render('Company/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
            'programs' => $programs,
            'authUser' => $authUser,
            'skills' => $skills,
            'departments' => $departments,
        ]);
    }

    public function archivedlist(User $user)
    {
        $all_jobs = Job::with('user', 'jobTypes')->onlyTrashed()->get();

        return Inertia::render('Company/Jobs/Index/ArchivedList', [
            'all_jobs' => $all_jobs
        ]);
    }

    public function manage(User $user)
    {
        $jobs = Job::with([
            'jobTypes:id,type',
            'locations:id,address',
            'workEnvironments:id,environment_type',
            'salary'
        ])
            ->where('company_id', $user->hr->company_id)
            ->orderByRaw('is_approved DESC') // prioritize approved jobs
            ->orderBy('created_at', 'desc')            // then by latest
            ->get();

        return Inertia::render('Company/Jobs/Index/ManageJobs', [
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $this->abortIfOnHold($user);

        $validated = $request->validate([
            'job_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobs')->where(function ($query) use ($user, $request) {
                    return $query->where('user_id', $user->id)
                        ->where('location', $request->location);
                }),
            ],
            'posted_by' => 'nullable|string|max:255',
            'location' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->work_environment != 2; // Only required if NOT remote
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'department_id' => [
                Rule::requiredIf(function () use ($request) {
                    // Only required if there are departments for this company
                    $departments = Department::where('company_id', $request->user()->hr->company_id)->count();
                    return $departments > 0;
                }),
                'nullable',
                'integer',
                'exists:departments,id'
            ],
            'job_vacancies' => 'required|integer|min:1',
            'salary.job_min_salary' => 'nullable|numeric',
            'salary.job_max_salary' => 'nullable|numeric',
            'salary.salary_type' => 'required|string',
            'is_negotiable' => 'nullable|boolean',
            'job_type' => 'required|integer|exists:job_types,id',
            'job_experience_level' => 'required|string|max:255',
            'work_environment' => 'required|integer|exists:work_environments,id',
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'required|string|max:5000',
            'job_deadline' => 'required|date|after:today',
            'job_application_limit' => [
                'nullable',
                'integer',
                'min:' . ($request->job_vacancies ?? 1),
            ],
            'skills' => 'required|array|min:1',
            // 'sector' => 'required|exists:sectors,id',
            // 'category' => 'required|exists:categories,id',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
            'sector_'
        ]);

        $user->loadMissing('hr');
        $jobService = new JobCreationService();
        $new_job = $jobService->createJob($validated, $user);

        $new_job->is_approved = null; // Pending admin approval
        $new_job->status = 'pending';
        $new_job->save();

        $invitedCount = $this->autoScreenAndInvite($new_job, 30);



        $pesoUsers = User::where('role', 'peso')->get();
        foreach ($pesoUsers as $pesoUser) {
            $pesoUser->notify(new NewCompanyJobPostedNotification($new_job));
        }
        // Notify graduates of the new job posting
        $this->notifyGraduates($new_job);

        return redirect()
           ->route('company.jobs.create', ['user' => $user->id])
            ->with('flash.bannerStyle', 'success')
            ->with('flash.banner', "Job submitted for PESO approval. {$invitedCount} graduate" . ($invitedCount === 1 ? '' : 's') . " auto-invited.")
            ->with('flash.job_invited_count', $invitedCount)
            ->with('flash.show_job_modal', true);
    }

    public function batchPage()
    {
        return Inertia::render('Company/Jobs/Index/BatchUploadPreview');
    }
    public function fuzzyFind($model, $column, $value)
    {
        if (!$value) return null;
        // Try exact match (case-insensitive)
        $record = $model::whereRaw('LOWER(' . $column . ') = ?', [strtolower(trim($value))])->first();
        if ($record) return $record;
        // Try partial match
        $match = $model::where($column, 'LIKE', '%' . trim($value) . '%')->first();
        if ($match) return $match;
        // Try removing spaces and compare
        $match = $model::whereRaw("REPLACE(LOWER($column), ' ', '') = ?", [str_replace(' ', '', strtolower(trim($value)))])->first();
        return $match;
    }

    public function excelSerialToDate($serial)
    {
        // Excel's epoch starts at 1900-01-01
        $unix = ($serial - 25569) * 86400;
        return gmdate('Y-m-d', $unix);
    }

    // 2. Batch Upload Handler
    public function batchUpload(Request $request)
    {
        $this->abortIfOnHold(auth()->user());

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        // Resolve company once (for departments + sector/category)
        $user = auth()->user();
        $user->loadMissing('hr.company');
        $company = $user->hr?->company;
        $companyId = $company?->id;

        $extension = $request->file('csv_file')->getClientOriginalExtension();

        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = Excel::toArray(new \stdClass(), $request->file('csv_file'))[0];
            $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
            $dataRows = array_slice($rows, 1);
        } else {
            $file = $request->file('csv_file');
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle, 0, ',');
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            $dataRows = [];
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $dataRows[] = $data;
            }
            fclose($handle);
        }

        $errors = [];
        $validRows = [];
        $rowNum = 2;

        // Does company already have departments? (affects required rule)
        $hasExistingDepartments = $companyId
            ? Department::where('company_id', $companyId)->exists()
            : false;

        foreach ($dataRows as $data) {
            $row = array_combine($header, $data);

            if (!$row || !is_array($row) || empty(array_filter($row))) {
                $rowNum++;
                continue;
            }

            // Inject sector & category from company BEFORE validation
            $row['sector_id']   = $company?->sector_id;
            $row['category_id'] = $company?->category_id;

            // --- Department (auto-create if missing) ---
            $row['department_id'] = null;
            if (!empty($row['department_name'])) {
                $deptName = trim($row['department_name']);
                if ($deptName !== '' && $companyId) {
                    // Try exact-case-insensitive match
                    $dept = Department::where('company_id', $companyId)
                        ->whereRaw('LOWER(department_name) = ?', [mb_strtolower($deptName)])
                        ->first();

                    if (!$dept) {
                        // Create if not exists
                        $dept = Department::create([
                            'company_id' => $companyId,
                            'department_name' => $deptName,
                        ]);
                        \Log::info('BatchUpload: Department created', [
                            'company_id' => $companyId,
                            'department_id' => $dept->id,
                            'name' => $deptName,
                        ]);
                    }
                    $row['department_id'] = $dept->id;
                }
            }

            // --- Program ---
            $row['program_id'] = [];
            if (!empty($row['program_name'])) {
                $programNames = array_map('trim', explode(',', $row['program_name']));
                foreach ($programNames as $progName) {
                    $program = $this->fuzzyFind(Program::class, 'name', $progName);
                    \Log::info('BatchUpload: Program Lookup', [
                        'input' => $progName,
                        'found' => $program ? $program->name : null
                    ]);
                    if ($program) {
                        $row['program_id'][] = $program->id;
                    }
                }
            }
            // If no valid programs found, set to null for validation to catch
            if (empty($row['program_id'])) {
                $row['program_id'] = null;
            }

            // --- Work Environment ---
            $row['work_environment'] = null;
            if (!empty($row['work_environment_type'])) {
                $workEnv = $this->fuzzyFind(WorkEnvironment::class, 'environment_type', $row['work_environment_type']);
                $row['work_environment'] = $workEnv ? $workEnv->id : null;
            }

            // --- Job Types (comma-separated) ---
            $jobTypeIds = [];
            if (!empty($row['job_types'])) {
                $typeNames = array_map('trim', explode(',', $row['job_types']));
                foreach ($typeNames as $typeName) {
                    $jobType = $this->fuzzyFind(JobType::class, 'type', $typeName);
                    if ($jobType) $jobTypeIds[] = $jobType->id;
                }
            }
            $row['job_type_ids'] = $jobTypeIds;
            $row['job_type'] = !empty($jobTypeIds) ? $jobTypeIds[0] : null;

            // --- Experience Level normalize ---
            $allowed = ['Entry-level', 'Intermediate', 'Mid-level', 'Senior/Executive'];
            $input = strtolower(trim($row['job_experience_level'] ?? ''));
            $matched = collect($allowed)->first(function ($level) use ($input) {
                $levelLower = strtolower($level);
                return $levelLower === $input
                    || str_contains($levelLower, $input)
                    || str_contains($input, $levelLower)
                    || preg_replace('/[^a-z]/', '', $levelLower) === preg_replace('/[^a-z]/', '', $input);
            });
            $row['job_experience_level'] = $matched ?? $allowed[0];

            // --- Skills ---
            if (!empty($row['skills'])) {
                $row['skills'] = array_map('trim', explode(',', $row['skills']));
            }

            // --- job_application_limit (only optional column) ---
            $row['job_application_limit'] = isset($row['job_application_limit']) && $row['job_application_limit'] !== '' ? (int)$row['job_application_limit'] : null;

            // --- Salary fields + negotiable rule ---
            $row['job_min_salary'] = isset($row['job_min_salary']) && $row['job_min_salary'] !== '' ? $row['job_min_salary'] : null;
            $row['job_max_salary'] = isset($row['job_max_salary']) && $row['job_max_salary'] !== '' ? $row['job_max_salary'] : null;

            // --- Location ---
            $row['location'] = $row['location'] ?? null;

            // --- Deadline (supports Excel serial) ---
            if (isset($row['job_deadline']) && trim($row['job_deadline']) !== '') {
                if (is_numeric($row['job_deadline'])) {
                    $row['job_deadline'] = $this->excelSerialToDate($row['job_deadline']);
                } else {
                    $date = str_replace(['/', '.'], '-', $row['job_deadline']);
                    try {
                        $carbon = Carbon::createFromFormat('Y-m-d', $date);
                    } catch (\Exception $e) {
                        $carbon = null;
                    }
                    $row['job_deadline'] = $carbon ? $carbon->format('Y-m-d') : null;
                }
            } else {
                $row['job_deadline'] = null;
            }

            // --- Negotiable ---
            if (isset($row['is_negotiable']) && strtolower(trim($row['is_negotiable'])) === 'yes') {
                $row['is_negotiable'] = 1;
                // enforce no min/max when negotiable
                $row['job_min_salary'] = null;
                $row['job_max_salary'] = null;
            } else {
                $row['is_negotiable'] = 0;
            }

            // --- Status ---
            $row['status'] = $row['status'] ?? 'pending';

            // --- Salary payload for service ---
            $row['salary'] = [
                'job_min_salary' => $row['job_min_salary'],
                'job_max_salary' => $row['job_max_salary'],
                'salary_type' => $row['salary_type'] ?? 'Monthly',
            ];

            // --- Validation ---
            $validator = \Validator::make($row, [
                'job_title' => 'required|string|max:255',
                // Department required only if company already maintains departments
                'department_id' => [Rule::requiredIf($hasExistingDepartments), 'nullable', 'exists:departments,id'],
                'job_description' => 'required|string',
                'job_requirements' => 'required|string',
                'job_min_salary' => 'nullable|numeric',
                'job_max_salary' => 'nullable|numeric',
                'location' => 'nullable|string|max:255',
                'work_environment' => 'required|exists:work_environments,id',
                'program_id' => 'required|array|min:1',
                'program_id.*' => 'exists:programs,id',
                'job_experience_level' => 'required|string|max:255',
                'status' => 'nullable|string|max:255',
                'is_approved' => 'nullable|boolean',
                'skills' => 'required|array|min:1',
                'job_deadline' => 'required|date|after:today',
                'job_application_limit' => 'nullable|integer|min:1',
            ]);

            // Extra check: if not negotiable, min/max must be present and valid
            $validator->after(function ($v) use ($row) {
                if (empty($row['is_negotiable'])) {
                    if ($row['job_min_salary'] === null) $v->errors()->add('job_min_salary', 'Min salary is required when not negotiable.');
                    if ($row['job_max_salary'] === null) $v->errors()->add('job_max_salary', 'Max salary is required when not negotiable.');
                    if (
                        $row['job_min_salary'] !== null && $row['job_max_salary'] !== null
                        && (float)$row['job_min_salary'] > (float)$row['job_max_salary']
                    ) {
                        $v->errors()->add('job_min_salary', 'Min salary cannot exceed max salary.');
                    }
                }
            });

            if ($validator->fails()) {
                $errors[] = ['row' => $rowNum, 'messages' => $validator->errors()->all()];
            } else {
                $validRows[] = $row;
            }

            $rowNum++;
        }

        if (count($errors)) {
            return response()->json([
                'status' => 'error',
                'errors' => $errors,
            ], 422);
        }

        $jobService = new JobCreationService();

        $totalInvited = 0;
        $totalJobs = 0;

        foreach ($validRows as $row) {
            // Duplicate check
            $existingJob = Job::where('job_title', $row['job_title'])
                ->where('user_id', $user->id)
                ->where('location', $row['location'])
                ->first();
            if ($existingJob) {
                continue;
            }

            // Ensure the company sector/category always applied
            $row['sector_id']   = $company?->sector_id;
            $row['category_id'] = $company?->category_id;

            $job = $jobService->createJob($row, $user);

            if (!empty($row['job_type_ids'])) {
                $job->jobTypes()->sync($row['job_type_ids']);
            }
            if (!empty($row['program_id'])) {
                $job->programs()->sync($row['program_id']);
            }

            // Open/approve by default
            $job->update(['is_approved' => null, 'status' => 'pending']);

            // Auto-invite + general notification
            $invited = $this->autoScreenAndInvite($job, 30);
            $totalInvited += $invited;
            $this->notifyGraduates($job);

            $pesoUsers = User::where('role', 'peso')->get();
            foreach ($pesoUsers as $pesoUser) {
                $pesoUser->notify(new NewCompanyJobPostedNotification($job));
            }

            $totalJobs++;
        }

        return redirect()
            ->back()
            ->with('flash.bannerStyle', 'success')
            ->with('flash.banner', "Batch submitted for PESO approval. {$totalJobs} job(s) uploaded; {$totalInvited} graduate(s) auto-invited.")
            ->with('flash.batch_total_jobs', $totalJobs)
            ->with('flash.batch_total_invites', $totalInvited)
            ->with('flash.show_job_modal', true);
    }


    // 3. Download CSV Template
    public function downloadTemplate()
    {
        return response()->download(public_path('templates/job_template.xlsx'));
    }

    public function view(Job $job)
    {
        $job->load([
            'company',
            'category',
            'user.hr',
            'jobTypes',
            'workEnvironments',
            'locations',
            'programs'
        ]);

        // Salary range logic as before
        $salaryRange = $job->job_min_salary && $job->job_max_salary
            ? "₱" . $job->job_min_salary . ' - ' . "₱" . $job->job_max_salary
            : "Negotiable";

        return Inertia::render('Company/Jobs/View/EmployersJobDetails', [
            'job' => [
                'id' => $job->id,
                'job_title' => $job->job_title,
                'location' => $job->locations->pluck('address')->toArray(), // array of addresses
                'job_type' => $job->jobTypes->pluck('type')->toArray(), // array of types
                'work_environment' => $job->workEnvironments->pluck('environment_type')->toArray(), // array of env types
                'job_experience_level' => $job->job_experience_level,
                'job_description' => $job->job_description,
                'job_requirements' => $job->job_requirements,
                'job_vacancies' => $job->job_vacancies,
                'skills' => is_array($job->skills) ? $job->skills : json_decode($job->skills, true),
                'is_approved' => $job->is_approved,
                'posted_at' => $job->created_at->format('F j, Y'),
                'posted_by' => $job->posted_by,
                'job_deadline' => $job->job_deadline
                    ? (is_string($job->job_deadline) || is_int($job->job_deadline)
                        ? Carbon::parse($job->job_deadline)->format('F j, Y')
                        : (method_exists($job->job_deadline, 'format')
                            ? $job->job_deadline->format('F j, Y')
                            : null))
                    : null,
                'user_role' => $job->user->role ?? null,
                'category' => $job->category->name ?? null,
                'salary_range' => $salaryRange,
                'company' => [
                    'name' => $job->company->company_name,
                    'email' => $job->company->company_email,
                    'contact_number' => $job->company->company_contact_number,
                    'address' => implode(', ', array_filter([
                        $job->company->company_street_address,
                        $job->company->company_brgy,
                        $job->company->company_city,
                        $job->company->company_province,
                        $job->company->company_zip_code,
                    ])),
                    'profile_photo' => $job->company->profile_photo_path ? Storage::url($job->company->profile_photo_path) : null,
                    'cover_photo' => $job->company->cover_photo_path ? Storage::url($job->company->cover_photo_path) : null,
                ],
                'programs' => $job->programs->pluck('name')->toArray(),
                'status' => $job->status,
            ],
            'user' => Auth::user(),
        ]);
    }

    public function edit(Job $job)
    {
        $job->load([
            'programs:id,name',
            'jobTypes:id,type',
            'workEnvironments:id,environment_type',
            'salary',
        ]);

        $departments = Department::where('company_id', $job->company_id)
            ->select('id', 'department_name')
            ->orderBy('department_name')
            ->get();

        $programs = Program::select('id', 'name')->get();
        $jobTypes = JobType::select('id', 'type')->get();
        $workEnvironments = WorkEnvironment::select('id', 'environment_type')->get();
        $skills = Skill::orderBy('name')->pluck('name');

        // Prepare job data for Vue
        $jobData = $job->toArray();
        $jobData['programs'] = $job->programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name]);
        $jobData['program_id'] = $job->programs->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $jobData['job_type'] = $job->jobTypes->first()?->id ? (string)$job->jobTypes->first()->id : '';
        $jobData['work_environment'] = $job->workEnvironments->first()?->id ? (string)$job->workEnvironments->first()->id : '';
        $jobData['skills'] = is_array($job->skills) ? $job->skills : (json_decode($job->skills, true) ?: []);
        $jobData['salary'] = $job->salary ? [
            'salary_type' => $job->salary->salary_type,
            'job_min_salary' => $job->salary->job_min_salary,
            'job_max_salary' => $job->salary->job_max_salary,
        ] : [
            'salary_type' => $job->salary_type ?? '',
            'job_min_salary' => $job->job_min_salary ?? '',
            'job_max_salary' => $job->job_max_salary ?? '',
        ];

        return Inertia::render('Company/Jobs/Edit/Index', [
            'job' => $jobData,
            'departments' => $departments,
            'programs' => $programs,
            'jobTypes' => $jobTypes,
            'workEnvironments' => $workEnvironments,
            'skills' => $skills,
            'authUser' => Auth::user()->load('hr'),
        ]);
    }

    public function update(Request $request, Job $job)
    {
        $this->abortIfOnHold(auth()->user());

        $validated = $request->validate([
            'job_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobs')->ignore($job->id)->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id)
                        ->where('location', $request->location);
                }),
            ],
            'posted_by' => 'nullable|string|max:255',
            'location' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->work_environment != 2; // Only required if NOT remote
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id'
            ],
            'job_vacancies' => 'required|integer|min:1',
            'salary.job_min_salary' => 'nullable|numeric',
            'salary.job_max_salary' => 'nullable|numeric',
            'salary.salary_type' => 'required|string',
            'is_negotiable' => 'nullable|boolean',
            'job_type' => 'required|integer|exists:job_types,id',
            'job_experience_level' => 'required|string|max:255',
            'work_environment' => 'required|integer|exists:work_environments,id',
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'required|string|max:5000',
            'job_deadline' => 'required|date|after:today',
            'job_application_limit' => [
                'nullable',
                'integer',
                'min:' . ($request->job_vacancies ?? 1),
            ],
            'skills' => 'required|array|min:1',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
        ]);

        // Update main job fields
        $job->update([
            'job_title' => $validated['job_title'],
            'posted_by' => $validated['posted_by'] ?? $job->posted_by,
            'location' => $validated['location'] ?? null,
            'department_id' => $validated['department_id'],
            'job_vacancies' => $validated['job_vacancies'],
            'job_experience_level' => $validated['job_experience_level'],
            'work_environment' => $validated['work_environment'],
            'job_type' => $validated['job_type'],
            'job_description' => $validated['job_description'],
            'job_requirements' => $validated['job_requirements'],
            'job_deadline' => $validated['job_deadline'],
            'job_application_limit' => $validated['job_application_limit'] ?? null,
            'is_negotiable' => $validated['is_negotiable'] ?? 0,
            'skills' => $validated['skills'],
        ]);

        // Update salary (if you have a Salary model/relationship)
        if (isset($validated['salary'])) {
            $job->salary()->updateOrCreate(
                ['job_id' => $job->id],
                [
                    'salary_type' => $validated['salary']['salary_type'],
                    'job_min_salary' => $validated['salary']['job_min_salary'],
                    'job_max_salary' => $validated['salary']['job_max_salary'],
                ]
            );
        }

        // Sync programs (many-to-many)
        if (isset($validated['program_id'])) {
            $job->programs()->sync($validated['program_id']);
        }

        // Sync job types (if you support multiple types)
        if (isset($validated['job_type'])) {
            $job->jobTypes()->sync([$validated['job_type']]);
        }

        // Optionally, sync work environments if you support multiple
        // $job->workEnvironments()->sync([$validated['work_environment']]);

        $job->save();

        return redirect()->back()->with('flash.banner', 'Job updated successfully.');
    }

    public function close(Job $job)
    {
        $job->status = 'closed';
        $job->save();

        return redirect()->back()->with('flash.banner', 'Job has been closed.');
    }
    public function delete(Request $request, Job $job)
    {

        // Gate::authorize('delete', $job);

        $job->delete();

        // $user_id = $request->user()->id;

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job Archived successfully.');
    }

    protected function autoScreenAndInvite(Job $job, int $threshold = 30): int
    {
        $service = new ApplicantScreeningService();

        $job->loadMissing(['company', 'programs', 'jobTypes', 'locations']);
        $companyIdForInvite = $job->company_id ?? $job->company->id ?? null;

        $programIds = $job->programs->pluck('id');
        $programNames = $job->programs->pluck('name')->map(fn($n) => mb_strtolower($n))->filter()->values();

        // Base query (unfiltered)
        $allowedStatuses = ['unemployed', 'underemployed'];

        $baseQuery = Graduate::with([
            'user',
            'graduateSkills.skill',
            'education',
            'experience',
            'employmentPreference',
        ])->where(function ($q) use ($allowedStatuses) {
            $q->whereNull('employment_status')
              ->orWhereIn(DB::raw('LOWER(employment_status)'), $allowedStatuses);
        });

        $usingFiltered = false;
        $filteredQuery = null;

        if ($programNames->isNotEmpty()) {
            $filteredQuery = (clone $baseQuery)->whereHas('education', function ($q) use ($programNames) {
                $q->whereIn(DB::raw('LOWER(program)'), $programNames->toArray());
            });

            // Quick existence check
            $hasAny = (clone $filteredQuery)->limit(1)->count();
            if ($hasAny) {
                $usingFiltered = true;
            }
        }

        $query = $usingFiltered ? $filteredQuery : $baseQuery;

        // Log which path we take
        \Log::info('AutoScreen: graduate query prepared', [
            'job_id' => $job->id,
            'using_filtered' => $usingFiltered,
            'program_names' => $programNames,
            'total_graduates' => (clone $query)->count(),
        ]);

        // Already invited grads
        $alreadyInvited = JobInvitation::where('job_id', $job->id)
            ->pluck('graduate_id')
            ->flip();

        $attempted = 0;
        $matched = 0;
        $newInvites = 0;
        $notifyUsers = [];

        $query->chunk(150, function ($graduates) use (
            $service,
            $job,
            $threshold,
            $companyIdForInvite,
            &$alreadyInvited,
            &$notifyUsers,
            &$newInvites,
            &$attempted,
            &$matched
        ) {
            foreach ($graduates as $graduate) {
                if (!$graduate->user) continue;
                if (isset($alreadyInvited[$graduate->id])) continue;

                if (
                    method_exists($graduate, 'jobApplications') &&
                    $graduate->jobApplications()->where('job_id', $job->id)->exists()
                ) {
                    continue;
                }

                $attempted++;

                $result = $service->screen($graduate, $job);
                $match = (int)($result['match_percentage'] ?? 0);

                if ($match >= $threshold) {
                    $matched++;

                    $invitationMessage = 'You have been invited to apply for "' .
                        $job->job_title . '" at ' . ($job->company->company_name ?? 'the company') . '.';

                    try {
                        $invite = JobInvitation::updateOrCreate(
                            [
                                'job_id' => $job->id,
                                'graduate_id' => $graduate->id,
                            ],
                            [
                                'company_id' => $companyIdForInvite,
                                'status' => 'pending',
                                'message' => $invitationMessage,
                            ]
                        );

                        $alreadyInvited[$graduate->id] = true;
                        $notifyUsers[] = $graduate->user;
                        $newInvites++;

                        \Log::info('AutoScreen: invitation stored', [
                            'job_id' => $job->id,
                            'graduate_id' => $graduate->id,
                            'invite_id' => $invite->id,
                            'match' => $match
                        ]);
                    } catch (\Throwable $e) {
                        \Log::error('AutoScreen: invitation insert failed', [
                            'job_id' => $job->id,
                            'graduate_id' => $graduate->id,
                            'company_id_used' => $companyIdForInvite,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });

    
        \Log::info('AutoScreen summary', [
            'job_id' => $job->id,
            'threshold' => $threshold,
            'attempted' => $attempted,
            'matched' => $matched,
            'new_invites_created' => $newInvites,
            'using_filtered' => $usingFiltered,
            'company_id_used' => $companyIdForInvite
        ]);

        return $newInvites;
    }


    public function restore($job)
    {
        $job = Job::withTrashed()->findOrFail($job);

        $job->restore();

        return redirect()->back()->with('flash.banner', 'Job restored successfully.');
    }

    public function notifyGraduates(Job $new_job)
    {
        // Find graduates whose preferences match the new job
        $graduates = Graduate::whereHas('employmentPreference', function ($q) use ($new_job) {
            $q->where(function ($query) use ($new_job) {
                $query->where('job_type', 'like', "%{$new_job->job_type}%")
                    ->orWhere('location', 'like', "%{$new_job->location}%")
                    ->orWhere('work_environment', 'like', "%{$new_job->work_environment}%");
            });
        })->get();

        foreach ($graduates as $graduate) {
            if ($graduate->user) {
                $graduate->user->notify(new NewJobPostingNotification($new_job));
            }
        }
    }
}
