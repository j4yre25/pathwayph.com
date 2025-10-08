<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Notifications\JobApprovedNotification;
use App\Notifications\JobDisapprovedNotification;
use Illuminate\Support\Facades\Storage;

class ManageJobApprovalController extends Controller
{

    public function index()
    {
        // Do not eager-load the old locations pivot; jobs now store a location string
        $jobs = \App\Models\Job::with(['salary', 'workEnvironments', 'jobTypes', 'company'])
            ->orderByRaw('is_approved IS NULL DESC') // Pending (null) first
            ->orderBy('created_at', 'desc')          // Newest first
            ->paginate(10);

        $totalJobs = \App\Models\Job::count();
        $openJobs = \App\Models\Job::where('status', 'open')->count();
        $closedJobs = \App\Models\Job::where('status', 'closed')->count();
        $expiredJobs = \App\Models\Job::where('status', 'expired')->count();

        return \Inertia\Inertia::render('Admin/Jobs/Index/ApprovalIndex', [
            'jobs' => $jobs,
            'kpiTotals' => [
                'total' => $totalJobs,
                'open' => $openJobs,
                'closed' => $closedJobs,
                'expired' => $expiredJobs,
            ],
        ]);
    }

    // Approve a job
    public function approve(Job $job)
    {
        $job->is_approved = true;
        $job->status = 'open';
        $job->save();


        if ($job->company && $job->company->user) {
            $job->company->user->notify(new JobApprovedNotification($job));
        }

        (new \App\Http\Controllers\CompanyJobsController)->notifyGraduates($job);


        return redirect()->back()->with('flash.banner', 'Job approved successfully.');
    }

    // Disapprove a job
    public function disapprove(Job $job)
    {
        $job->is_approved = false;
        $job->save();


        if ($job->company && $job->company->user) {
            $job->company->user->notify(new JobDisapprovedNotification($job));
        }

        return redirect()->back()->with('flash.banner', 'Job disapproved successfully.');
    }

    public function view(Job $job)
    {
        // Do not load the old locations relation; jobs store a simple location string now
        $job->load([
            'company',
            'category',
            'user.hr',
            'jobTypes',
            'workEnvironments',
            'programs'
        ]);

        $salaryRange = $job->job_min_salary && $job->job_max_salary
            ? "₱" . $job->job_min_salary . ' - ' . "₱" . $job->job_max_salary
            : "Negotiable";

        return \Inertia\Inertia::render('Admin/Jobs/View/JobDetails', [
            'job' => [
                'id' => $job->id,
                'job_title' => $job->job_title,
                // Keep front-end-friendly shape: array of addresses (was locations list)
                'location' => $job->location ? [$job->location] : [],
                'job_type' => $job->jobTypes->pluck('type')->toArray(),
                'work_environment' => $job->workEnvironments->pluck('environment_type')->toArray(),
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
                        ? \Carbon\Carbon::parse($job->job_deadline)->format('F j, Y')
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
                    'profile_photo' => $job->company->profile_photo_path ? \Storage::url($job->company->profile_photo_path) : null,
                    'cover_photo' => $job->company->cover_photo_path ? \Storage::url($job->company->cover_photo_path) : null,
                ],
                'programs' => $job->programs->pluck('name')->toArray(),
                'status' => $job->status,
            ],
        ]);
    }
}
