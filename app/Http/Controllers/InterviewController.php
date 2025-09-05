<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Messages;
use App\Models\InterviewFeedback;

class InterviewController extends Controller
{
    public function sendInvitation(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'receiver_id'    => ['required','integer','exists:users,id'],
            'interview_type' => ['required','string','max:50'], // in-person | online | phone
            'date'           => ['required','date'],
            'time'           => ['required','date_format:H:i'],
            'location'       => ['nullable','string','max:255'],
            'link'           => ['nullable','string','max:500'],
            'message'        => ['nullable','string','max:1500'],
        ]);

        $app = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update',$app)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $defaultMsg = "Dear {$app->graduate?->first_name}, we would like to invite you to an interview on {$data['date']} at {$data['time']}. Please confirm your availability.";
        $content = $data['message'] ?: $defaultMsg;

        $msg = Messages::create([
            'application_id' => $data['application_id'],
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => Messages::TYPE_INTERVIEW_INVITE,
            'content'        => $content,
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => [
                'interview_type' => $data['interview_type'],
                'date' => $data['date'],
                'time' => $data['time'],
                'location' => $data['location'],
                'link' => $data['link'],
            ],
        ]);

        return response()->json(['message'=>'Interview invitation sent','data'=>$msg],201);
    }

    public function reschedule(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'receiver_id'    => ['required','integer','exists:users,id'],
            'interview_type' => ['required','string','max:50'],
            'new_date'       => ['required','date'],
            'new_time'       => ['required','date_format:H:i'],
            'reason'         => ['nullable','string','max:500'],
            'message'        => ['nullable','string','max:1500'],
        ]);

        $app = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update',$app)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $defaultMsg = "We apologize, but your interview has been rescheduled to {$data['new_date']} at {$data['new_time']}. Please confirm.";
        $content = $data['message'] ?: $defaultMsg;

        $msg = Messages::create([
            'application_id' => $data['application_id'],
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => Messages::TYPE_INTERVIEW_RESCHEDULE,
            'content'        => $content,
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => [
                'interview_type' => $data['interview_type'],
                'new_date' => $data['new_date'],
                'new_time' => $data['new_time'],
                'reason' => $data['reason'],
            ],
        ]);

        return response()->json(['message'=>'Interview reschedule sent','data'=>$msg],201);
    }

    public function recordFeedback(Request $request)
    {
        $data = $request->validate([
            'application_id'   => ['required','integer','exists:job_applications,id'],
            'rating'           => ['nullable','string','max:50'],
            'strengths'        => ['nullable','string'],
            'weaknesses'       => ['nullable','string'],
            'recommendation'   => ['required','string','in:move_forward,reject,hold'],
        ]);

        $app = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update',$app)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $feedback = InterviewFeedback::create([
            'application_id'   => $data['application_id'],
            'interviewer_id'   => $request->user()->id,
            'interviewer_name' => $request->user()->name,
            'rating'           => $data['rating'],
            'strengths'        => $data['strengths'],
            'weaknesses'       => $data['weaknesses'],
            'recommendation'   => $data['recommendation'],
        ]);

        return response()->json(['message'=>'Interview feedback saved','data'=>$feedback],201);
    }
}