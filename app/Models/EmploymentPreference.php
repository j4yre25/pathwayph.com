<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_types',
        'salary_expectations',
        'preferred_locations',
        'work_environment',
        'availability',
        'additional_notes',
        'graduate_id',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'job_job_type');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }

    public function workEnvironments()
{
    return $this->belongsToMany(WorkEnvironment::class, 'employment_preference_work_environment');
}
}
