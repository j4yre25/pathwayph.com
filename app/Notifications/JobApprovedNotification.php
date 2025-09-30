<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Job;

class JobApprovedNotification extends Notification
{
    use Queueable;

    public $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function via($notifiable)
    {
         return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Job Posting Has Been Approved')
            ->greeting('Hello!')
            ->line('Your job posting "' . $this->job->job_title . '" has been approved by PESO.')
            ->action('View Job', url('/company/jobs/' . $this->job->id))
            ->line('Thank you for using our platform!');
    }


    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your job posting "' . $this->job->job_title . '" has been approved by PESO.',
            'job_id' => $this->job->id,
            'company_id' => $this->job->company_id,
        ];
    }


    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
