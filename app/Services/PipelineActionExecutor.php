<?php

namespace App\Services;

use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PipelineActionExecutor
{
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

                // (Temporarily skip notifications to isolate error)
                return "Stage moved to {$to}";
            }

            if ($type === 'action') {
                if (Schema::hasTable('job_application_action_logs')) {
                    try {
                        DB::table('job_application_action_logs')->insert([
                            'job_application_id'=>$application->id,
                            'user_id'=>Auth::id(),
                            'action_key'=>$action['key'],
                            'event'=>$action['event'] ?? null,
                            'payload'=>null,
                            'created_at'=>now(),
                            'updated_at'=>now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Action log failed (ignored)', ['err'=>$e->getMessage()]);
                    }
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
}