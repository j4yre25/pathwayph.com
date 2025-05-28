<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Graduate;
use App\Models\Sector;
use App\Models\Program;
use App\Models\JobInvitation;
use App\Models\Location;
use App\Models\Salary;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Notifications\NewJobPostingNotification;


class CompanyJobsController extends Controller
{
    public function index(User $user)
    {

         $jobs = Job::with(['jobTypes', 'locations', 'workEnvironments'])
        ->where('company_id', $user->hr->company_id)
        ->get();

        // dd($jobs->toArray()) ; // Debugging line to check the jobs data
        $sectors = Sector::pluck('name');
        $categories = \App\Models\Category::pluck('name');

        return Inertia::render('Company/Jobs/Index/Index', [
            'jobs' => $jobs,
            'sectors' => $sectors,
            'categories' => $categories,
        ]);
    }



    public function create(User $user)
    {
        $authUser = Auth::user()->load('hr');

        $sectors = Sector::with('categories')->get();
        $programs = Program::select('id', 'name')->get();
        $skills = Skill::orderBy('name')->pluck('name');



        return Inertia::render('Company/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
            'programs' => $programs,
            'authUser' => $authUser,
            'skills' => $skills,
        ]);
    }

    public function archivedlist(User $user)
    {


        $all_jobs = Job::with('user')->onlyTrashed()->get();

        return Inertia::render('Company/Jobs/Index/ArchivedList', [
            'all_jobs' => $all_jobs


        ]);
    }

    public function manage(User $user)
    {
        $jobs = $user->jobs()
        ->with(['jobTypes', 'locations', 'workEnvironments'])
        ->get();

        return Inertia::render('Company/Jobs/Index/ManageJobs', [
            'jobs' => $jobs,
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

        // Handle salary creation
        $salary = Salary::create([
            'job_min_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_min_salary'],
            'job_max_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_max_salary'],
            'salary_type' => $validated['salary']['salary_type'],
        ]);

        // Handle location only if NOT remote
        $location = null;
        if ($validated['work_environment'] != 2) {
            $location = Location::firstOrCreate(['address' => $validated['location']]);
        }

        $user->loadMissing('hr');

        // Prepare job data
        $jobData = [
            'user_id' => $user->id,
            'posted_by' => $user->hr->full_name,
            'company_id' => $user->hr->company_id,
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
            'job_deadline' => Carbon::parse($validated['job_deadline'])->format('Y-m-d'),
            'job_application_limit' => $validated['job_application_limit'] ?? null,
            'is_negotiable' => $validated['is_negotiable'],
            'job_salary_type' => $validated['salary']['salary_type'],
            'job_min_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_min_salary'],
            'job_max_salary' => $validated['is_negotiable'] ? null : $validated['salary']['job_max_salary'],
        ];

        // Generate job code and ID
        $sector = Sector::find($validated['sector']);
        $category = \App\Models\Category::find($validated['category']);
        $sectorCode = $sector->sector_id;
        $divisionCodes = $sector->division_codes;
        $categoryCode = $category->division_code;
        $initials = collect(explode(' ', $validated['job_title']))
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
        $initials = strtoupper($initials);
        $jobCode = "{$sectorCode}{$divisionCodes}{$initials}-{$categoryCode}";
        $jobCount = Job::count() + 1;
        $jobID = str_pad($jobCount, 3, '0', STR_PAD_LEFT);
        $jobData['job_code'] = $jobCode;
        $jobData['job_id'] = "JS-{$jobID}-{$jobCode}";

        $new_job = Job::create($jobData);
        $new_job->jobTypes()->sync([$validated['job_type']]);
        if ($location) {
            $new_job->locations()->sync([$location->id]);
        }
        $new_job->workEnvironments()->sync([$validated['work_environment']]);
        $new_job->programs()->attach($validated['program_id']);


        // Notify graduates about the new job posting
        $this->notifyGraduates($new_job);

        return redirect()->route('company.jobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
    }

    public function view(Job $job)
    {
        $job->load('company', 'category', 'user');

        // Combine job_min_salary and job_max_salary into a salary range string
        $salaryRange = $job->job_min_salary && $job->job_max_salary
            ? "₱" . $job->job_min_salary . ' - ' . $job->job_max_salary
            : "Negotiable";

        $hrFirstName = $job->user->company_hr_first_name ?? '';
        $hrLastName = $job->user->company_hr_last_name ?? '';


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
                'posted_by' => $job->user->name,
                'job_deadline' => Carbon::parse($job->job_deadline)->format('F j, Y'),
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
            ],
            'user' => Auth::user(),
        ]);
    }

    public function edit(Job $job)
    {
        return Inertia::render('Company/Jobs/Edit/Index', [
            'job' => $job
        ]);
    }

    public function update(Request $request, Job $job)
    {

        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:99'],
            'job_description' => ['required', 'string', 'max:1000'],
            'job_requirements' => ['required', 'string', 'max:1000'],
            'related_skills' => ['nullable', 'array'], // Optional: if you want to update related_skills too
            'related_skills.*' => ['string', 'max:255'], // Validate each skill
        ]);

        $job->update([
            'job_title' => $validated['job_title'],
            'job_description' => $validated['job_description'],
            'job_requirements' => $validated['job_requirements'],
            'related_skills' => $validated['related_skills'] ?? $job->related_skills, // Keep existing if not updating
        ]);
        $job->save();


        return redirect()->back()->with('flash.banner', 'Job updated successfully.');
    }


    public function approve(Job $job)
    {
        $job->is_approved = 1;
        $job->status = 'open'; // Set status to approved
        $job->save();

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');
    }

    public function disapprove(Job $job)
    {
        $job->is_approved = 0;
        $job->status = 'closed'; // Set status to disapproved
        $job->save();

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job disapproved successfully.');
    }


    public function delete(Request $request, Job $job)
    {

        // Gate::authorize('delete', $job);

        $job->delete();

        // $user_id = $request->user()->id;

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job Archived successfully.');
    }


    public function autoInvite(Job $job)
    {
        $jobSkills = collect($job->skill)
            ->map(fn($skill) => Str::lower($skill))
            ->filter()
            ->unique();

        // Fetch all graduates (Users with role 'graduate') who have matching related_skills
        $qualifiedGraduates = User::where('role', 'graduate')
            ->whereHas('related_skills', function ($query) use ($jobSkills) {
                $query->whereIn(DB::raw('LOWER(graduate_skills_name)'), $jobSkills);
            })
            ->get();

        foreach ($qualifiedGraduates as $graduate) {
            JobInvitation::updateOrCreate([
                'job_id' => $job->id,
                'graduate_id' => $graduate->id,
            ], [
                'company_id' => Auth::id(),
                'status' => 'pending',
                'message' => 'You have been invited to apply to this job opportunity.',
            ]);
        }

        return back()->with('flash.banner', $qualifiedGraduates->count() . ' graduates invited based on skill alignment.');
    }


    public function restore($job)
    {
        $job = Job::withTrashed()->findOrFail($job);

        $job->restore();

        return redirect()->back()->with('flash.banner', 'Job restored successfully.');
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
}
