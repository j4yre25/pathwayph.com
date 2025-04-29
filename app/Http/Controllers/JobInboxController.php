<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
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
            'notifications' => $this->getNotifications($user)
        ]);
    }

    // Get job opportunities
    public function getJobOpportunities($user)
    {
        $jobs = Job::with(['sector', 'category'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) use ($user) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company' => $job->company,
                    'location' => $job->location,
                    'salary' => $job->min_salary . ' - ' . $job->max_salary,
                    'type' => $job->work_type,
                    'posted_at' => $job->created_at->diffForHumans(),
                    'description' => $job->description,
                    'experience_level' => $job->experience_level,
                    'required_skills' => explode(',', $job->skills),
                    'match_percentage' => $this->calculateMatchPercentage($user)
                ];
            });

        return $jobs;
    }

    // Get job applications
    public function getJobApplications($user)
    {
        return JobApplication::with('job')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $progress = $this->calculateApplicationProgress($application->status);
                
                return [
                    'id' => $application->id,
                    'job_title' => $application->job->title,
                    'company' => $application->job->company,
                    'status' => $application->status,
                    'progress' => $progress,
                    'applied_at' => $application->created_at->format('M d, Y'),
                    'interview_date' => optional($application->interview_date)->format('M d, Y')
                ];
            });
    }

    // Get notifications
    public function getNotifications($user)
    {
        return $user->notifications()
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'application_update',
                    'created_at' => $notification->created_at->diffForHumans(),
                    'read' => !is_null($notification->read_at)
                ];
            });
    }

    // Apply for a job
    public function applyForJob(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'job_id' => 'required|integer'
        ]);

        // Example: store application with user relationship
        // $application = new Application($validated);
        // $user->applications()->save($application);

        return response()->json(['success' => true]);
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