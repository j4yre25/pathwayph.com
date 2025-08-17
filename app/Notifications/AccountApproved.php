<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Account Approved',
            'body' => 'Your account has been approved by the admin. You can now access the full website.',
        ];
    }

    /**
     * (Optional) Get the array representation of the notification.
     * This is used for broadcasting or other channels.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Account Approved',
            'body' => 'Your account has been approved by the admin.',
        ];
    }
}
