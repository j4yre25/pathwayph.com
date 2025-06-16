<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\Location;
use App\Models\Program;
use App\Models\Salary;
use App\Models\Sector;
use App\Models\User;
use App\Models\Skill;
use App\Models\Graduate;
use App\Models\JobInvitation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\JobInviteNotification;
use App\Notifications\NewJobPostingNotification;
use Illuminate\Support\Facades\DB;

class PesoJobsController extends Controller
{
    public function index(User $user)
    {
        $jobs = Job::with('user', 'sector', 'category', 'company', 'peso',)
            ->orderBy('created_at', 'desc') // Sort by created_at in descending order
            ->paginate(10); // Paginate the results

        $sectors = Sector::all();
        $categories = Category::all();

        return Inertia::render('Admin/Jobs/Index/Index', [
            'jobs' => $jobs,
            'sectors' => $sectors,
            'categories' => $categories,
        ]);
    }

public function peso(User $user)
{
    $peso = $user->peso;
    $jobs = Job::with('user', 'sector', 'category', 'company', 'peso')
        ->where('peso_id', $peso ? $peso->id : 0)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // Transform jobs for frontend


    $sectors = Sector::all();
    $categories = Category::all();

    return Inertia::render('Admin/Jobs/Index/PesoJobs', [
        'jobs' => $jobs,
        'sectors' => $sectors,
        'categories' => $categories,
    ]);
}



