<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Sector;
use App\Models\JobInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Inertia\Inertia;


class CompanyJobsController extends Controller
{
    public function index(User $user) {

        // $jobs = $user->jobs()->withCount('applicant')->get();


        $jobs = $user->jobs;
        $sectors = Sector::pluck('name'); // Fetch all sector names
        $categories = \App\Models\Category::pluck('name'); // Fetch all category names
        
        return Inertia::render('Company/Jobs/Index/Index', [
            'jobs' => $jobs,
            'sectors' => $sectors, // Array of sectors
            'categories' => $categories,
        ]);

    
    }


    
    public function create(User $user) {
         $sectors = Sector::with('categories')->get();


        return Inertia::render('Company/Jobs/Index/CreateJobs', [
            'sectors' => $sectors,
    ]);
    }

    public function archivedlist(User $user)
    {


        $all_jobs = Job::with('user')->onlyTrashed()->get();

        return Inertia::render('Company/Jobs/Index/ArchivedList', [
            'all_jobs' => $all_jobs


        ]);
    }

    public function manage(User $user) {
        // Fetch jobs posted by the authenticated user
        $jobs = $user->jobs;
    
        return Inertia::render('Company/Jobs/Index/ManageJobs', [
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
            'branch_location' => 'nullable|string|max:255',
            'vacancy' => 'required|integer',
            'salary_type' => 'required|string',
            'min_salary' => 'required|integer',
            'max_salary' => 'required|integer',
            'job_type' => 'required|string',
            'experience_level' => 'required|string',
            'description' => 'required|string| max:5000',
            'requirements' => 'required|string',
            'job_benefits' => 'nullable|string',
            'expiration_date' => 'required|date',
            'applicants_limit' => 'nullable|integer',
            'skills' => 'required|array',
            'sector' => 'required|exists:sectors,id', 
            'category' => 'required|exists:categories,id',
            'posted_by' => 'string|max:255',
        ]);
    
        $new_job = new Job();
        $new_job->user_id = $user->id;
        $new_job->job_title = $validated['job_title'];
        $new_job->location = $validated['location'];
        $new_job->branch_location = $validated['branch_location'];
        $new_job->salary_type = $validated['salary_type'];
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
        $new_job->job_benefits = $validated['job_benefits'];
        $new_job->expiration_date = Carbon::parse($validated['expiration_date'])->format('F j, Y');
        $new_job->applicants_limit = $validated['applicants_limit'] ?? null; 
        $new_job->posted_by = $validated['posted_by'] ?? null; 
        $new_job->save();
    
        // return redirect()->back()->with('flash.banner', 'Job posted successfully.');
        return redirect()->route('company.jobs', ['user' => $user->id])->with('flash.banner', 'Job posted successfully.');
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

        return Inertia::render('Company/Jobs/View/EmployersJobDetails', [
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
            'user' => Auth::user(),
        ]);
    }

    public function edit(Job $job) {
        return Inertia::render('Company/Jobs/Edit/Index', [
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
    
        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job approved successfully.');    }

    public function disapprove(Job $job)
    {
        $job->is_approved = 0; 
        $job->save();

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job disapproved successfully.');
    }


    public function delete(Request $request, Job $job) {

        // Gate::authorize('delete', $job);

        $job->delete();
    
        // $user_id = $request->user()->id;

        return redirect()->route('company.jobs', ['user' => $job->user_id])->with('flash.banner', 'Job Archived successfully.');
    }


    public function autoInvite(Job $job)
{
    // Get all graduates who have a graduate profile
    $allGraduates = User::where('role', 'graduate')
        // ->whereHas('graduate') 
        ->get();

    foreach ($allGraduates as $graduate) {
        JobInvitation::updateOrCreate([
            'job_id' => $job->id,
            'graduate_id' => $graduate->id,
        ], [
            'company_id' => auth()->id(),
            'status' => 'pending',
            'message' => 'You have been invited to apply to this job opportunity.',
        ]);
    }

    return back()->with('flash.banner', count($allGraduates) . ' graduates invited successfully.');
}


    public function restore($job)
    {
        $job = Job::withTrashed()->findOrFail($job);

        $job->restore();

        return redirect()->back()->with('flash.banner', 'Job restored successfully.');
    }
    
}