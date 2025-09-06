<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPipelineStage;
use App\Support\StageOwnerResolver;
use App\Services\PipelineStageProvisioner;
use Illuminate\Support\Facades\Log;

class PipelineStageController extends Controller
{
    public function index(StageOwnerResolver $resolver, PipelineStageProvisioner $prov)
    {
        try {
            $ctx = $resolver->resolve();
            $prov->ensure($ctx['type'], $ctx['id']);

            $q = JobPipelineStage::query();
            if ($ctx['type']) {
                $q->where('owner_type',$ctx['type'])->where('owner_id',$ctx['id']);
            } else {
                $q->whereNull('owner_type')->whereNull('owner_id');
            }

            return response()->json(
                $q->orderBy('position')->get(['id','slug','name','position','is_terminal'])
            );
        } catch (\Throwable $e) {
            Log::error('Stage index error', ['e'=>$e->getMessage()]);
            return response()->json(['message'=>'Server error'], 500);
        }
    }

    public function reorder(Request $request, StageOwnerResolver $resolver, PipelineStageProvisioner $prov)
    {
        $ctx = $resolver->resolve();
        if (!$ctx['type']) {
            return response()->json(['message'=>'Cannot reorder global defaults'], 403);
        }

        $prov->ensure($ctx['type'], $ctx['id']);

        $data = $request->validate([
            'order' => 'required|array|min:1',
            'order.*.id' => 'required|integer|distinct|exists:job_pipeline_stages,id',
        ]);

        $current = JobPipelineStage::where('owner_type',$ctx['type'])
            ->where('owner_id',$ctx['id'])
            ->orderBy('position')
            ->pluck('id')->toArray();

        $submitted = collect($data['order'])->pluck('id')->toArray();

        if (count($submitted) !== count($current) ||
            array_diff($submitted,$current) ||
            array_diff($current,$submitted)) {
            return response()->json(['message'=>'Stage set mismatch'], 422);
        }

        foreach ($submitted as $i => $id) {
            JobPipelineStage::where('id',$id)->update(['position'=>$i+1]);
        }

        return response()->json(['message'=>'Order updated']);
    }
}