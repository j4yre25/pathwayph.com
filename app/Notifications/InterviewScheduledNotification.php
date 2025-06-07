<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduledNotification extends Notification 
{
    use Queueable;

    /**
     * The interview instance.
     *
     * @var mixed
     */
    public $interview;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $interview
     * @return void
     */
    public function __construct($interview)
    {
        $this->interview = $interview;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
            return (new MailMessage)
            ->subject('Interview Scheduled')
            ->line('Your interview has been scheduled.')
            ->line('Date & Time: ' . $this->interview->scheduled_at->format('F j, Y, g:i a'))
            ->line('Location: ' . ($this->interview->location ?? 'TBA'))
            ->action('View Application', url('/applications/' . $this->interview->job_application_id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Get job title and company from the related job application
        $jobTitle = $this->interview->jobApplication->job->job_title ?? '';
        $company = $this->interview->jobApplication->job->company->company_name ?? '';

        return [
            'interview_id' => $this->interview->id,
            'scheduled_at' => $this->interview->scheduled_at,
            'location' => $this->interview->location,
            'job_application_id' => $this->interview->job_application_id,
            'job_title' => $jobTitle,
            'company' => $company,
            'title' => 'Interview Scheduled',
            'message' => "Your interview for <b>{$jobTitle}</b> at <b>{$company}</b> has been scheduled for " . $this->interview->scheduled_at->format('F j, Y, g:i a') . '.',
            'action_url' => url('/applications/' . $this->interview->job_application_id),
        ];
    }
}
