<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobApplicationStageLog extends Model
{
    protected $fillable = [
        'job_application_id','from_stage_id','to_stage_id','changed_by','changed_at'
    ];
    protected $casts = ['changed_at' => 'datetime'];

    public function application() 
    { 
        return $this->belongsTo(JobApplication::class,'job_application_id'); 
    }
    public function fromStage() 
    { 
        return $this->belongsTo(JobPipelineStage::class,'from_stage_id'); 
    }
    public function toStage() 
    { 
        return $this->belongsTo(JobPipelineStage::class,'to_stage_id'); 
    }
    public function user() 
    { 
        return $this->belongsTo(User::class,'changed_by'); 
    }
}