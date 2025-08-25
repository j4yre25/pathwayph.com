<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\ApplicantScreeningService;

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
    



        $job = $application->job;
        
        $screening = (new ApplicantScreeningService())->screen($graduate, $job);

        $application->is_shortlisted = $screening['is_shortlisted'];
        $application->status = $screening['status'];
        $application->stage = 'Screening';
        $application->screening_label = $screening['screening_label'];
        $application->screening_feedback = $screening['screening_feedback'];
        $application->save();

        
        return back()->with('success', 'Application submitted successfully.');
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
