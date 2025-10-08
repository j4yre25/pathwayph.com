<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(public $job) {}

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'type' => 'job_expired',
            'job_id' => $this->job->id,
            'job_title' => $this->job->job_title,
            'message' => "Job '{$this->job->job_title}' expired (deadline passed) and was archived."
        ];
    }
}