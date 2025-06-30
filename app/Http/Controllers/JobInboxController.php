<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;

class JobInboxController extends Controller
{
    // Display job inbox index page
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Frontend/JobInbox', [
            'jobOpportunities' => $this->getJobOpportunities($user),
            'jobApplications' => $this->getJobApplications($user),
            'notifications' => $this->getNotifications()
        ]);
    }

    // Get job opportunities
    public function getJobOpportunities($user)
    {
        $jobs = Job::with(['sector', 'category'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) use ($user) {
                return [
                    'id' => $job->id,
                    'title' => $job->job_title,
                    'company' => optional($job->company)->company_name ?? 'Unknown Company',
                    'location' => $job->job_location,
                    'salary' => ($job->job_min_salary && $job->job_max_salary)
                        ? "{$job->job_min_salary} - {$job->jpb_max_salary}"
                        : 'Negotiable',
                    'type' => $job->job_employment_type,
                    'posted_at' => $job->created_at->diffForHumans(),
                    'description' => $job->job_description,
                    'experience_level' => $job->job_experience_level,
                    'required_skills' => is_array($job->related_skills) ? $job->related_skills : json_decode($job->related_skills, true),
                    'match_percentage' => $this->calculateMatchPercentage($user)
                ];
            });

        return $jobs;
    }

    // Get job applications
    public function getJobApplications($user)
    {
        return JobApplication::with(['job.company']) // Eager load job and company
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $progress = $this->calculateApplicationProgress($application->status);

                return [
                    'id' => $application->id,
                    'job_title' => $application->job->job_title,
                    'company' => optional($application->job->company)->company_name ?? 'Unknown Company',
                    'status' => $application->status,
                    'progress' => $progress,
                    'applied_at' => $application->created_at->format('M d, Y'),
                    'interview_date' => optional($application->interview_date)->format('M d, Y'),
                ];
            });
    }

    // Get notifications
    public function getNotifications()
    {
        $user = Auth::user();

        return $user->notifications()
            // ->whereNull('read_at')
            ->whereIn('type', [
                \App\Notifications\NewJobPostingNotification::class,
                \App\Notifications\ApplicationStatusUpdated::class,
                \App\Notifications\InterviewScheduledNotification::class,
                \App\Notifications\JobInviteNotification::class
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $type = $notification->type;
                $data = $notification->data;

                // Default fields
                $result = [
                    'id' => $notification->id,
                    'type' => class_basename($type),
                    'data' => $data,
                    'title' => $data['title'] ?? '',
                    'company' => $data['company'] ?? '',
                    'job_id' => $data['job_id'] ?? null,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'read' => !is_null($notification->read_at),
                ];

                // Special handling for InterviewScheduledNotification
                if ($type === \App\Notifications\InterviewScheduledNotification::class) {
                    $result['interview_id'] = $data['interview_id'] ?? null;
                    $result['scheduled_at'] = $data['scheduled_at'] ?? null;
                    $result['location'] = $data['location'] ?? null;
                    $result['job_application_id'] = $data['job_application_id'] ?? null;
                    $result['job_title'] = $data['job_title'] ?? '';
                    $result['message'] =
                        'Interview scheduled for ' .
                        ($data['job_title'] ?? 'this position') .
                        ((!empty($data['company'])) ? ' at ' . $data['company'] : '') .
                        (!empty($data['scheduled_at']) ? ' on ' . \Carbon\Carbon::parse($data['scheduled_at'])->format('F j, Y, g:i a') : '') .
                        (!empty($data['location']) ? ' (' . $data['location'] . ')' : '') .
                        '.';
                    $result['action_url'] = $data['action_url'] ?? '';
                }

                return $result;
            });
    }

    public function showNotification($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);

        // Optionally mark as read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return Inertia::render('Frontend/NotificationDetail', [
            'notification' => [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $notification->data,
                'created_at' => $notification->created_at->toDayDateTimeString(),
                'read' => !is_null($notification->read_at),
            ]
        ]);
    }

    public function offerResponse(Request $request, JobApplication $application)
    {
        $request->validate([
            'response' => 'required|in:accept,decline'
        ]);

        // Use the correct relationship
        $jobOffer = $application->offer;

        if ($request->response === 'accept') {
            $application->status = 'offer_accepted';
            $application->stage = 'Onboarding';
            $application->save();

            if ($jobOffer) {
                $jobOffer->status = 'accepted';
                $jobOffer->save();
            }
        } else {
            $application->status = 'offer_declined';
            $application->save();

            if ($jobOffer) {
                $jobOffer->status = 'declined';
                $jobOffer->save();
            }
        }
        // Optionally, send notification to employer here

        return back()->with('success', 'Your response has been recorded.');
    }

    // Apply for a job
    public function applyForJob(Request $request)
    {
        $user = Auth::user();
        $graduate = $user->graduate;

        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'applied_at' => 'nullable|date',
            'interview_date' => 'nullable|date',
            'resume_id' => 'nullable|exists:resumes,id',
            'cover_letter' => 'nullable|string',
            'additional_documents' => 'nullable|array',
            // add other fields kung unsa pa ang need (check ang table named job_applications sa database)
        ]);

        // Check if user already applied for this job
        $exists = JobApplication::where('user_id', $user->id)
            ->where('job_id', $validated['job_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job.'
            ], 409);
        }

        // Create application
        $application = JobApplication::create([
            'user_id' => $user->id,
            'graduate_id' => $graduate->id,
            'job_id' => $validated['job_id'],
            'resume_id' => $validated['resume_id'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'additional_documents' => $validated['additional_documents'] ?? null,
            'status' => 'applied',
            'stage' => 'applying',
            'applied_at' => now(),
        ]);

        // --- Automated Screening Logic ---
        $job = $application->job;

        // 1. Retrieve job requirements (example fields, adjust as needed)
        $requiredDegree = $job->required_degree ?? 'Bachelor';
        $minExperience = $job->min_experience ?? 2; // years
        $requiredSkills = $job->required_skills ?? ['PHP', 'Vue.js']; // array or comma-separated

        // 2. Calculate applicant's total experience (in years)
        $totalExperience = $graduate->experience->sum(function($exp) {
            $start = $exp->start_date ? \Carbon\Carbon::parse($exp->start_date) : null;
            $end = $exp->end_date ? \Carbon\Carbon::parse($exp->end_date) : now();
            return $start && $end ? $start->diffInYears($end) : 0;
        });

        // 3. Get applicant's skills
        $skills = $graduate->graduateSkills->pluck('skill.name')->map(fn($s) => strtolower($s))->toArray();

        // 4. Check applicant's education
        $hasDegree = $graduate->education->contains(function($edu) use ($requiredDegree) {
            return stripos($edu->education, $requiredDegree) !== false;
        });

        // 5. Check if all criteria are met
        $hasSkills = collect($requiredSkills)->every(fn($skill) => in_array(strtolower($skill), $skills));
        $isQualified = $hasDegree && $totalExperience >= $minExperience && $hasSkills;

        if ($isQualified) {
            $application->is_shortlisted = true;
            $application->status = 'shortlisted';
            $application->stage = 'Screened';
            $application->screening_feedback = 'Auto-screened: Qualified';
        } else {
            $application->is_shortlisted = false;
            $application->screening_feedback = 'Auto-screened: Not qualified';
            // Optionally set status to 'rejected'
            // $application->status = 'rejected';
        }
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully.',
            'application' => $application,
        ]);
    }


    // Archive a job opportunity
    public function archiveJobOpportunity(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|integer'
        ]);

        // Add your archiving logic here

        return response()->json(['success' => true]);
    }

    // Mark notification as read
    public function markNotificationAsRead(Request $request)
    {
        $validated = $request->validate([
            'notification_id' => 'required|integer'
        ]);

        // Add your notification marking logic here

        return response()->json(['success' => true]);
    }

    // Helper method to calculate match percentage
    private function calculateMatchPercentage($user)
    {
        // TODO: Implement actual matching logic based on user skills and job requirements
        return rand(70, 100);
    }

    // Helper method to calculate application progress
    private function calculateApplicationProgress($status)
    {
        $progressMap = [
            'pending' => 25,
            'reviewing' => 50,
            'interview' => 75,
            'offer' => 100,
            'rejected' => 0
        ];

        return $progressMap[$status] ?? 0;
    }
}
