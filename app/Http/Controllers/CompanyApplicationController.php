<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Notifications\InterviewScheduledNotification;
use App\Notifications\ApplicationStatusUpdated;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanyApplicationController extends Controller
{
/**
 * Display the specified graduate's portfolio.
 *
 * @param  \App\Models\User  $user
 * @return \Inertia\Response
 */
    public function show(JobApplication $application)
    {
            // Load related data (skills, portfolio, etc.)
        $application->load([
                'graduate.user',       // Basic user info
                'graduate.graduateSkills.skill',    
                'graduate.education',   
                'graduate.experience',
                'graduate.projects',
                'graduate.achievements',
                'graduate.certifications',
                'graduate.testimonials',
                'graduate.employmentPreference',
                'graduate.employmentPreference.jobTypes',
                'graduate.employmentPreference.locations',
                'graduate.employmentPreference.workEnvironments',
                'graduate.employmentPreference.salary',
                'graduate.careerGoals',
                'graduate.resume',
                'job',                 // Job applied for
            ]);

        $graduate = $application->graduate;

        $resume = $graduate->resume;
        if ($resume) {
            $resume->file_url = Storage::url('resumes/' . $resume->file_name); // or $resume->file_path
        }

        return Inertia::render('Company/Applicants/ListOfApplicants/ApplicantProfile', [
            'applicant' => $application,
            'graduate' => $graduate,
            'skills' => $graduate?->graduateSkills?->map(function($gs) {
                return [
                    'id' => $gs->id,
                    'skill_id' => $gs->skill_id,
                    'skill_name' => $gs->skill->name ?? null,
                    'years_experience' => $gs->years_experience,
                    'proficiency_type' => $gs->proficiency_type,
                    // add other fields if needed
                ];
            }) ?? [],
            'experiences' => $graduate?->experience ?? [],
            'education' => $graduate?->education ?? [],
            'projects' => $graduate?->projects ?? [],
            'achievements' => $graduate?->achievements ?? [],
            'certifications' => $graduate?->certifications ?? [],
            'testimonials' => $graduate?->testimonials ?? [],
            'employmentPreferences' => $graduate?->employmentPreference,
            'careerGoals' => $graduate?->careerGoals,
            'resume' => ($resume = $graduate?->resume) ? [
                'file_url' => Storage::url($resume->file_path),
                'file_type' => $resume->file_type,
                'file_name' => $resume->file_name,
            ] : null,
            'job' => $application->job,
        ]);
    }

    public function update(Request $request, JobApplication $application)
    {
        // Allow partial updates
        $data = $request->validate([
            'notes'  => 'sometimes|nullable|string|max:2000',
            'stage'  => 'sometimes|nullable|string|in:applying,screening,testing,interview,offer,onboarding,hired,rejected',
            'status' => 'sometimes|nullable|string|in:under_review,offer_sent,offer_accepted,offer_declined,hired,rejected',
        ]);

        $changed = [];

        if (array_key_exists('notes', $data) && $data['notes'] !== $application->notes) {
            $application->notes = $data['notes'];
            $changed[] = 'notes';
        }

        if (array_key_exists('stage', $data) && $data['stage'] !== $application->stage) {
            $application->stage = $data['stage'];
            $changed[] = 'stage';
        }

        if (array_key_exists('status', $data) && $data['status'] !== $application->status) {
            $application->status = $data['status'];
            $changed[] = 'status';
        }

        if (!empty($changed)) {
            $application->save();

            // Notify on stage or status changes
            if (($application->graduate && $application->graduate->user) &&
                (in_array('stage', $changed) || in_array('status', $changed))) {
                $application->graduate->user->notify(
                    new ApplicationStatusUpdated($application, $application->stage ?? $application->status)
                );
            }
        }

        return back()->with('success', 'Application updated: '.implode(', ', $changed));
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
        ]);

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'scheduled_at' => $request->scheduled_at,
            'location' => $request->location,
        ]);

        $interview->load('jobApplication.job.company');
        
        // Send notification to graduate/user
        if ($application->graduate && $application->graduate->user) {
        $application->graduate->user->notify(new InterviewScheduledNotification($interview));
        }


        // (Optional) Integrate with Google/Outlook Calendar here and save event ID

        return redirect()->back()->with('success', 'Interview scheduled and notification sent.');
    }

    public function storeOffer(Request $request, JobApplication $application)
    {
        $request->validate([
            'offered_salary' => 'required|numeric|min:0',
            'start_date' => 'required|date|after:today',
            'offer_letter' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $offerData = [
            'job_application_id' => $application->id,
            'offered_salary' => $request->offered_salary,
            'start_date' => $request->start_date,
        ];

        if ($request->hasFile('offer_letter')) {
            $offerData['offer_letter_path'] = $request->file('offer_letter')->store('offer_letters', 'public');
        }

        $offer = JobOffer::create($offerData);

        // Update application status
        $application->status = 'offer_sent';
        $application->save();

        // Notify applicant
        if ($application->graduate && $application->graduate->user) {
            $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->status));
        }

        return redirect()->back()->with('success', 'Job offer sent successfully.');
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $request->validate([
            'status' => 'required|string|in:under_review,offer_sent,offer_accepted,offer_declined,hired'
        ]);

        $application->status = $request->status;
        $application->save();

        // If hired, update graduate's info
        if ($request->status === 'hired') {
            $graduate = $application->graduate;
            if ($graduate && $application->job) {
                $graduate->current_job_title = $application->job->job_title ?? 'Hired';
                $graduate->company_id = $application->job->company_id;
                $graduate->employment_status = 'Employed';
                $graduate->save();
            }
        }

        // Notify applicant
        if ($application->graduate && $application->graduate->user) {
            $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->status));
        }

        return back()->with('success', 'Status updated.');
    }
}
