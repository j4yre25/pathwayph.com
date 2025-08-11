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
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $jobTitle = $this->application->job->job_title;
        $status = ucfirst($this->status);

        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Your Application Status Has Been Updated')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->status === 'rejected') {
            $mail->line('We regret to inform you that your application for the position "' . $jobTitle . '" has been rejected.')
                ->line('Reason: ' . ($this->application->screening_feedback ?? 'Not specified.'))
                ->action('View Application', url('/job-inbox'))
                ->line('Thank you for your interest and we encourage you to apply for other positions.');
         } elseif ($this->status === 'shortlisted') {
            $mail->line('Congratulations! You have been shortlisted for the position "' . $jobTitle . '".')
                ->action('View Application', url('/job-inbox'))
                ->line('We will contact you for the next steps.');
        } elseif ($this->status === 'offer_sent') {
            $mail->line('Congratulations! You have received a job offer for the position "' . $jobTitle . '". Please review the offer details and respond.')
                ->action('View Application', url('/job-inbox'))
                ->line('Thank you for using our platform!');
        } else {
            $mail->line('The status of your application for the position "' . $jobTitle . '" has been updated to: ' . $status . '.')
                ->action('View Application', url('/job-inbox'))
                ->line('Thank you for using our platform!');
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        $jobTitle = $this->application->job->job_title;

         if ($this->status === 'offer_sent') {
            $message = 'Congratulations! You have received a job offer for the position "' . $jobTitle . '". Please review the offer details and respond.';
        } elseif ($this->status === 'rejected') {
            $message = 'We regret to inform you that your application for the position "' . $jobTitle . '" has been rejected. Reason: ' . ($this->application->screening_feedback ?? 'Not specified.');
        } elseif ($this->status === 'shortlisted') {
            $message = 'Congratulations! You have been shortlisted for the position "' . $jobTitle . '". We will contact you for the next steps.';
        } else {
            $message = 'The status of your application for the position "' . $jobTitle . '" has been updated to: ' . ucfirst($this->status) . '.';
        }

        return [
            'application_id' => $this->application->id,
            'job_title' => $jobTitle,
            'status' => $this->status,
            'message' => $message
        ];
    }
}