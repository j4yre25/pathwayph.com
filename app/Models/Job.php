<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'peso_id',
        'company_id',
        'status',
        'department_id',
        'job_title',
        'job_description',
        'job_requirements',
        'is_negotiable',
        'job_salary_type',
        'job_min_salary',
        'job_max_salary',
        'job_type',
        'job_experience_level',
        'job_vacancies',
        'work_environment',
        'skills',
        'job_deadline',
        'job_application_limit',
        'is_approved',
        'posted_by',
        'job_code',
        'job_id',
        'location',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'job_deadline' => 'date',
        'skills' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_approved' => null,
        'status' => 'active',
        'is_negotiable' => false,
    ];

    /**
     * Get the applications for this job.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Scope a query to only include active jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to match jobs with user's next role preferences.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\NextRole  $nextRole
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMatchingPreferences($query, NextRole $nextRole)
    {
        return $query->where('work_type', $nextRole->work_type)
            ->where('sector', $nextRole->sector)
            ->where(function ($query) use ($nextRole) {
                foreach ($nextRole->locations as $location) {
                    $query->orWhere('location', 'like', "%{$location}%");
                }
            });
    }

    protected static function booted()
    {
        // When status changes to closed or expired -> archive (soft delete)
        static::updated(function (Job $job) {
            if ($job->wasChanged('status')) {
                $status = strtolower($job->status);
                if (in_array($status, ['closed', 'expired']) && !$job->trashed()) {
                    // Normalize expired to closed
                    if ($status === 'expired' && $job->status !== 'closed') {
                        $job->status = 'closed';
                        $job->saveQuietly();
                    }
                    // Archive
                    $job->delete();
                }
            }
        });

        // When explicitly archived (soft deleted), force status to closed
        static::deleting(function (Job $job) {
            if (!$job->trashed() && strtolower($job->status) !== 'closed') {
                $job->status = 'closed';
                $job->saveQuietly();
            }
        });
    }

    /**
     * Get the user that owns the job.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'job_program', 'job_id', 'program_id') ;
    }

    /**
     * Get the sector that the job belongs to.
     */
    public function sectorRelation()
    {
        return $this->belongsTo(Sector::class, 'sector');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the category that the job belongs to.
     */
    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function JobApplication()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function invitations()
    {
        return $this->hasMany(JobInvitation::class);
    }

    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    public function hasApplicationLimitReached()
    {
        if (!$this->applicants_limit) {
            return false;
        }

        return $this->applicants()->count() >= $this->applicants_limit;
    }

    public function shouldBeArchived()
    {
        return $this->isExpired() || $this->hasApplicationLimitReached();
    }

    public function peso()
    {
        return $this->belongsTo(\App\Models\Peso::class, 'peso_id');
    }

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'job_job_type');
    }


    public function salary()
    {
        return $this->hasOne(Salary::class, 'job_id');
    }

    public function workEnvironments()
    {
        return $this->belongsToMany(WorkEnvironment::class, 'job_work_environment', 'job_id', 'work_environment_id');
    }

    /**
     * Get all interviews for this job through job applications.
     */
    public function interviews()
    {
        return $this->hasManyThrough(
            \App\Models\Interview::class,      // The related model
            \App\Models\JobApplication::class, // The intermediate model
            'job_id',                          // Foreign key on JobApplication table...
            'job_application_id',              // Foreign key on Interview table...
            'id',                              // Local key on Job table...
            'id'                               // Local key on JobApplication table...
        );
    }

    // Optional compatibility accessor if legacy code expects job->locations:
    public function getLocationsAttribute()
    {
        // Return collection-like array with single pseudo record to avoid errors
        if (!$this->location) return [];
        return [(object)['address' => $this->location]];
    }
}
