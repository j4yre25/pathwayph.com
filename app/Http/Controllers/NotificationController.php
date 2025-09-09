<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAll(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['status' => 'ok']);
    }

    public function markOne(Request $request, DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        return response()->json([
            'status' => 'ok',
            'notification' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
            ],
        ]);
    }
}