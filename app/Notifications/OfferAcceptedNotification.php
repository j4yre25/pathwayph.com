<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferAcceptedNotification extends Notification
{
    use Queueable;

    public $application;
    public $offer;
    public $messageText;
    public $url;

    public function __construct($application, $offer, $messageText = null, $url = null)
    {
        $this->application = $application;
        $this->offer = $offer;
        $this->messageText = $messageText;
        $this->url = $url ?? url('/company/applicants/'.($application->id ?? ''));
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $jobTitle = $this->application->job->job_title ?? $this->offer->job_title ?? 'the position';
        $applicantName = trim(($this->application->graduate->first_name ?? '') . ' ' . ($this->application->graduate->last_name ?? ''));
        $subject = "Applicant in {$jobTitle} accepted the offer";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello,')
            ->line("{$applicantName} has accepted the offer for \"{$jobTitle}\".")
            ->line($this->messageText ?: '')
            ->action('View Applicant', $this->url)
            ->line('Thank you.');
    }

    public function toDatabase($notifiable)
    {
        $jobTitle = $this->application->job->job_title ?? $this->offer->job_title ?? 'the position';
        $applicantName = trim(($this->application->graduate->first_name ?? '') . ' ' . ($this->application->graduate->last_name ?? ''));

        return [
            'title' => "Applicant in {$jobTitle} accepted the offer",
            'subtitle' => "{$applicantName} accepted the offer in {$jobTitle}. Click to view more.",
            'body' => $this->messageText ?: "{$applicantName} accepted the offer.",
            'application_id' => $this->application->id ?? null,
            'offer_id' => $this->offer->id ?? null,
            'graduate_id' => $this->application->graduate_id ?? null,
            'url' => $this->url,
            'message' => $this->messageText,
        ];
    }
}