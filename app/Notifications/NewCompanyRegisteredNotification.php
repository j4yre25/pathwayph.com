<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCompanyRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(public Company $company) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "A new company \"{$this->company->company_name}\" has completed registration.",
            'company_id' => $this->company->id,
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}