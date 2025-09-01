<?php

namespace App\Support;

use App\Models\JobApplication;
use App\Notifications\ApplicantPipelineActionNotification;

class ApplicantNotifier
{
    public static function resolve(JobApplication $application)
    {
        // Direct user assigned to application
        if ($application->user) {
            return $application->user;
        }
        // Graduate's user (if graduate linkage used)
        if ($application->graduate && $application->graduate->user) {
            return $application->graduate->user;
        }
        return null;
    }

    public static function notify(JobApplication $application, string $subject, string $body, array $extra = []): void
    {
        if ($recipient = self::resolve($application)) {
            $recipient->notify(
                new ApplicantPipelineActionNotification(
                    $subject,
                    $body,
                    $extra + [
                        'application_id' => $application->id,
                        'stage' => $application->stage,
                    ]
                )
            );
        }
    }

    public static function stageChanged(JobApplication $application, ?string $from, string $to): void
    {
        $subject = 'Application stage updated';
        $body = "Your application moved from ".($from ? ucfirst($from) : 'start')." to ".ucfirst($to).".";
        self::notify($application, $subject, $body, [
            'from' => $from,
            'to'   => $to,
            'type' => 'transition'
        ]);
    }
}