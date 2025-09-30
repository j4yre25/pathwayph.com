<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\Messages;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $uid = Auth::id();

        // Include both received and sent messages to build threads
        $messages = Messages::with(['application.job.company', 'sender'])
            ->where(function ($q) use ($uid) {
                $q->where('receiver_id', $uid)->orWhere('sender_id', $uid);
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('application_id');

        $threads = $messages->map(function ($group, $applicationId) {
            $last = $group->sortByDesc('created_at')->first();
            $application = $last->application;
            $company = $application?->job?->company;
            $avatarPath = $company?->company_profile_photo_path ?: $company?->company_logo_path;
            return [
                'conversation_id' => (int) $applicationId,
                'company_name' => $company?->company_name,
                'job_title' => $application?->job?->job_title,
                'last_message' => [
                    'id' => $last->id,
                    'body_preview' => str($last->content)->limit(120)->toString(),
                    'created_at' => optional($last->created_at)->diffForHumans(),
                ],
                'unread_count' => $group->whereNull('read_at')->count(),
                'avatar_url' => $avatarPath ? Storage::url($avatarPath) : null,
            ];
        })->values();

        return Inertia::render('Frontend/Messages/Index', [
            'threads' => $threads,
        ]);
    }

    public function show(Request $request, int $conversationId)
    {
        $uid = Auth::id();

        $messages = Messages::with(['sender', 'application.job.company'])
            ->where('application_id', $conversationId)
            ->where(function ($q) use ($uid) {
                $q->where('receiver_id', $uid)->orWhere('sender_id', $uid);
            })
            ->orderBy('created_at')
            ->get();

        $any = $messages->last();
        $application = $any?->application;
        $company = $application?->job?->company;
        $avatarPath = $company?->company_profile_photo_path ?: $company?->company_logo_path;

        // Mark as read for current user
        Messages::where('application_id', $conversationId)
            ->where('receiver_id', $uid)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => Messages::STATUS_READ,
            ]);

        // Latest request_info message
        $lastRequest = $messages->filter(fn($m) => $m->message_type === Messages::TYPE_REQUEST_INFO)->last();

        // You can respond only if employer requested AND you haven't responded yet
        $alreadyResponded = (bool) data_get($lastRequest?->meta, 'request_completed')
            || (count((array) data_get($lastRequest?->meta, 'responses', [])) > 0);

        $canRespond = $lastRequest
            && (int)$lastRequest->receiver_id === (int)$uid
            && !$alreadyResponded;

        return Inertia::render('Frontend/Messages/Show', [
            'thread' => [
                'conversation_id' => $conversationId,
                'company_name' => $company?->company_name,
                'job_title' => $application?->job?->job_title,
                'avatar_url' => $avatarPath ? Storage::url($avatarPath) : null,
            ],
            'messages' => $messages->map(function ($m) use ($uid, $avatarPath) {
                $senderName = $m->sender?->name
                    ?? trim(($m->sender?->hr?->first_name ?? '') . ' ' . ($m->sender?->hr?->last_name ?? '')) ?: 'HR';
                return [
                    'id' => $m->id,
                    'isMine' => (int)$m->sender_id === $uid,
                    'body' => $m->content,
                    'type' => $m->message_type,
                    'created_at' => $m->created_at, // Use raw timestamp for Vue
                    'sender_name' => $senderName,
                    'sender_avatar_url' => $avatarPath ? Storage::url($avatarPath) : null,
                    'meta' => $m->meta ?? [],
                    'status' => $m->status,
                ];
            })->values()->toArray(),
            'request' => $lastRequest ? [
                'id' => $lastRequest->id,
                'canRespond' => (bool)$canRespond,
                'requested' => data_get($lastRequest->meta, 'requested', []),
                'responses' => data_get($lastRequest->meta, 'responses', []),
            ] : null,
        ]);
    }

    public function markAll(Request $request)
    {
        $userId = Auth::id();
        Messages::where('receiver_id', $userId)->whereNull('read_at')->update([
            'read_at' => now(),
            'status' => Messages::STATUS_READ,
        ]);
        return response()->json(['ok' => true]);
    }

    public function markOne(Request $request, $id)
    {
        $msg = Messages::where('id', $id)->where('receiver_id', Auth::id())->firstOrFail();
        $msg->markRead();
        return response()->json(['ok' => true]);
    }

    public function respond(Request $request, int $id)
    {
        $uid = Auth::id();
        $message = Messages::where('id', $id)
            ->where('receiver_id', $uid)
            ->where('message_type', Messages::TYPE_REQUEST_INFO)
            ->firstOrFail();

        $validated = $request->validate([
            'links' => 'array',
            'links.*' => 'nullable|url|max:2048',
            'files' => 'array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $meta = $message->meta ?? [];
        $responses = $meta['responses'] ?? [];

        // Save files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('message-responses', 'public');
                $responses[] = [
                    'kind' => 'file',
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::url($path),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toISOString(),
                    'by' => $uid,
                ];
            }
        }

        // Save links
        foreach (($validated['links'] ?? []) as $url) {
            if (!$url) continue;
            $responses[] = [
                'kind' => 'link',
                'url' => $url,
                'added_at' => now()->toISOString(),
                'by' => $uid,
            ];
        }

        $meta['responses'] = $responses;
        $meta['request_completed'] = true; // mark as completed in meta
        $message->meta = $meta;

        // Do not set status = 'completed' (column does not allow it)
        // Keep status as-is or mark read explicitly
        $message->read_at = now();
        $message->status = Messages::STATUS_READ;
        $message->save();

        // Notify employer with a new message (includes responses for immediate view)
        Messages::create([
            'application_id' => $message->application_id,
            'sender_id'      => Auth::id(),           // graduate
            'receiver_id'    => $message->sender_id,  // employer who requested
            'message_type'   => Messages::TYPE_REQUEST_INFO,
            'content'        => 'Applicant submitted requested information.',
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => $meta,
        ]);

        return back()->with('success', 'Information submitted.');
    }
}