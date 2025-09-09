<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class JobInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $job)
    {
        $this->job->loadMissing('company');
    }

    public function via($notifiable)
    {
        return ['database','mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Job Invitation: ' . $this->job->job_title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('You have been invited to apply for "' . $this->job->job_title . '" at ' . ($this->job->company->company_name ?? 'a company') . '.')
            ->action('View Job', url('/jobs/' . $this->job->id));
    }

    public function toDatabase($notifiable)
    {
        $company = $this->job->company->company_name ?? 'A Company';
        $title = 'Job Invitation: ' . $this->job->job_title;
        $body  = 'You have been invited to apply for "' . $this->job->job_title . '" at ' . $company . '.';

        return [
            'status'     => 'job_invite',
            'job_id'     => $this->job->id,
            'job_title'  => $this->job->job_title,
            'company'    => $company,
            'title'      => $title,
            'body'       => $body,
            'message'    => $body,
            'link'       => url('/jobs/' . $this->job->id),
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}

