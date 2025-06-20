<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Notifications\InterviewScheduledNotification;
use App\Notifications\ApplicationStatusUpdated;
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

    public function updateNote(Request $request, JobApplication $application)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $application->notes = $request->input('notes');
        $application->save();

        return redirect()->back()->with('success', 'Note updated successfully.');
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

    public function updateStage(Request $request, JobApplication $application)
    {
        $request->validate([
            'stage' => 'required|string|in:applying,screening,testing,final interview,onboarding'
        ]);
        $application->stage = $request->stage;
        $application->save();

        // ... inside your method after saving the new status:
        if ($application->graduate && $application->graduate->user) {
            $application->graduate->user->notify(new ApplicationStatusUpdated($application, $application->stage));
        }
        return back()->with('success', 'Stage updated.');
    }
}
