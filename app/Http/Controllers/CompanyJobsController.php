<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Graduate;
use App\Models\Sector;
use App\Models\Program;
use App\Models\JobInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;


class CompanyJobsController extends Controller
{
    public function index(User $user)
    {

       // Get all jobs belonging to the same company as the user
         $jobs = Job::where('company_id', $user->company_id)
                ->get();
        $sectors = Sector::pluck('name'); // Fetch all sector names
        $categories = \App\Models\Category::pluck('name'); // Fetch all category names

        return Inertia::render('Company/Jobs/Index/Index', [
            'jobs' => $jobs,
            'sectors' => $sectors, // Array of sectors
            'categories' => $categories,
        ]);
    }



    public function create(User $user)
    {
        $authUser = Auth::user()->load('hr');

        $sectors = Sector::with('categories')->get();
        $programs = Program::select('id', 'name')->get();



        return Inertia::render('Company/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
            'programs' => $programs,
            'authUser' => $authUser,
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
        // Fetch jobs posted by the authenticated user
        $jobs = $user->jobs;

        return Inertia::render('Company/Jobs/Index/ManageJobs', [
            'jobs' => $jobs
        ]);
    }



    public function store(Request $request, User $user)
    {
        // return Inertia::render('Jobs/Index/CreateJobs');
        $validated = $request->validate([
            'job_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobs')->where(function ($query) use ($user, $request) {
                    return $query->where('user_id', $user->id)
                        ->where('job_location', $request->job_location);
                }),
            ],
            'posted_by' => 'nullable|string|max:255',
            'job_location' => 'required|string|max:255',
            'job_vacancies' => 'required|integer|min:1', // Added minimum value check for vacancies
            'job_salary_type' => 'nullable|string|max:255', // Added max length to string
            'job_min_salary' => 'nullable|integer|min:5000', // Ensuring a minimum salary of 5,000
            'job_max_salary' => 'nullable|integer|min:5000|gte:job_min_salary', // Added job_min_salary constraint
            'is_negotiable' => 'required|boolean',
            'job_employment_type' => 'required|string|max:255', // Added max length to string
            'job_experience_level' => 'required|string|max:255', 
            'job_work_environment' => 'required|string|max:255', 
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'required|string|max:5000',
            'job_deadline' => 'required|date|after:today', // Ensuring the expiration date is in the future
            'job_application_limit' => 'nullable|integer|min:1', // Added minimum value check for applicants
            'related_skills' => 'required|array|min:1', // Ensuring at least one skill is provided
            'sector' => 'required|exists:sectors,id',
            'category' => 'required|exists:categories,id',
            'program_id' => 'required|array|min:1', // Ensuring at least one program is selected
            'program_id.*' => 'exists:programs,id', // Validating each selected program's ID
        ], [
            'job_title.required' => 'Please provide a job title.',
            'job_location.required' => 'Job job_location is required.',
            'job_min_salary.min' => 'The minimum salary must be at least ₱5000.',
            'job_max_salary.gte' => 'The maximum salary cannot be less than the minimum salary.',
            'job_deadline.after' => 'The application deadline must be a future date.',
            'job_vacancies.required' => 'Please provide the number of vacancies for this job.',
            'related_skills.required' => 'At least one skill is required.',
            'program_id.required' => 'Please select at least one program.',
            'program_id.*.exists' => 'Each selected program must be valid.',
            'job_description.required' => 'Please provide a job job_description.',
            'job_requirements.required' => 'Please provide the job job_requirements.',
        ]);

        $user->loadMissing('hr');

        $new_job = new Job();
        $new_job->user_id = $user->id;
        $new_job->posted_by = $user->hr->full_name;
        $new_job->company_id = $user->hr->company_id; 
        $new_job->status = 'pending'; // Default status
        $new_job->job_title = $validated['job_title'];
        $new_job->job_location = $validated['job_location'];


        // Salary handling
        if ($validated['is_negotiable']) {
            $new_job->job_min_salary = null;
            $new_job->job_max_salary = null;
            $new_job->is_negotiable = true;
            $new_job->job_salary_type = null; // optional, clear salary type if negotiable
        } else {
            $new_job->job_min_salary = $validated['job_min_salary'];
            $new_job->job_max_salary = $validated['job_max_salary'];
            $new_job->is_negotiable = false;
            $new_job->job_salary_type = $validated['job_salary_type']; // set salary type if not negotiable
        }

        $new_job->job_employment_type = $validated['job_employment_type'];
        $new_job->job_experience_level = $validated['job_experience_level'];
        $new_job->job_work_environment = $validated['job_work_environment'];
        $new_job->related_skills = json_encode($validated['related_skills']);
        $new_job->job_vacancies = $validated['job_vacancies'];
        $new_job->job_description = $validated['job_description'];
        $new_job->job_requirements = $validated['job_requirements'];
        $new_job->sector_id = $validated['sector'];
        $new_job->category_id = $validated['category'];
        $new_job->job_deadline = Carbon::parse($validated['job_deadline'])->format('Y-m-d');
        $new_job->job_application_limit = $validated['job_application_limit'] ?? null;
        $new_job->is_approved = 0; 
         $new_job->save();
        $new_job->programs()->attach($validated['program_id']);

        return redirect()->route('company.jobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
    }

    public function view(Job $job)
    {
        $job->load('company', 'category', 'user');

        // Combine job_min_salary and job_max_salary into a salary range string
        $salaryRange = $job->job_min_salary && $job->job_max_salary
            ? "₱". $job->job_min_salary . ' - ' . $job->job_max_salary
            : "Negotiable";

        $hrFirstName = $job->user->company_hr_first_name ?? '';
        $hrLastName = $job->user->company_hr_last_name ?? '';


        return Inertia::render('Company/Jobs/View/EmployersJobDetails', [
            'job' => [
                'id' => $job->id,
                'job_title' => $job->job_title,
                'job_location' => $job->job_location,
                'job_employment_type' => $job->job_employment_type,
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
        $job->save();

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');
    }

    public function disapprove(Job $job)
    {
        $job->is_approved = 0;
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
}
