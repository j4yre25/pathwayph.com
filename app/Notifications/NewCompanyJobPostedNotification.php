<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCompanyJobPostedNotification extends Notification
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
        $company = $this->job->company->company_name ?? 'a company';
        return [
            'message' => "A new job \"{$this->job->job_title}\" was posted by {$company}.",
            'job_id' => $this->job->id,
            'company_id' => $this->job->company->id ?? null,
            'url' => route('admin.jobs.view', $this->job->id), // Add this line

        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