    public function create(User $user)
    {

        $sectors = Sector::with('categories')->get();
        $programs = Program::select('id', 'name')->get();
        $skills = Skill::orderBy('name')->pluck('name');

        $user->load('peso');

        return Inertia::render('Admin/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
            'programs' => $programs,
            'user' => $user,
            'skills' => $skills,

        ]);
    }

    public function archivedlist(User $user)
    {
        $all_jobs = Job::onlyTrashed()
            ->with('user', 'sector', 'category', 'company', 'peso',)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Jobs/Index/ArchivedList', [
            'all_jobs' => $all_jobs,
        ]);
    }

    public function manage(User $user)
    {
        $jobs = Job::with('user', 'company', 'sector', 'category', 'peso')
            ->orderBy('created_at', 'desc') // Sort by created_at in descending order
            ->paginate(10); // Paginate the results
        return Inertia::render('Admin/Jobs/Index/ManageJobs', [
            'jobs' => $jobs
        ]);
    }



    public function store(Request $request, User $user)
    {
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
            'job_application_limit' => 'nullable|integer|min:1',
            'skills' => 'required|array|min:1',
            'sector' => 'required|exists:sectors,id',
            'category' => 'required|exists:categories,id',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
        ]);

        $salary = $this->createSalary($validated);
        $location = $this->handleLocation($validated);
        $user->loadMissing('hr');
        $jobData = $this->prepareJobData($validated, $user, $salary, $location);

        [$jobCode, $jobID] = $this->generateJobCodes($validated['sector'], $validated['category'], $validated['job_title']);
        $jobData['job_code'] = $jobCode;
        $jobData['job_id'] = "JS-{$jobID}-{$jobCode}";

        $new_job = Job::create($jobData);
        $new_job->jobTypes()->sync([$validated['job_type']]);
        if ($location) {
            $new_job->locations()->sync([$location->id]);
        }
        $new_job->workEnvironments()->sync([$validated['work_environment']]);
        $new_job->programs()->attach($validated['program_id']);

        // Notify graduates of the new job posting
        $this->notifyGraduates($new_job);

        // Auto-invite qualified graduates based on skills/program
        $this->autoInvite($new_job);

        return redirect()->route('peso.pesojobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
    }

    private function createSalary(array $validated): Salary
    {
        return Salary::create([
            'job_min_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_min_salary'],
            'job_max_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_max_salary'],
            'salary_type' => $validated['salary']['salary_type'],
        ]);
    }

    private function handleLocation(array $validated): ?Location
    {
        if ($validated['work_environment'] != 2) {
            return Location::firstOrCreate(['address' => $validated['location']]);
        }
        return null;
    }

    private function prepareJobData(array $validated, User $user, Salary $salary, $location): array
    {
        return [
            'user_id' => $user->id,
            'posted_by' => $user->peso->peso_first_name . ' ' . $user->peso->peso_last_name,
            'peso_id' => $user->peso->peso_id,
            'status' => 'pending',
            'job_title' => $validated['job_title'],
            'location' => $location ? $location->id : null,
            'salary_id' => $salary->id,
            'is_approved' => null,
            'job_type' => $validated['job_type'],
            'job_experience_level' => $validated['job_experience_level'],
            'work_environment' => $validated['work_environment'],
            'skills' => json_encode($validated['skills']),
            'job_vacancies' => $validated['job_vacancies'],
            'job_description' => $validated['job_description'],
            'job_requirements' => $validated['job_requirements'],
            'sector_id' => $validated['sector'],
            'category_id' => $validated['category'],
            'job_deadline' => \Carbon\Carbon::parse($validated['job_deadline'])->format('Y-m-d'),
            'job_application_limit' => $validated['job_application_limit'] ?? null,
            'is_negotiable' => $validated['is_negotiable'],
            'job_salary_type' => $validated['salary']['salary_type'],
            'job_min_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_min_salary'],
            'job_max_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_max_salary'],
        ];
    }

    private function generateJobCodes($sectorId, $categoryId, $jobTitle): array
    {
        $sector = Sector::find($sectorId);
        $category = \App\Models\Category::find($categoryId);
        $sectorCode = $sector->sector_id;
        $divisionCodes = $sector->division_codes;
        $categoryCode = $category->division_code;
        $initials = collect(explode(' ', $jobTitle))
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
        $initials = strtoupper($initials);
        $jobCode = "{$sectorCode}{$divisionCodes}{$initials}-{$categoryCode}";
        $jobCount = Job::count() + 1;
        $jobID = str_pad($jobCount, 3, '0', STR_PAD_LEFT);
        return [$jobCode, $jobID];
    }

    public function view(Job $job)
    {
        $job->load('company', 'category', 'user');

        // Combine job_min_salary and job_max_salary into a salary range string
        $salaryRange = $job->job_min_salary && $job->job_max_salary
            ? "₱" . $job->job_min_salary . ' - ' . $job->job_max_salary
            : "Negotiable";

        // PESO details
        $peso = $job->user->peso ?? null;
        $pesoFirstName = $peso->peso_first_name ?? '';
        $pesoLastName = $peso->peso_last_name ?? '';
        $pesoFullName = trim($pesoFirstName . ' ' . $pesoLastName);

        return Inertia::render('Company/Jobs/View/EmployersJobDetails', [
            'job' => [
                'id' => $job->id,
                'job_title' => $job->job_title,
                'location' => $job->location,
                'job_type' => $job->job_type,
                'job_experience_level' => $job->job_experience_level,
                'job_description' => $job->job_description,
                'job_requirements' => $job->job_requirements,
                'job_vacancies' => $job->job_vacancies,
                'related_skills' => is_array($job->related_skills) ? $job->related_skills : json_decode($job->related_skills, true),
                'is_approved' => $job->is_approved,
                'posted_at' => $job->created_at->format('F j, Y'),
                'posted_by' => $pesoFullName ?: $job->user->name,
                'job_deadline' => \Carbon\Carbon::parse($job->job_deadline)->format('F j, Y'),
                'user_role' => $job->user->role ?? null,
                'category' => $job->category->name ?? null,
                'salary_range' => $salaryRange,
                'company' => $job->company ? [
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
                ] : null,
                // Optionally, add a peso field for frontend clarity
                'peso' => $peso ? [
                    'first_name' => $pesoFirstName,
                    'last_name' => $pesoLastName,
                    'full_name' => $pesoFullName,
                    'email' => $peso->peso_email ?? null,
                    'contact_number' => $peso->peso_contact_number ?? null,
                ] : null,
            ],
            'user' => Auth::user(),
        ]);
    }

    public function edit(Job $job)
    {
        return Inertia::render('Admin/Jobs/Edit/Index', [
            'job' => $job
        ]);
    }

    public function update(Request $request, Job $job)
    {
        Gate::authorize('update', $job);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:99'],
            'description' => ['required', 'string', 'max:99'],


        ]);

        $job->name = $request->name;
        $job->description = $request->description;
        $job->save();

        return Redirect()->back()->with('flash.banner', 'Job updated successfully.');
    }

    public function approve(Job $job)
    {
        $job->is_approved = 1;
        $job->status = 'open';
        $job->save();

        return redirect()->route('peso.jobs.manage', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');
    }

    public function disapprove(Job $job)
    {
        $job->is_approved = 0;
        $job->status = 'closed';
        $job->save();

        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job disapproved successfully.');
    }


    public function delete(Request $request, Job $job)
    {

        // Gate::authorize('delete', $job);

        $job->delete();

        // $user_id = $request->user()->id;

        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job Archived successfully.');
    }

    public function restore($job)
    {
        $job = Job::withTrashed()->findOrFail($job);

        $job->restore();

        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job restored successfully.');
    }


    protected function notifyGraduates(Job $new_job)
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

    public function autoInvite(Job $job)
    {
        $jobSkills = collect($job->skills)
            ->map(fn($skill) => Str::lower($skill))
            ->filter()
            ->unique();
        $jobProgramIds = $job->programs->pluck('id');

        // Match graduates who have matching skills AND are from related programs
        $qualifiedGraduates = Graduate::whereHas('user', function ($query) {
            $query->where('role', 'graduate');
        })
            ->whereIn('program_id', $jobProgramIds)
            ->whereHas('graduateSkills.skill', function ($query) use ($jobSkills) {
                $query->whereIn(DB::raw('LOWER(name)'), $jobSkills);
            })
            ->get();

        // Get the PESO's ID for company_id
        $peso = Auth::user()->peso;
        $companyId = $peso ? $peso->peso_id : null;

        foreach ($qualifiedGraduates as $graduate) {
            JobInvitation::updateOrCreate([
                'job_id' => $job->id,
                'graduate_id' => $graduate->id,
            ], [
                'company_id' => $companyId,
                'status' => 'pending',
                'title' => 'You have been invited to apply to this job opportunity.',
            ]);

            if ($graduate->user) {
                $graduate->user->notify(new JobInviteNotification($job));
            }
        }

        return back()->with('flash.banner', $qualifiedGraduates->count() . ' graduates invited based on skills and program.');
    }
}
