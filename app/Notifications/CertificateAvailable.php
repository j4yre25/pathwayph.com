<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CertificateAvailable extends Notification
{
    use Queueable;

    public $certificatePath;

    public function __construct($certificatePath)
    {
        $this->certificatePath = $certificatePath;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your referral certificate is now available for download.',
            'certificate_path' => $this->certificatePath,
        ];
    }
}