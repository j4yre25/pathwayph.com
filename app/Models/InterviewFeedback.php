<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewFeedback extends Model
{
    protected $table = 'interview_feedbacks';
    
    protected $fillable = [
        'application_id',
        'interviewer_id',
        'interviewer_name',
        'rating',
        'strengths',
        'weaknesses',
        'recommendation',
    ];

    public function application() {
        return $this->belongsTo(JobApplication::class,'application_id');
    }

    public function interviewer() {
        return $this->belongsTo(User::class,'interviewer_id');
    }
}