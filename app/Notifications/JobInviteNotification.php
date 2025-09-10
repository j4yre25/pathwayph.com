<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobInviteNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job)
    {
        $this->job->loadMissing('company');
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $company = $this->job->company->company_name ?? 'the company';
        return [
            'message' => 'You have been invited to apply for "' . $this->job->job_title . '" at ' . $company . '.',
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}

