<?php

namespace App\Services;

use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Messages;
use App\Models\JobOffer;
use App\Models\Experience;
use App\Models\Graduate;

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

    private function normalizeAction(array $action): array
    {
        $a = $action;

        // accept "action" as key
        if (!isset($a['key']) && isset($a['action'])) {
            $a['key'] = $a['action'];
        }

        // guess type if missing
        if (!isset($a['type'])) {
            $actionKeys = [
                'request_info','request_more_info',
                'schedule_interview','reschedule_interview','record_interview_feedback',
                'send_exam_instructions','record_test_results','reschedule_test',
                'send_offer','hire','reject','reject_withdraw'
            ];
            if (!empty($a['key']) && in_array($a['key'], $actionKeys, true)) {
                $a['type'] = 'action';
            }
        }

        // ensure payload array carries modal fields
        if (!isset($a['payload']) || !is_array($a['payload'])) {
            $a['payload'] = [];
        }
        if (!empty($a['requested']) && is_array($a['requested'])) {
            $a['payload']['requested'] = array_values(array_unique($a['requested']));
        }
        if (!empty($a['custom_message'])) {
            $a['payload']['custom_message'] = (string)$a['custom_message'];
        }

        return $a;
    }

    public function execute(array $action, JobApplication $application): string
    {
        $action = $this->normalizeAction($action); 

        try {
            $type = $action['type'];

            if ($type === 'transition' || ($action['key'] ?? null) === 'move_next') {
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

                 // Ensure application.status reflects hired and perform hire side-effects
                if ($to === 'hired') {
                    try {
                        $application->status = 'hired';
                        $application->save();
                        // Perform side-effects: update graduate, add experience, etc.
                        $this->handleHireAction($application);
                    } catch (\Throwable $e) {
                        Log::error('handleHireAction failed', [
                            'application_id' => $application->id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Stage log with optional note
                if (Schema::hasTable('job_application_stage_logs')) {
                    try {
                        if (!$this->isDuplicateStageLog($application->id, $from, $to)) {
                            $cols = Schema::getColumnListing('job_application_stage_logs');
                            $row = [
                                'job_application_id' => $application->id,
                                'from_stage' => $from,
                                'to_stage'   => $to,
                            ];
                            if (in_array('changed_by', $cols))  $row['changed_by'] = Auth::id();
                            if (!empty($note) && in_array('note',$cols)) $row['note'] = $note;
                            if (in_array('created_at', $cols))  $row['created_at'] = now();
                            DB::table('job_application_stage_logs')->insert($row);
                        }
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
                        $key     = $action['key'] ?? '';
                        $payload = $action['payload'] ?? [];

                        // include requested items for request_info
                        if (in_array($key, ['request_info','request_more_info'], true) && !empty($action['requested'])) {
                            $payload['requested'] = array_values(array_unique((array)$action['requested']));
                        }

                        $event = $action['event'] ?? $this->buildEvent($key, (array)$payload);

                        // Insert only the columns that exist (schema-agnostic)
                        $cols   = Schema::getColumnListing('job_application_action_logs');
                        $row    = [];

                        // application foreign key
                        $appCol = in_array('job_application_id', $cols) ? 'job_application_id'
                            : (in_array('application_id', $cols) ? 'application_id' : null);
                        if ($appCol) $row[$appCol] = $application->id;

                        // user foreign key
                        $userCol = in_array('user_id', $cols) ? 'user_id'
                                : (in_array('actor_id', $cols) ? 'actor_id' : null);
                        if ($userCol) $row[$userCol] = Auth::id();

                        // action key/name
                        $actionCol = in_array('action_key', $cols) ? 'action_key'
                                : (in_array('action', $cols) ? 'action' : null);
                        if ($actionCol) $row[$actionCol] = $key;

                        // optional fields
                        if (in_array('event', $cols)) {
                            $row['event'] = $event;
                        }
                        if (in_array('payload', $cols)) {
                            $row['payload'] = is_string($payload) ? $payload : json_encode($payload);
                        }
                        if (in_array('created_at', $cols)) $row['created_at'] = now();
                        if (in_array('updated_at', $cols)) $row['updated_at'] = now();

                        // Only insert if we have the minimal FK + action
                        if ($appCol && $actionCol) {
                            DB::table('job_application_action_logs')->insert($row);
                        } else {
                            Log::warning('Action log skipped: missing essential columns', compact('cols','row'));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Action log failed (ignored)', ['err' => $e->getMessage()]);
                    }
                }

                // Map action keys to message types
                $keyToType = [
                    'request_info'           => Messages::TYPE_REQUEST_INFO,
                    'request_more_info'      => Messages::TYPE_REQUEST_INFO, // added
                    'schedule_interview'     => Messages::TYPE_INTERVIEW_INVITE,
                    'reschedule_interview'   => Messages::TYPE_INTERVIEW_RESCHEDULE,
                    'send_exam_instructions' => Messages::TYPE_EXAM_INSTRUCTIONS,
                    'reschedule_test'        => Messages::TYPE_EXAM_RESCHEDULE, // align with config key
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

    /**
     * Create experience and update graduate when an application is hired.
     *
     * Call this after you persist the "hired" stage/status on the JobApplication.
     *
     * @param  JobApplication  $application
     * @return void
     */
    public function handleHireAction(JobApplication $application): void
    {
        if (! $application || ! $application->id) {
            Log::warning('handleHireAction called with invalid application', ['application'=>$application]);
            return;
        }

        DB::beginTransaction();
        try {
            // Ensure relations
            $application->loadMissing(['job.company', 'job.locations', 'graduate']);

            Log::info('handleHireAction start', ['application_id' => $application->id]);

            // find the most relevant offer for this application
            $offer = JobOffer::where('job_application_id', $application->id)->latest()->first();

            // determine start date (prefer offer.start_date if available)
            $startDate = $offer && $offer->start_date ? $offer->start_date->toDateString() : now()->toDateString();

            // update application status to 'hired'
            $application->status = 'hired';
            $application->save();
            Log::info('Application status set to hired', ['application_id' => $application->id]);

            $graduate = $application->graduate;
            if (! $graduate) {
                Log::warning('handleHireAction: no graduate relation found', ['application_id' => $application->id]);
                DB::commit();
                return;
            }

            // determine job title and company name (prefer offer values)
            $jobTitle = $offer && !empty($offer->job_title) ? $offer->job_title : optional($application->job)->job_title;
            $companyName = optional($application->job->company)->company_name ?? null;

            // update graduate current job title and employment_status
            $gradUpdates = [];
            if ($jobTitle) $gradUpdates['current_job_title'] = $jobTitle;
            $gradUpdates['employment_status'] = 'employed';
            if (!empty($gradUpdates)) {
                $graduate->update($gradUpdates);
                Log::info('Graduate updated', ['graduate_id' => $graduate->id, 'updates' => $gradUpdates]);
            }

            /* Experience creation/update is currently commented out.
               Only update graduate employment_status and current_job_title as requested.

            // mark any existing current experiences as not current
            Experience::where('graduate_id', $graduate->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'end_date' => $startDate,
                ]);
            Log::info('Previous experiences marked not current', ['graduate_id' => $graduate->id]);

            // avoid creating duplicate current experience - use updateOrCreate
            $experienceData = [
                'graduate_id'  => $graduate->id,
                'title'        => $jobTitle,
                'company_name' => $companyName,
            ];

            $jobDescription = optional($application->job)->job_description ?? ($offer->body ?? null);
            $address = null;
            try {
                $firstLocation = optional($application->job)->locations()->first();
                if ($firstLocation) $address = $firstLocation->address ?? null;
            } catch (\Throwable $e) {
                $address = null;
            }

            $attrs = [
                'description' => $jobDescription,
                'start_date'  => $startDate,
                'end_date'    => null,
                'is_current'  => true,
                'address'     => $address,
            ];

            $exp = Experience::updateOrCreate($experienceData, $attrs);
            Log::info('Experience created/updated', ['experience_id' => $exp->id ?? null, 'graduate_id' => $graduate->id]);
            */

            Log::info('Experience creation skipped (commented) - only updating graduate fields', ['graduate_id' => $graduate->id]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('handleHireAction failed for application '.$application->id.': '.$e->getMessage(), [
                'application_id' => $application->id,
                'exception' => $e,
            ]);
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

    private function isDuplicateStageLog(int $applicationId, string $from, string $to, int $windowSeconds = 15): bool
    {
        if (!Schema::hasTable('job_application_stage_logs')) return false;

        $last = DB::table('job_application_stage_logs')
            ->where('job_application_id', $applicationId)
            ->orderByDesc('created_at')
            ->limit(1)
            ->first();

        if (!$last) return false;

        // Resolve "to" and "from" of last entry regardless of schema (id vs name)
        $slugById = [];
        if (Schema::hasTable('job_pipeline_stages')) {
            $slugById = DB::table('job_pipeline_stages')->pluck('slug', 'id')->toArray();
        }

        $lastTo = null;
        if (isset($last->to_stage) && $last->to_stage) $lastTo = strtolower((string)$last->to_stage);
        elseif (isset($last->to_stage_id) && $last->to_stage_id && isset($slugById[$last->to_stage_id])) $lastTo = strtolower($slugById[$last->to_stage_id]);

        $lastFrom = null;
        if (isset($last->from_stage) && $last->from_stage) $lastFrom = strtolower((string)$last->from_stage);
        elseif (isset($last->from_stage_id) && $last->from_stage_id && isset($slugById[$last->from_stage_id])) $lastFrom = strtolower($slugById[$last->from_stage_id]);

        $sameTransition = ($lastFrom === strtolower($from)) && ($lastTo === strtolower($to));
        if (!$sameTransition) return false;

        $lastAt = isset($last->created_at) ? Carbon::parse($last->created_at) : null;
        if (!$lastAt) return false;

        return $lastAt->greaterThanOrEqualTo(now()->subSeconds($windowSeconds));
    }
}