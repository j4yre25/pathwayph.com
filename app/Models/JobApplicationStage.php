<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicationStage extends Model
{
    protected $fillable = [
        'job_application_id',
        'stage',
        'changed_at',
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }
}