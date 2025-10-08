<?php
namespace App\Notifications;

use App\Models\User;
use App\Models\Graduate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GraduateNeedsApprovalNotification extends Notification
{
    use Queueable;

    public Graduate $graduate;

    public function __construct(Graduate $graduate)
    {
        $this->graduate = $graduate;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Graduate name: {$this->graduate->first_name} {$this->graduate->last_name} has registered and needs approval.",
            'graduate_id' => $this->graduate->id,
            'user_id' => $this->graduate->user_id,
            'status' => 'pending_approval',
        ];
    }
}