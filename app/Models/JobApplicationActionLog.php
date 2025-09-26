<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicationActionLog extends Model
{
    protected $fillable = [
        'job_application_id','user_id','action_key','event','payload'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function jobApplication()
    {
        return $this->belongsTo(\App\Models\JobApplication::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}