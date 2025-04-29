<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;


class PesoJobsController extends Controller
{
    public function index(User $user) {


        $jobs = Job::with('user', 'sector', 'category')->get();
        $sectors = Sector::all();
        $categories = Category::all();
    

        return Inertia::render('Admin/Jobs/Index/Index', [
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


        $all_jobs = Job::with('user')->onlyTrashed()->get();

        return Inertia::render('Admin/Jobs/Index/ArchivedList', [
            'all_jobs' => $all_jobs


        ]);
    }

    public function manage(User $user) {
        // Fetch jobs posted by the authenticated user
        $jobs = Job::with('user')->get();
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
                                 ->where('location', $request->location);
                }),
            ],
            'location' => 'required|string|max:255',
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
        $new_job->location = $validated['location'];
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

   


    public function view(Job $job) {
        return Inertia::render('Admin/Jobs/View/Index', [
            'job' => $job
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

        return redirect()->back()->with('flash.banner', 'Job restored successfully.');
    }
}
