<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Messages;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\PipelineActionExecutor;

class AssessmentController extends Controller
{
    // Send initial exam instructions
    public function sendInstructions(Request $request, PipelineActionExecutor $executor)
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

        $executor->execute([
            'key'     => 'send_exam_instructions',
            'type'    => 'action',
            'payload' => [
                'exam_type'     => $data['exam_type'],
                'date'          => $data['schedule_date'],
                'time'          => $data['schedule_time'],
                'location'      => $data['venue'] ?: $data['link'],
                'requirements'  => $data['requirements'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'receiver_id'   => $data['receiver_id'],
            ],
            // optional: custom message could be added here
        ], $application);

        return response()->json(['message'=>'Exam instructions sent'],201);
    }

    // Reschedule exam
    public function reschedule(Request $request, PipelineActionExecutor $executor)
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

        $executor->execute([
            'key'     => 'reschedule_test',
            'type'    => 'action',
            'payload' => [
                'exam_type' => $data['exam_type'],
                'new_date'  => $data['new_date'],
                'new_time'  => $data['new_time'],
                'reason'    => $data['reason'] ?? null,
                'receiver_id'=> $data['receiver_id'],
            ],
        ], $application);

        return response()->json(['message'=>'Exam reschedule sent'],201);
    }

    // Record test results
    public function recordResult(Request $request, PipelineActionExecutor $executor)
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

        // Log action
        $executor->execute([
            'key'     => 'record_test_results',
            'type'    => 'action',
            'payload' => [
                'exam_type' => $data['exam_type'],
                'score'     => $data['score'],
                'result'    => $data['status'],
                'remarks'   => $data['remarks'] ?? null,
            ],
        ], $application);

        return response()->json(['message'=>'Result recorded','data'=>$result],201);
    }
}