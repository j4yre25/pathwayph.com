<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicantPipelineActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $subjectLine,
        public string $bodyLine,
        public array $data = []
    ) {}

    public function via($notifiable): array
    {
        return ['database','mail']; // remove 'mail' if you only want in-app
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectLine)
            ->line($this->bodyLine)
            ->line('Application ID: '.$this->data['application_id'])
            ->when(isset($this->data['from']), fn($m) => $m->line('From Stage: '.$this->data['from']))
            ->when(isset($this->data['to']), fn($m) => $m->line('To Stage: '.$this->data['to']));
    }

    public function toArray($notifiable): array
    {
        return $this->data + [
            'subject' => $this->subjectLine,
            'message' => $this->bodyLine,
        ];
    }
}