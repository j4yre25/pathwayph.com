<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPipelineStage extends Model
{
    protected $fillable = [
        'owner_type','owner_id','name','slug','position','is_terminal','active','is_default'
    ];

    public function scopeForOwner($q, string $type = null, $id = null) {
        if ($type && $id) {
            return $q->where('owner_type', $type)->where('owner_id', $id);
        }
        return $q->whereNull('owner_type')->whereNull('owner_id'); // global
    }
}