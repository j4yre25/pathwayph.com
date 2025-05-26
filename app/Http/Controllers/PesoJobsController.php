<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\Program;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $jobs = Job::with('user', 'sector', 'category', 'company', 'peso',)
            ->where('peso_id', $peso ? $peso->id : 0)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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

        $user->load('peso');

        return Inertia::render('Admin/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
            'programs' => $programs,
            'user' => $user,
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
                        ->where('job_location', $request->job_location);
                }),
            ],
            'job_location' => 'required|string|max:255',
            'job_vacancies' => 'required|integer|min:1',
            'job_min_salary' => 'nullable|integer|min:5000',
            'job_max_salary' => 'nullable|integer|min:5000|gte:job_min_salary',
            'is_negotiable' => 'required|boolean',
            'job_employment_type' => 'required|string|max:255',
            'job_experience_level' => 'required|string|max:255',
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'required|string|max:5000',
            'job_deadline' => 'required|date|after:today',
            'job_application_limit' => 'nullable|integer|min:1',
            'related_skills' => 'required|array|min:1',
            'program_id' => 'required|array|min:1',
            'program_id.*' => 'exists:programs,id',
            'posted_by' => 'nullable|string|max:255',
            'sector_id' => 'required|exists:sectors,id',
            'category_id' => 'required|exists:categories,id',
        ], [
            'job_title.required' => 'Please provide a job title.',
            'job_location.required' => 'Job location is required.',
            'job_min_salary.min' => 'The minimum salary must be at least ₱5000.',
            'job_max_salary.gte' => 'The maximum salary cannot be less than the minimum salary.',
            'job_deadline.after' => 'The application deadline must be a future date.',
            'job_vacancies.required' => 'Please provide the number of vacancies for this job.',
            'related_skills.required' => 'At least one skill is required.',
            'program_id.required' => 'Please select at least one program.',
            'program_id.*.exists' => 'Each selected program must be valid.',
            'job_description.required' => 'Please provide a job description.',
            'job_requirements.required' => 'Please provide the job requirements.',
        ]);

        $peso = $user->peso;

        $new_job = new Job();
        $new_job->company_id = null;
        $new_job->user_id = $user->id;
        $new_job->peso_id = $peso ? $peso->id : null;
        $new_job->job_title = $validated['job_title'];
        $new_job->job_location = $validated['job_location'];
        $new_job->job_vacancies = $validated['job_vacancies'];
        $new_job->job_min_salary = $validated['job_min_salary'];
        $new_job->job_max_salary = $validated['job_max_salary'];
        $new_job->is_negotiable = $validated['is_negotiable'];
        $new_job->job_employment_type = $validated['job_employment_type'];
        $new_job->job_experience_level = $validated['job_experience_level'];
        $new_job->job_description = $validated['job_description'];
        $new_job->job_requirements = $validated['job_requirements'];
        $new_job->job_deadline = Carbon::parse($validated['job_deadline'])->format('Y-m-d');
        $new_job->job_application_limit = $validated['job_application_limit'] ?? null;
        $new_job->related_skills = $validated['related_skills'];
        $new_job->sector_id = $validated['sector_id'];
        $new_job->category_id = $validated['category_id'];
        $new_job->posted_by = $validated['posted_by'] ?? null;
        $new_job->is_approved = true;
        $new_job->save();
        $new_job->programs()->attach($validated['program_id']);

        return redirect()->route('peso.pesojobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
    }

    public function view(Job $job)
    {
        $job->load('company', 'category', 'user');

        // Combine min_salary and max_salary into a salary range string
        $salaryRange = $job->min_salary && $job->max_salary
            ? $job->min_salary . ' - ' . $job->max_salary
            : null;

        $hrFirstName = $job->user->company_hr_first_name ?? '';
        $hrLastName = $job->user->company_hr_last_name ?? '';

        $hrFullName = trim($hrFirstName . ' ' . $hrLastName);

        return Inertia::render('Admin/Jobs/View/EmployersJobDetails', [
            'job' => [
                'id' => $job->id,
                'job_title' => $job->job_title,
                'location' => $job->location,
                'job_employment_type' => $job->job_employment_type,
                'experience_level' => $job->experience_level,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'vacancy' => $job->vacancy,
                'skills' => is_array($job->skills) ? $job->skills : json_decode($job->skills, true),
                'is_approved' => $job->is_approved,
                'posted_at' => $job->created_at->format('F j, Y'),
                'posted_by' => $job->posted_by,
                'expiration_date' => Carbon::parse($job->expiration_date)->format('F j, Y'),
                'user_role' => $job->user->role ?? null,
                'category' => $job->category->name ?? null,
                'salary_range' => $salaryRange,
                'company' => $job->company ? [
                    'name' => $job->company->company_name ?? 'PESO',
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
                ] : [
                    'name' => 'PESO',
                    'email' => null,
                    'contact_number' => null,
                    'address' => null,
                    'profile_photo' => null,
                    'cover_photo' => null,
                ],
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

        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');
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
}
