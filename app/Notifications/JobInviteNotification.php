<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class JobInviteNotification extends Notification
{
    protected $job;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function via($notifiable)
    {
        return ['database']; // stores in notifications table
    }

    public function toDatabase($notifiable)
    {
        return [
            'job_id' => $this->job->id,
            'title' => 'You have a new job invitation: ' . $this->job->job_title,
            'company' => $this->job->company->company_name ?? 'Unknown Company',
            'type' => 'invite',
        ];
    }
}

