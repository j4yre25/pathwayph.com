<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkEnvironment extends Model
{
    protected $fillable = ['environment_type'];

    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_work_environment');
    }

    public function employmentPreferences()
    {
        return $this->belongsToMany(EmploymentPreference::class, 'employment_preference_work_environment');
    }
}
