<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
     protected $fillable = [
        'job_application_id',
        'scheduled_at',
        'location',
        'calendar_event_id', // optional, for calendar integration
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relationship: An interview belongs to a job application
    public function jobApplication()
    {
        return $this->belongsTo(\App\Models\JobApplication::class);
    }
    public function graduates()
    {
        return $this->belongsToMany(\App\Models\Graduate::class, 'interview_graduate', 'interview_id', 'graduate_id');
    }
}
