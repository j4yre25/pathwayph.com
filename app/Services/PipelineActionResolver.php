<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobPipelineStage;

class PipelineActionResolver
{
    public function forApplication(JobApplication $app): array
    {
        $cfg = config('pipeline') ?? [];
        $stages = $cfg['stages'] ?? [];
        $actions = $cfg['actions'] ?? [];
        if (!$stages || !$actions) return [];

        $slug = strtolower($app->stage ?: 'applied');
        $stageCfg = $stages[$slug] ?? null;
        if (!$stageCfg) return [];

        $companyId = optional($app->job)->company_id;
        $ownerType = $companyId ? 'company' : null;
        $ownerId   = $companyId;

        $ordered = JobPipelineStage::query()
            ->when($ownerType,
                fn($q) => $q->where('owner_type',$ownerType)->where('owner_id',$ownerId),
                fn($q) => $q->whereNull('owner_type')->whereNull('owner_id')
            )
            ->orderBy('position')
            ->get();

        if ($ordered->isEmpty()) return [];

        $bySlug  = $ordered->keyBy(fn($s)=>strtolower($s->slug));
        $current = $bySlug[$slug] ?? null;

        $next = null;
        if ($current && !$current->is_terminal) {
            $next = $ordered
                ->where('position','>',$current->position)
                ->sortBy('position')
                ->first(fn($s)=>$s->active);
        }

        $out = [];
        foreach ($stageCfg['actions'] as $key) {
            $def = $actions[$key] ?? null;
            if (!$def) continue;

            if ($key === 'move_next') {
                if (!$next) continue;
                $out[] = [
                    'key'   => 'move_next',
                    'label' => 'Move to: '.$next->name,
                    'type'  => 'transition',
                    'to'    => strtolower($next->slug),
                    'icon'  => $def['icon'] ?? null,
                ];
                continue;
            }

            $out[] = [
                'key'   => $key,
                'label' => $def['label'],
                'type'  => $def['type'],
                'icon'  => $def['icon'] ?? null,
                'to'    => $def['to'] ?? null,
                'event' => $def['event'] ?? null,
                'modal' => $def['modal'] ?? null,
            ];
        }
        return $out;
    }
}