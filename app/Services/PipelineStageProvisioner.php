<?php

namespace App\Services;

use App\Models\JobPipelineStage;
use Illuminate\Support\Facades\DB;

class PipelineStageProvisioner
{
    public function ensure(?string $type, ?int $id): void
    {
        if (!$type || !$id) return;
        if (JobPipelineStage::where('owner_type',$type)->where('owner_id',$id)->exists()) return;

        $defaults = JobPipelineStage::whereNull('owner_type')->whereNull('owner_id')
            ->orderBy('position')->get();

        DB::transaction(function () use ($defaults,$type,$id) {
            foreach ($defaults as $d) {
                JobPipelineStage::create([
                    'owner_type'=>$type,
                    'owner_id'=>$id,
                    'slug'=>$d->slug,
                    'name'=>$d->name,
                    'position'=>$d->position,
                    'is_terminal'=>$d->is_terminal,
                    'active'=>$d->active,
                    'is_default'=>$d->is_default
                ]);
            }
        });
    }
}