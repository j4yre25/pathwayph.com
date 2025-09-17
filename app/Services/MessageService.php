<?php

namespace App\Services;

use App\Models\Messages;

class MessageService
{
    /**
     * Send a pipeline message to the graduate for a job application.
     */
    public static function sendPipelineMessage($application, int $senderUserId, string $type, array $meta = [], ?string $content = null): Messages
    {
        $receiverUserId = self::resolveApplicantUserId($application);

        return Messages::create([
            'application_id' => $application->id,
            'sender_id'      => $senderUserId,
            'receiver_id'    => $receiverUserId,
            'message_type'   => $type,
            'content'        => $content ?? Messages::template($type),
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => $meta,
        ]);
    }

    private static function resolveApplicantUserId($application): int
    {
        // Try common columns; adjust if your JobApplication uses different naming
        foreach (['user_id','applicant_user_id','graduate_user_id','applicant_id'] as $col) {
            if (isset($application->{$col}) && $application->{$col}) {
                return (int) $application->{$col};
            }
        }
        // Fall back to relation
        if (method_exists($application, 'user') && $application->user) {
            return (int) $application->user->id;
        }
        throw new \RuntimeException('Could not resolve application receiver user id');
    }
}
