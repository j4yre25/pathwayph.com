<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;



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
        'sector_id',
        'category_id',
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

    public function locations()
    {
        return $this->belongsToMany(Location::class, table: 'job_location');
    }

    public function salary()
    {
        return $this->hasOne(Salary::class, 'job_id');
    }

    public function workEnvironments()
    {
        return $this->belongsToMany(\App\Models\WorkEnvironment::class, 'job_work_environment');
    }

    public function getWorkEnvironmentLabelsAttribute(): array
    {
        $labels = $this->relationLoaded('workEnvironments')
            ? $this->workEnvironments->pluck('environment_type')
            : $this->workEnvironments()->pluck('work_environments.environment_type');
        $labels = $labels->filter()->unique()->values()->all();

        if (!count($labels) && isset($this->work_environment) && $this->work_environment) {
            return [$this->work_environment]; // legacy single column fallback
        }
        return $labels;
    }

    public function scopeWithWorkEnvironment($q, $env)
    {
        if (!$env) return $q;
        $values = is_array($env) ? array_filter($env) : [$env];
        if (!count($values)) return $q;

        return $q->where(function($qq) use ($values) {
            $qq->whereIn('work_environment', $values);
            if (\Illuminate\Support\Facades\Schema::hasTable('job_work_environment')) {
                $qq->orWhereHas('workEnvironments', function($wq) use ($values) {
                    $wq->whereIn('environment_type', $values);
                });
            }
        });
    }

    protected $appends = ['work_environment_labels'];
}
