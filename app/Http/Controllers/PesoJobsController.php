<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
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


        return Inertia::render('Admin/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
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
        // return Inertia::render('Jobs/Index/CreateJobs');
        $validated = $request->validate([
            'job_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobs')->where(function ($query) use ($user, $request) {
                    return $query->where('user_id', $user->id)
                                 ->where('location', 'PESO');
                }),
            ],
            'vacancy' => 'required|integer',
            'min_salary' => 'required|integer',
            'max_salary' => 'required|integer',
            'job_type' => 'required|string',
            'experience_level' => 'required|string',
            'description' => 'required|string| max:5000',
            'requirements' => 'required|string',
            'skills' => 'required|array',
            'sector' => 'required|exists:sectors,id', 
            'category' => 'required|exists:categories,id',
        ]);
    
        $new_job = new Job();
        $new_job->user_id = $user->id;
        $new_job->job_title = $validated['job_title'];
        $new_job->location = 'PESO'; 
        $new_job->min_salary = $validated['min_salary'];
        $new_job->max_salary = $validated['max_salary'];
        $new_job->job_type = $validated['job_type'];
        $new_job->experience_level = $validated['experience_level'];
        $new_job->skills = json_encode($validated['skills']);
        $new_job->vacancy = $validated['vacancy'];
        $new_job->description = $validated['description'];
        $new_job->requirements = $validated['requirements'];
        $new_job->sector_id = $validated['sector']; 
        $new_job->category_id = $validated['category']; 
        $new_job->save();
    
        // return redirect()->back()->with('flash.banner', 'Job posted successfully.');
        return redirect()->route('peso.jobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
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
            'user' =>Auth::user(),
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
