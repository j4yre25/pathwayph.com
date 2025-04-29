<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;



    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'is_approved',
        'company',
        'location',
        'description',
        'min_salary',
        'max_salary',
        'salary_frequency',
        'work_type',
        'sector',
        'sector_category',
        'posted_at',
        'application_deadline',
        'status',
        'user_id',
        'job_title',
        'location',
        'vacancy',
        'salary',
        'job_type',
        'experience_level',
        'description',
        'skills',
        'sector',
        'category',
        'requirements',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'posted_at' => 'datetime',
        'application_deadline' => 'date',
        'skills' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_approved' => null, 
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
        
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Get the sector that the job belongs to.
     */
    public function sectorRelation()
    {
        return $this->belongsTo(Sector::class, 'sector');
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

}
