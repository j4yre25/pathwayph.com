<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Services\PipelineActionResolver;
use App\Services\PipelineActionExecutor;
use Illuminate\Support\Facades\Log;

class ApplicationActionController extends Controller
{
    public function index(JobApplication $application, PipelineActionResolver $resolver)
    {
        return response()->json([
            'stage' => $application->stage,
            'actions' => $resolver->forApplication($application),
        ]);
    }

    public function perform(Request $request, JobApplication $application, PipelineActionResolver $resolver, PipelineActionExecutor $executor)
    {
        // Accept extra fields needed for logging (requested items, custom message, payload)
        $data = $request->validate([
            'action'          => 'required|string',
            'to'              => 'nullable|string',
            'note'            => 'nullable|string|max:1000',
            'requested'       => 'sometimes|array',
            'requested.*'     => 'nullable|string',
            'custom_message'  => 'sometimes|nullable|string|max:2000',
            'payload'         => 'sometimes|array',
        ]);

        try {
            $available = collect($resolver->forApplication($application))->keyBy('key');
            $actionKey = strtolower($data['action']);
            $action    = $available->get($actionKey) ?? ['key' => $actionKey];

            // Allow client-provided 'to' only for move_next/transition when definition has no target
            if ($request->filled('to') && ($action['key'] === 'move_next' || ($action['type'] ?? '') === 'transition') && empty($action['to'])) {
                $action['to'] = $request->string('to')->lower();
            }

            // Pass note through for executors that support it
            if (!empty($data['note'])) {
                $action['__note'] = $data['note'];
            }

            // Merge payload from request with any pre-defined payload
            $payload = (array)($action['payload'] ?? []);
            if (isset($data['payload']) && is_array($data['payload'])) {
                $payload = array_replace($payload, $data['payload']);
            }

            // Special support for "request_more_info" / "request_info"
            if (isset($data['requested'])) {
                $payload['requested'] = array_values(array_unique(array_filter((array)$data['requested'])));
                $action['requested']  = $payload['requested']; // also expose at root for executor normalization
            }
            if (array_key_exists('custom_message', $data)) {
                $payload['custom_message'] = $data['custom_message'];
            }

            $action['payload'] = $payload;

            // Ensure a sensible default type for known action keys (so executor logs it)
            $knownActionKeys = [
                'request_info','request_more_info',
                'schedule_interview','reschedule_interview','record_interview_feedback',
                'send_exam_instructions','record_test_results','reschedule_test',
                'send_offer','hire','reject','reject_withdraw',
            ];
            if (!isset($action['type']) && in_array($action['key'], $knownActionKeys, true)) {
                $action['type'] = 'action';
            }

            $msg = $executor->execute($action, $application);

            // Keep legacy sync if present
            if (method_exists($application, 'syncStatusFromStage')) {
                if ($application->syncStatusFromStage()) {
                    $application->save();
                }
            }

            return response()->json([
                'message' => $msg,
                'stage'   => $application->stage,
                'actions' => $resolver->forApplication($application),
            ]);
        } catch (\Throwable $e) {
            Log::error('ApplicationAction perform failed', [
                'application_id' => $application->id,
                'action'         => $request->action,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Server error',
                'error'   => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}