<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salary extends Model
{
    protected $fillable = ['job_id','employment_preference_id','job_min_salary', 'job_max_salary', 'salary_type', 'employment_min_salary', 'employment_max_salary'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
    
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function employmentPreferences(): HasMany
    {
        return $this->hasMany(EmploymentPreference::class, 'salary_id');
    }
}