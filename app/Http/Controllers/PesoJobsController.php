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
        $jobs = Job::with('user', 'sector', 'category')
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
    $jobs = Job::with('user', 'sector', 'category')
        ->whereHas('user', function ($query) {
            $query->where('role', 'peso'); // Filter jobs posted by users with the role 'peso'
        })
        ->orderBy('created_at', 'desc') // Sort by created_at in descending order
        ->paginate(10); // Paginate the results

    $sectors = Sector::all();
    $categories = Category::all();

    return Inertia::render('Admin/Jobs/Index/PesoJobs', [
        'jobs' => $jobs,
        'sectors' => $sectors,
        'categories' => $categories,
    ]);
}


    
    public function create(User $user) {
         $sectors = Sector::with('categories')->get();
         $programs = Program::select('id', 'name')->get();



        return Inertia::render('Admin/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
             'programs' => $programs,
    ]);
    }

    public function archivedlist(User $user)
    {
        $all_jobs = Job::onlyTrashed()
        ->with('user')
        ->orderBy('created_at', 'desc') 
        ->paginate(10); 

    return Inertia::render('Admin/Jobs/Index/ArchivedList', [
        'all_jobs' => $all_jobs, 
    ]);
    }

    public function manage(User $user) {
        $jobs = Job::with('user')
            ->orderBy('created_at', 'desc') // Sort by created_at in descending order
            ->paginate(10); // Paginate the results
        return Inertia::render('Admin/Jobs/Index/ManageJobs', [
            'jobs' => $jobs
        ]);
    }
    


    public function store(Request $request, User $user) {
        $validated = $request->validate([
            'job_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobs')->where(function ($query) use ($user, $request) {
                    // Ensures that job titles are unique for each user and location
                    return $query->where('user_id', $user->id)
                        ->where('location', $request->location);
                }),
            ],
            'location' => 'required|string|max:255',
            'branch_location' => 'nullable|string|max:255',
            'vacancy' => 'required|integer|min:1', // Added minimum value check for vacancies
            'min_salary' => 'nullable|integer|min:5000', // Ensuring a minimum salary of 5,000
            'max_salary' => 'nullable|integer|min:5000|gte:min_salary', // Added min_salary constraint
            'is_salary_negotiable' => 'required|boolean',
            'job_type' => 'required|string|max:255', // Added max length to string
            'experience_level' => 'required|string|max:255', // Added max length to string
            'description' => 'required|string|max:5000',
            'requirements' => 'required|string|max:5000',
            'expiration_date' => 'required|date|after:today', // Ensuring the expiration date is in the future
            'applicants_limit' => 'nullable|integer|min:1', // Added minimum value check for applicants
            'skills' => 'required|array|min:1', // Ensuring at least one skill is provided
            'sector' => 'required|exists:sectors,id',
            'category' => 'required|exists:categories,id',
            'program_id' => 'required|array|min:1', // Ensuring at least one program is selected
            'program_id.*' => 'exists:programs,id', // Validating each selected program's ID
            'posted_by' => 'nullable|string|max:255', // Made nullable to allow default value to be assigned
        ], [
            'job_title.required' => 'Please provide a job title.',
            'location.required' => 'Job location is required.',
            'min_salary.min' => 'The minimum salary must be at least ₱5000.',
            'max_salary.gte' => 'The maximum salary cannot be less than the minimum salary.',
            'expiration_date.after' => 'The application deadline must be a future date.',
            'vacancy.required' => 'Please provide the number of vacancies for this job.',
            'skills.required' => 'At least one skill is required.',
            'program_id.required' => 'Please select at least one program.',
            'program_id.*.exists' => 'Each selected program must be valid.',
            'description.required' => 'Please provide a job description.',
            'requirements.required' => 'Please provide the job requirements.',
        ]);

        $new_job = new Job();
        $new_job->user_id = $user->id;
        $new_job->job_title = $validated['job_title'];
        $new_job->location = $validated['location'];
        $new_job->branch_location = $validated['branch_location'];

        // Salary handling
        if ($validated['is_salary_negotiable']) {
            $new_job->min_salary = null;
            $new_job->max_salary = null;
            $new_job->is_salary_negotiable = true;
        } else {
            $new_job->min_salary = $validated['min_salary'];
            $new_job->max_salary = $validated['max_salary'];
            $new_job->is_salary_negotiable = false;
        }

        $new_job->job_type = $validated['job_type'];
        $new_job->experience_level = $validated['experience_level'];
        $new_job->skills = json_encode($validated['skills']);
        $new_job->vacancy = $validated['vacancy'];
        $new_job->description = $validated['description'];
        $new_job->requirements = $validated['requirements'];
        $new_job->sector_id = $validated['sector'];
        $new_job->category_id = $validated['category'];
        $new_job->expiration_date = Carbon::parse($validated['expiration_date'])->format('Y-m-d');
        $new_job->applicants_limit = $validated['applicants_limit'] ?? null;
        $new_job->posted_by = $validated['posted_by'] ?? null;
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
            'job_type' => $job->job_type,
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



    public function edit(Job $job) {
        return Inertia::render('Admin/Jobs/Edit/Index', [
            'job' => $job
        ]);
    }

    public function update(Request $request, Job $job){
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
        $job->save();
    
        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');    }

    public function disapprove(Job $job)
    {
        $job->is_approved = 0; 
        $job->save();

        return redirect()->route('peso.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job disapproved successfully.');
    }


    public function delete(Request $request, Job $job) {

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
