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

    public function perform(
        Request $request,
        JobApplication $application,
        PipelineActionResolver $resolver,
        PipelineActionExecutor $executor
    ) {
        $request->validate([
            'action' => 'required|string',
            'to' => 'nullable|string',
            'note' => 'nullable|string|max:1000', 
        ]);

        try {
            $available = collect($resolver->forApplication($application))->keyBy('key');
            $action = $available->get($request->action);

            if (!$action) {
                return response()->json(['message'=>'Unknown action'], 422);
            }

            // Allow client-provided 'to' only if move_next missing 'to'
            if ($request->filled('to') && $action['key']==='move_next' && empty($action['to'])) {
                $action['to'] = $request->string('to')->lower();
            }

            if ($request->filled('note')) {
                $action['__note'] = $request->string('note')->value();
            }

            $msg = $executor->execute($action, $application);

            if (method_exists($application,'syncStatusFromStage')) {
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
                'application_id'=>$application->id,
                'action'=>$request->action,
                'error'=>$e->getMessage(),
                'trace'=>$e->getTraceAsString(),
            ]);
            return response()->json([
                'message'=>'Server error',
                'error'=>app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}