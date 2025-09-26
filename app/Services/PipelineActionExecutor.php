<?php

namespace App\Services;

use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Messages;

class PipelineActionExecutor
{
    private function buildEvent(string $key, array $payload): string
    {
        $dt = null;
        $fmt = fn(Carbon $c) => $c->format('M d, Y').' at '.$c->format('h:i A');

        // try common datetime keys
        foreach (['scheduled_at','schedule_at','interview_at','interview_datetime','datetime'] as $k) {
            if (!empty($payload[$k])) { try { $dt = Carbon::parse($payload[$k]); } catch (\Throwable $e) {} }
        }
        if (!$dt && !empty($payload['date']) && !empty($payload['time'])) {
            try { $dt = Carbon::parse($payload['date'].' '.$payload['time']); } catch (\Throwable $e) {}
        }

        $loc = !empty($payload['location']) ? ' ('.$payload['location'].')' : '';

        return match ($key) {
            'request_more_info','request_info' => 'Requested more info'.(
                !empty($payload['requested']) && is_array($payload['requested'])
                    ? ': '.implode(', ', array_filter($payload['requested']))
                    : ''
            ),
            'schedule_interview'   => 'Interview scheduled'.($dt ? ' for '.$fmt($dt) : '').$loc,
            'reschedule_interview' => 'Interview rescheduled'.($dt ? ' to '.$fmt($dt) : '').$loc,
            'record_interview_feedback' => 'Interview feedback recorded'.(!empty($payload['feedback']) ? ': '.$payload['feedback'] : ''),
            'send_exam_instructions'    => 'Exam instructions sent'.($dt ? ' for '.$fmt($dt) : ''),
            'record_test_results'       => 'Test results recorded'.(
                isset($payload['score']) ? ': '.$payload['score'] : (isset($payload['result']) ? ': '.$payload['result'] : '')
            ),
            'reschedule_test' => 'Exam rescheduled'.($dt ? ' to '.$fmt($dt) : ''),
            'send_offer'      => 'Offer sent'.(!empty($payload['offered_salary']) ? ' (salary '.$payload['offered_salary'].')' : ''),
            'hire'            => 'Marked as hired',
            'reject','reject_withdraw' => 'Application rejected'.(!empty($payload['reason']) ? ': '.$payload['reason'] : ''),
            default => ucfirst(str_replace('_',' ', $key)),
        };
    }
    public function execute(array $action, JobApplication $application): string
    {
        try {
            $type = $action['type'];

            if ($type === 'transition' || $action['key'] === 'move_next') {
                $from = $application->stage ?: 'applied';
                $to   = strtolower($action['to'] ?? '');

                if (!$to || $to === strtolower($from)) {
                    return 'No stage change';
                }

                $application->forceFill(['stage' => $to])->save();

                // Auto-sync status based on new stage
                if (method_exists($application, 'syncStatusFromStage')) {
                    if ($application->syncStatusFromStage()) {
                        $application->save();
                    }
                }

                $note = $action['__note'] ?? null;

                // Archive automatically if moving to hired
                if ($to === 'hired') {
                    if (Schema::hasColumn('job_applications','archived_at')) {
                        $application->forceFill(['archived_at'=>now()])->save();
                    } elseif (Schema::hasColumn('job_applications','is_archived')) {
                        $application->forceFill(['is_archived'=>1])->save();
                    }
                }

                // Stage log with optional note
                if (Schema::hasTable('job_application_stage_logs')) {
                    try {
                        $cols = Schema::getColumnListing('job_application_stage_logs');
                        $row = [
                            'job_application_id' => $application->id,
                            'from_stage' => $from,
                            'to_stage'   => $to,
                        ];
                        if (in_array('changed_by', $cols))  $row['changed_by'] = Auth::id();
                        if ($note && in_array('note',$cols)) $row['note'] = $note;
                        if (in_array('created_at', $cols))  $row['created_at'] = now();
                        DB::table('job_application_stage_logs')->insert($row);
                    } catch (\Throwable $e) {
                        Log::warning('Stage log failed (ignored)', ['err'=>$e->getMessage()]);
                    }
                }

                // Send message based on new stage
                try {
                    $stageToType = [
                        'interview'  => Messages::TYPE_INTERVIEW_INVITE,
                        'assessment' => Messages::TYPE_EXAM_INSTRUCTIONS,
                        'exam'       => Messages::TYPE_EXAM_INSTRUCTIONS,
                        'offer'      => Messages::TYPE_OFFER_LETTER,
                        'offered'    => Messages::TYPE_OFFER_LETTER,
                        'hired'      => Messages::TYPE_HIRED,
                        'rejected'   => Messages::TYPE_REJECTED,
                        'request_info' => Messages::TYPE_REQUEST_INFO,
                    ];
                    if (isset($stageToType[$to])) {
                        MessageService::sendPipelineMessage(
                            $application,
                            Auth::id(),
                            $stageToType[$to],
                            [
                                'from_stage' => $from,
                                'to_stage'   => $to,
                                'note'       => $note,
                                'application_id' => $application->id,
                                'link' => url("/applications/{$application->id}"),
                            ]
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('Pipeline stage message failed (ignored)', ['err'=>$e->getMessage()]);
                }

                return "Stage moved to {$to}";
            }

           if (($action['type'] ?? null) === 'action') {
                // Log action with payload + event
                if (Schema::hasTable('job_application_action_logs')) {
                    try {
                        $key = $action['key'] ?? '';
                        $payload = $action['payload'] ?? [];

                        // include requested items for request_info
                        if (in_array($key, ['request_info','request_more_info']) && !empty($action['requested'])) {
                            $payload['requested'] = array_values(array_unique((array)$action['requested']));
                        }

                        $event = $action['event'] ?? $this->buildEvent($key, (array)$payload);

                        DB::table('job_application_action_logs')->insert([
                            'job_application_id'=>$application->id,
                            'user_id'=>Auth::id(),
                            'action_key'=>$key,
                            'event'=>$event,
                            'payload'=>json_encode($payload),
                            'created_at'=>now(),
                            'updated_at'=>now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Action log failed (ignored)', ['err'=>$e->getMessage()]);
                    }
                }

                // Map action keys to message types
                $keyToType = [
                    'request_info'      => Messages::TYPE_REQUEST_INFO,
                    'schedule_interview'     => Messages::TYPE_INTERVIEW_INVITE,
                    'reschedule_interview'   => Messages::TYPE_INTERVIEW_RESCHEDULE,
                    'send_exam_instructions' => Messages::TYPE_EXAM_INSTRUCTIONS,
                    'reschedule_exam'        => Messages::TYPE_EXAM_RESCHEDULE,
                    'send_offer'             => Messages::TYPE_OFFER_LETTER,
                    'hire'                   => Messages::TYPE_HIRED,
                    'reject'                 => Messages::TYPE_REJECTED,
                    'reject_withdraw'        => Messages::TYPE_REJECTED,
                ];

                $key = $action['key'] ?? null;
                if ($key && isset($keyToType[$key])) {
                    $type = $keyToType[$key];

                    // Build meta, include requested items for request_more_info
                    $meta = [
                        'action_key'     => $key,
                        'application_id' => $application->id,
                        'link'           => url("/applications/{$application->id}"),
                    ];
                    if ($type === Messages::TYPE_REQUEST_INFO && !empty($action['requested']) && is_array($action['requested'])) {
                        $meta['requested'] = array_values(array_unique($action['requested']));
                    }

                    // Use custom note if provided; else use the template for that type
                    $custom = trim((string)($action['custom_message'] ?? ''));
                    $content = $custom !== '' ? $custom : Messages::template($type);

                    // Create the message
                    Messages::create([
                        'application_id' => $application->id,
                        'sender_id'      => Auth::id(),
                        'receiver_id'    => $this->resolveApplicantUserId($application),
                        'message_type'   => $type,
                        'content'        => $content,
                        'status'         => Messages::STATUS_UNREAD,
                        'meta'           => $meta,
                    ]);
                }

                return 'Action recorded';
            }

            if ($type === 'noop') {
                return 'Kept in current stage';
            }

            return 'Unsupported action type';
        } catch (\Throwable $e) {
            Log::error('Pipeline executor error', [
                'application_id'=>$application->id,
                'action'=>$action['key'] ?? null,
                'error'=>$e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function resolveApplicantUserId(JobApplication $application): int
    {
        foreach (['user_id','applicant_user_id','graduate_user_id','applicant_id'] as $col) {
            if (!empty($application->{$col})) return (int)$application->{$col};
        }
        if (method_exists($application, 'user') && $application->user) return (int)$application->user->id;
        throw new \RuntimeException('Could not resolve applicant user id');
    }
}