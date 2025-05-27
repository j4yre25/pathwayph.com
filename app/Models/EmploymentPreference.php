<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_id',
        'employment_min_salary',
        'employment_max_salary',
        'work_environment',
        'graduate_id',
        'job_type',
        'location',
        'additional_notes'
        // add other fields as needed
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'employment_preference_job_type');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'employment_preference_location');
    }

    public function workEnvironments()
    {
        return $this->belongsToMany(WorkEnvironment::class, 'employment_preference_work_environment');
    }

    public function salary()
    {
        return $this->belongsTo(Salary::class, 'salary_id');
    }
}
