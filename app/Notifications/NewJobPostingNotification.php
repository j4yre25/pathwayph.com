<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewJobPostingNotification extends Notification
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
            'message' => 'A new job "' . $this->job->job_title . '" has been posted by ' . $company . '.',
        ];
    }

    public function toArray($notifiable)
    {
        $company = $this->job->company->company_name ?? 'the company';

        return [
            'job_id' => $this->job->id,
            'title' => $this->job->job_title,
            'company' => $this->job->company->company_name ?? 'Unknown',
            'message' => 'A new job "' . $this->job->job_title . '" has been posted by ' . $company . '.',
        ];
    }
}
