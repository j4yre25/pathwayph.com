<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Messages;
use App\Models\InterviewFeedback;
use App\Services\PipelineActionExecutor;

class InterviewController extends Controller
{
    public function sendInvitation(Request $request, PipelineActionExecutor $executor)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'receiver_id'    => ['required','integer','exists:users,id'],
            'interview_type' => ['required','string','max:50'],
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

        $executor->execute([
            'key'            => 'schedule_interview',
            'type'           => 'action',
            'custom_message' => $data['message'] ?? null,
            'payload'        => [
                'interview_type' => $data['interview_type'],
                'date'           => $data['date'],
                'time'           => $data['time'],
                'location'       => $data['location'] ?: $data['link'],
                'receiver_id'    => $data['receiver_id'],
            ],
        ], $app);

        return response()->json(['message'=>'Interview invitation sent'],201);
    }

    public function reschedule(Request $request, PipelineActionExecutor $executor)
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

        $executor->execute([
            'key'            => 'reschedule_interview',
            'type'           => 'action',
            'custom_message' => $data['message'] ?? null,
            'payload'        => [
                'interview_type' => $data['interview_type'],
                'new_date'       => $data['new_date'],
                'new_time'       => $data['new_time'],
                'reason'         => $data['reason'] ?? null,
                'receiver_id'    => $data['receiver_id'],
            ],
        ], $app);

        return response()->json(['message'=>'Interview reschedule sent'],201);
    }

    public function recordFeedback(Request $request, PipelineActionExecutor $executor)
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

        // Log the action
        $executor->execute([
            'key'     => 'record_interview_feedback',
            'type'    => 'action',
            'payload' => [
                'rating'         => $data['rating'],
                'strengths'      => $data['strengths'],
                'weaknesses'     => $data['weaknesses'],
                'recommendation' => $data['recommendation'],
            ],
        ], $app);

        return response()->json(['message'=>'Interview feedback saved','data'=>$feedback],201);
    }
}