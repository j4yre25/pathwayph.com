<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobClosedNotification extends Notification
{
    use Queueable;

    public function __construct(public $job, public string $reason) {}

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'type' => 'job_closed',
            'job_id' => $this->job->id,
            'job_title' => $this->job->job_title,
            'reason' => $this->reason,
            'message' => match($this->reason) {
                'vacancy_filled' => "Job '{$this->job->job_title}' closed: vacancies filled.",
                'application_limit_reached' => "Job '{$this->job->job_title}' closed: application limit reached.",
                default => "Job '{$this->job->job_title}' closed."
            },
        ];
    }
}