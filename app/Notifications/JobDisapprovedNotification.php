<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Job;

class JobDisapprovedNotification extends Notification
{
    use Queueable;
    public $job;

    /**
     * Create a new notification instance.
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
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

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Job Posting Has Been Disapproved')
            ->greeting('Hello!')
            ->line('Your job posting "' . $this->job->job_title . '" has been disapproved by PESO.')
            ->line('Please review your job posting and make necessary changes before resubmitting.');
    }

     public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your job posting "' . $this->job->job_title . '" has been disapproved by PESO.',
            'job_id' => $this->job->id,
            'company_id' => $this->job->company_id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
