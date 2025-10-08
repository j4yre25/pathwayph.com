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

    // ADD: Central action logger
    private function logAction(JobApplication $application, string $actionKey, string $event, array $payload = []): void
    {
        if (!Schema::hasTable('job_application_action_logs')) {
            Log::warning('logAction: table missing');
            return;
        }

        try {
            $cols = Schema::getColumnListing('job_application_action_logs');

            $appCol = in_array('job_application_id', $cols) ? 'job_application_id'
                : (in_array('application_id', $cols) ? 'application_id' : null);
            $actionCol = in_array('action_key', $cols) ? 'action_key'
                : (in_array('action', $cols) ? 'action' : null);

            if (!$appCol || !$actionCol) {
                Log::warning('logAction: required columns missing', ['have'=>$cols]);
                return;
            }

            $row = [
                $appCol    => $application->id,
                $actionCol => $actionKey,
            ];

            if (in_array('user_id', $cols)) {
                $row['user_id'] = Auth::id();
            } elseif (in_array('actor_id', $cols)) {
                $row['actor_id'] = Auth::id();
            }

            if (in_array('event', $cols)) {
                $row['event'] = $event;
            }

            if (in_array('payload', $cols)) {
                $row['payload'] = $payload; // let JSON cast (model) or encode later
            }

            if (in_array('created_at', $cols)) $row['created_at'] = now();
            if (in_array('updated_at', $cols)) $row['updated_at'] = now();

            // Try model first if it exists
            try {
                if (class_exists(\App\Models\JobApplicationActionLog::class)) {
                    \App\Models\JobApplicationActionLog::create($row);
                } else {
                    // Ensure payload encoded if not model
                    if (isset($row['payload']) && !is_string($row['payload'])) {
                        $row['payload'] = json_encode($row['payload']);
                    }
                    DB::table('job_application_action_logs')->insert($row);
                }
            } catch (\Throwable $e) {
                // Fallback raw insert (encode payload)
                if (isset($row['payload']) && !is_string($row['payload'])) {
                    $row['payload'] = json_encode($row['payload']);
                }
                DB::table('job_application_action_logs')->insert($row);
            }
        } catch (\Throwable $e) {
            Log::error('logAction failed', [
                'application_id'=>$application->id,
                'action_key'=>$actionKey,
                'error'=>$e->getMessage(),
            ]);
        }
    }

    public function execute(array $action, JobApplication $application): string
    {
        $action = $this->normalizeAction($action);

        try {
            $type = $action['type'] ?? null;
            $key  = $action['key'] ?? 'unknown';
            $payload = (array)($action['payload'] ?? []);

            /* ------------ Transition (stage move) ------------ */
            if ($type === 'transition' || $key === 'move_next') {
                $from = $application->stage ?: 'applied';
                $to   = strtolower($action['to'] ?? '');

                if (!$to || $to === strtolower($from)) {
                    $this->logAction($application, 'transition_noop', "No stage change ({$from})", $payload);
                    return 'No stage change';
                }

                $application->forceFill(['stage' => $to])->save();

                // --- PATCH: Always sync status for hired/rejected ---
                if ($to === 'hired') {
                    $application->status = 'hired';
                    $application->save();
                    $this->handleHireAction($application);
                    if (Schema::hasColumn('job_applications','archived_at')) {
                        $application->forceFill(['archived_at'=>now()])->save();
                    } elseif (Schema::hasColumn('job_applications','is_archived')) {
                        $application->forceFill(['is_archived'=>1])->save();
                    }
                }
                if ($to === 'rejected') {
                    $application->status = 'rejected';
                    $application->save();
                }
                // --- END PATCH ---

                if (method_exists($application, 'syncStatusFromStage') && $application->syncStatusFromStage()) {
                    $application->save();
                }

                $note = $action['__note'] ?? null;

                if ($to === 'hired') {
                    if (Schema::hasColumn('job_applications','archived_at')) {
                        $application->forceFill(['archived_at'=>now()])->save();
                    } elseif (Schema::hasColumn('job_applications','is_archived')) {
                        $application->forceFill(['is_archived'=>1])->save();
                    }
                    try {
                        $application->status = 'hired';
                        $application->save();
                        $this->handleHireAction($application);
                    } catch (\Throwable $e) {
                        Log::error('handleHireAction failed', [
                            'application_id'=>$application->id,
                            'error'=>$e->getMessage(),
                        ]);
                    }
                }

                // Stage log (existing behavior)
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

                            // Custom hire activity message
                            if ($to === 'hired') {
                                $offer  = JobOffer::where('job_application_id', $application->id)->latest()->first();
                                $start  = $offer && $offer->start_date ? $offer->start_date->format('Y-m-d') : now()->toDateString();
                                $hrName = optional(Auth::user())->name ?: 'HR';
                                $grad   = $application->graduate;
                                $appName = $grad ? trim(($grad->first_name ?? '') . ' ' . ($grad->last_name ?? '')) : 'the applicant';
                                $jobTitle = optional($application->job)->job_title ?: 'the position';
                                $custom = "{$hrName} hired {$appName} for {$jobTitle} starting from {$start}";
                                if (in_array('note',$cols)) {
                                    $row['note'] = $custom;
                                } else {
                                    // fallback: attach to payload via action log helper too
                                    $payload['custom_activity_text'] = $custom;
                                }
                            } elseif (!empty($note) && in_array('note',$cols)) {
                                $row['note'] = $note;
                            }

                            if (in_array('created_at', $cols))  $row['created_at'] = now();
                            DB::table('job_application_stage_logs')->insert($row);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Stage log failed (ignored)', ['err'=>$e->getMessage()]);
                    }
                }

                // Stage-based message
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
                    Log::warning('Pipeline stage message failed', ['err'=>$e->getMessage()]);
                }

                if (!empty($note)) $payload['note'] = $note;

                // ALWAYS log transition
                $this->logAction(
                    $application,
                    "transition_{$from}_to_{$to}",
                    "Stage moved {$from} -> {$to}",
                    $payload
                );

                return "Stage moved to {$to}";
            }

            /* ------------ Non-transition Actions (send_offer, schedule_interview, etc.) ------------ */
            if ($type === 'action') {
                // Attach requested array if present
                if (in_array($key, ['request_info','request_more_info'], true) && !empty($action['requested'])) {
                    $payload['requested'] = array_values(array_unique((array)$action['requested']));
                }

                $event = $action['event'] ?? $this->buildEvent($key, $payload);

                // Central log first
                $this->logAction($application, $key, $event, $payload);

                // Map action key to message type
                $keyToType = [
                    'request_info'           => Messages::TYPE_REQUEST_INFO,
                    'request_more_info'      => Messages::TYPE_REQUEST_INFO,
                    'schedule_interview'     => Messages::TYPE_INTERVIEW_INVITE,
                    'reschedule_interview'   => Messages::TYPE_INTERVIEW_RESCHEDULE,
                    'send_exam_instructions' => Messages::TYPE_EXAM_INSTRUCTIONS,
                    'reschedule_test'        => Messages::TYPE_EXAM_RESCHEDULE,
                    'send_offer'             => Messages::TYPE_OFFER_LETTER,
                    'hire'                   => Messages::TYPE_HIRED,
                    'reject'                 => Messages::TYPE_REJECTED,
                    'reject_withdraw'        => Messages::TYPE_REJECTED,
                ];

                if (isset($keyToType[$key])) {
                    try {
                        $meta = [
                            'action_key'     => $key,
                            'application_id' => $application->id,
                            'link'           => url("/applications/{$application->id}"),
                        ];
                        if ($key === 'request_info' && !empty($payload['requested'])) {
                            $meta['requested'] = $payload['requested'];
                        }

                        $custom = trim((string)($action['custom_message'] ?? ''));
                        $content = $custom !== '' ? $custom : Messages::template($keyToType[$key]);

                        Messages::create([
                            'application_id' => $application->id,
                            'sender_id'      => Auth::id(),
                            'receiver_id'    => $this->resolveApplicantUserId($application),
                            'message_type'   => $keyToType[$key],
                            'content'        => $content,
                            'status'         => Messages::STATUS_UNREAD,
                            'meta'           => $meta,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Action message dispatch failed', ['action'=>$key,'err'=>$e->getMessage()]);
                    }
                }

                // Special case: if action is hire (without explicit transition)
                if ($key === 'hire' && $application->stage !== 'hired') {
                    $prevStage = $application->stage ?: 'applied';
                    $application->stage = 'hired';
                    $application->status = 'hired';
                    $application->save();
                    $this->handleHireAction($application);
                    $this->logAction(
                        $application,
                        "transition_{$prevStage}_to_hired",
                        "Stage moved {$prevStage} -> hired (implicit via hire action)",
                        $payload
                    );
                }
                // PATCH: ensure reject action sets both stage and status
                if (in_array($key, ['reject','reject_withdraw'], true) && $application->stage !== 'rejected') {
                    $prevStage = $application->stage ?: 'applied';
                    $application->stage = 'rejected';
                    $application->status = 'rejected';
                    $application->save();
                    $this->logAction(
                        $application,
                        "transition_{$prevStage}_to_rejected",
                        "Stage moved {$prevStage} -> rejected (implicit via reject action)",
                        $payload
                    );
                }

                return 'Action recorded';
            }

            if ($type === 'noop') {
                $this->logAction($application, 'noop', 'No operation', $payload);
                return 'Kept in current stage';
            }

            $this->logAction($application, $key ?: 'unsupported', 'Unsupported action type', $payload);
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