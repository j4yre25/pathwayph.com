<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewJobPostingNotification extends Notification
{
   use Queueable;

    public $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function via($notifiable)
    {
        return ['database']; // or ['mail', 'database'] for email + in-app
    }

    public function toArray($notifiable)
    {
        return [
            'job_id' => $this->job->id,
            'title' => $this->job->job_title,
            'company' => $this->job->company->company_name ?? 'Unknown',
        ];
    }
}
