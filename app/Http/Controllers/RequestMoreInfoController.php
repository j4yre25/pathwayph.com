<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Messages;
use App\Models\JobApplication;

class RequestMoreInfoController extends Controller
{
    // Return allowed request types + default templates
    public function types(Request $request)
    {
        $out = collect(Messages::$ALLOWED_REQUESTS)->map(fn($type)=>[
            'value' => $type,
            'label' => ucwords(str_replace(['_','transcript Of'], [' ','Transcript of'], $type)),
            'template' => Messages::template($type),
        ])->values();

        return response()->json(['data'=>$out]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'receiver_id'    => ['required','integer','exists:users,id'],
            'request_type'   => ['required','string', Rule::in(Messages::$ALLOWED_REQUESTS)],
            'content'        => ['nullable','string','max:1000'],
        ]);

        $application = JobApplication::findOrFail($data['application_id']);

        if ($request->user()->cannot('update', $application)) {
            return $this->respond($request, false, 'Forbidden', null, 403);
        }

        $finalContent = $data['content'] ?: Messages::template($data['request_type']);

        $row = Messages::create([
            'application_id' => $data['application_id'],
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => 'request_info',
            'content'        => $finalContent,
            'status'         => Messages::STATUS_UNREAD,
        ]);

        return $this->respond($request, true, 'Request sent', $row, 201);
    }

    /**
     * List request_info messages.
     * Filters: application_id, receiver_id, status
     */
    public function index(Request $request)
    {
        $q = Messages::query()->requestInfo()
            ->when($request->filled('application_id'), fn($qq)=>$qq
                ->where('application_id',$request->integer('application_id')))
            ->when($request->filled('receiver_id'), fn($qq)=>$qq
                ->where('receiver_id',$request->integer('receiver_id')))
            ->when($request->filled('status'), fn($qq)=>$qq
                ->where('status',$request->get('status')));

        $items = $q->orderByDesc('created_at')->get();

        if ($request->expectsJson()) {
            return response()->json(['data'=>$items]);
        }
        return back(); // or Inertia page if you build one
    }

    /**
     * Mark a single request_info message as completed (compliance done).
     */
    public function complete(Messages $requestMoreInfo, Request $request)
    {
        if ($requestMoreInfo->message_type !== 'request_info') {
            return $this->respond($request, false, 'Not a request_info message', null, 422);
        }
        $requestMoreInfo->markCompleted();
        return $this->respond($request, true, 'Request marked as completed', $requestMoreInfo);
    }

    /**
     * Mark as read (optional helper).
     */
    public function markRead(Messages $requestMoreInfo, Request $request)
    {
        if ($requestMoreInfo->message_type !== 'request_info') {
            return $this->respond($request, false, 'Not a request_info message', null, 422);
        }
        if ($requestMoreInfo->status === Messages::STATUS_UNREAD) {
            $requestMoreInfo->status = Messages::STATUS_READ;
            $requestMoreInfo->save();
        }
        return $this->respond($request, true, 'Marked read', $requestMoreInfo);
    }

    protected function respond(Request $request, bool $ok, string $msg, $data=null, int $status=200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success'=>$ok,
                'message'=>$msg,
                'data'=>$data
            ], $status);
        }
        return redirect()->back()->with('flash.banner', $msg)->with('flash.bannerStyle', $ok?'success':'danger');
    }
}