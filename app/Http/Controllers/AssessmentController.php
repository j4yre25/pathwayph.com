<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Messages;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    // Send initial exam instructions
    public function sendInstructions(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'exam_type'      => ['required','string','max:120'],
            'schedule_date'  => ['required','date'],
            'schedule_time'  => ['required','date_format:H:i'],
            'venue'          => ['nullable','string','max:255'],
            'link'           => ['nullable','string','max:500'],
            'requirements'   => ['nullable','string','max:500'],
            'notes'          => ['nullable','string','max:1000'],
            'receiver_id'    => ['required','integer','exists:users,id'],
        ]);

        $application = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update', $application)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $contentLines = [
            "Exam Type: {$data['exam_type']}",
            "Schedule: ".date('Y-m-d', strtotime($data['schedule_date'])) . ' ' . $data['schedule_time'],
        ];
        if (!empty($data['venue'])) $contentLines[] = "Venue: {$data['venue']}";
        if (!empty($data['link'])) $contentLines[] = "Link: {$data['link']}";
        if (!empty($data['requirements'])) $contentLines[] = "Requirements: {$data['requirements']}";
        if (!empty($data['notes'])) $contentLines[] = "Notes: {$data['notes']}";

        $msg = Messages::create([
            'application_id' => $data['application_id'],
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => 'exam_instructions',
            'content'        => implode("\n", $contentLines),
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => [
                'exam_type' => $data['exam_type'],
                'schedule_date' => $data['schedule_date'],
                'schedule_time' => $data['schedule_time'],
                'venue' => $data['venue'] ?? null,
                'link' => $data['link'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        ]);

        return response()->json(['message'=>'Exam instructions sent','data'=>$msg],201);
    }

    // Reschedule (creates another message flagged exam_reschedule)
    public function reschedule(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'exam_type'      => ['required','string','max:120'],
            'new_date'       => ['required','date'],
            'new_time'       => ['required','date_format:H:i'],
            'reason'         => ['nullable','string','max:500'],
            'receiver_id'    => ['required','integer','exists:users,id'],
        ]);

        $application = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update', $application)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $lines = [
            "RESCHEDULED EXAM",
            "Exam Type: {$data['exam_type']}",
            "New Schedule: ".date('Y-m-d', strtotime($data['new_date'])) . ' ' . $data['new_time'],
        ];
        if (!empty($data['reason'])) $lines[] = "Reason: {$data['reason']}";

        $msg = Messages::create([
            'application_id' => $data['application_id'],
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => 'exam_reschedule',
            'content'        => implode("\n",$lines),
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => [
                'exam_type' => $data['exam_type'],
                'new_date'  => $data['new_date'],
                'new_time'  => $data['new_time'],
                'reason'    => $data['reason'] ?? null,
            ]
        ]);

        return response()->json(['message'=>'Exam reschedule sent','data'=>$msg],201);
    }

    // Record test results
    public function recordResult(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'exam_type'      => ['required','string','max:120'],
            'score'          => ['nullable','numeric'],
            'status'         => ['required','string', Rule::in(['passed','failed','pending'])],
            'remarks'        => ['nullable','string','max:1000'],
        ]);

        $application = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update', $application)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $result = TestResult::create([
            'application_id' => $data['application_id'],
            'exam_type'      => $data['exam_type'],
            'score'          => $data['score'],
            'status'         => $data['status'],
            'remarks'        => $data['remarks'],
            'recorded_by'    => $request->user()->id,
        ]);

        return response()->json(['message'=>'Result recorded','data'=>$result],201);
    }
}