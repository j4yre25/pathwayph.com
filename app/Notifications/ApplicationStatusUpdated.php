<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    public $application;
    public $status;

    public function __construct($application, $status)
    {
        $this->application = $application;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // or ['mail'] if you only want email
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Your Application Status Has Been Updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of your application for the position "' . $this->application->job->job_title . '" has been updated to: ' . ucfirst($this->status) . '.')
            ->action('View Application', url('/job-inbox'))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {

        if ($this->status === 'offer_sent') {
        $message = 'Congratulations! You have received a job offer for the position "' . $this->application->job->job_title . '". Please review the offer details and respond.';
        } else {
            $message = 'The status of your application for the position "' . $this->application->job->job_title . '" has been updated to: ' . ucfirst($this->status) . '.';
        }
        return [
            'application_id' => $this->application->id,
            'job_title' => $this->application->job->job_title,
            'status' => $this->status,
            'message' => $message
        ];
    }
}