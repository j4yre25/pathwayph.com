<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\ApplicantNotifier;
use App\Support\StageLogger;

class JobApplication extends Model
{
    use HasFactory;

    protected $previousStage = null; // non-persisted holder

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'graduate_id',
        'user_id',
        'job_id',
        'status',
        'stage',
        'applied_at',
        'resume_id',
        'cover_letter',
        'additional_documents',
        'notes'
    ];

    protected static function booted()
    {
        static::created(function ($application) {
            try {
                if (class_exists(StageLogger::class)) {
                    StageLogger::log($application, null, $application->stage);
                }
                ApplicantNotifier::stageChanged($application, null, $application->stage);
            } catch (\Throwable $e) {
                \Log::warning('JobApplication created side-effect failed', [
                    'id'=>$application->id,'err'=>$e->getMessage()
                ]);
            }
        });

        static::updating(function ($application) {
            if ($application->isDirty('stage')) {
                // Store original stage in a non-attribute property
                $application->previousStage = $application->getOriginal('stage');
            }
        });

        static::updated(function ($application) {
            if ($application->wasChanged('stage')) {
                $from = $application->previousStage;
                $to   = $application->stage;
                try {
                    if (class_exists(StageLogger::class)) {
                        StageLogger::log($application, $from, $to);
                    }
                    ApplicantNotifier::stageChanged($application, $from, $to);
                } catch (\Throwable $e) {
                    \Log::warning('JobApplication updated side-effect failed', [
                        'id'=>$application->id,'err'=>$e->getMessage()
                    ]);
                }
                // Clear holder
                $application->previousStage = null;
            }
        });
    }
    protected $attributes = [
        'stage' => 'applied', 
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'applied_at' => 'datetime',
        'additional_documents' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'job_applications';

    /**
     * Get the user that owns the job application.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the graduate that owns the job application.
     */ 
     public function graduate()
    {
        return $this->belongsTo(\App\Models\Graduate::class, 'graduate_id');
    }

    /**
     * Get the job that the application is for.
     */
     public function job()
    {
        return $this->belongsTo(\App\Models\Job::class, 'job_id');
    }
    /**
     * Get the resume used for the application.
     */
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
   
    /**
     * Get the interviews associated with the job application.
     */
    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    /**
     * Get the stages associated with the job application.
     */
    public function stages()
    {
        return $this->hasMany(JobApplicationStage::class);
    }

    public function offer()
    {
        return $this->hasOne(JobOffer::class);
    }
    
    public function pipelineStage() {
    return $this->belongsTo(JobPipelineStage::class,'job_pipeline_stage_id');
    }
    public function stageLogs() {
        return $this->hasMany(JobApplicationStageLog::class, 'job_application_id');
    }
    public function actionLogs()
    {
        return $this->hasMany(\App\Models\JobApplicationActionLog::class);
    }

    public function syncStatusFromStage(): bool
    {
        $stage = strtolower((string)$this->stage);
        $map = [
            'applied'    => 'applied',
            'screening'  => 'screening',
            'assessment' => 'screening',
            'interview'  => 'screening',
            'offer'      => 'offered',
            'hired'      => 'hired',
            'rejected'   => 'rejected',
        ];
        $new = $map[$stage] ?? $this->status;
        if ($this->status !== $new) {
            $this->status = $new;
            return true;
        }
        return false;
    }
}