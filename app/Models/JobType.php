<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobType extends Model
{
    protected $fillable = ['type'];

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_job_type');
    }

    public function employmentPreferences(): BelongsToMany
    {
        return $this->belongsToMany(EmploymentPreference::class, 'employment_preference_job_type');
    }
}