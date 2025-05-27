<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'current_job_title',
        'employment_status',
        'contact_number',
        'location',
        'ethnicity',
        'address',
        'about_me',
        'institution_id',
        'degree_id',
        'program_id',
        'school_year_id',
        'linkedin_url',
        'github_url',
        'personal_website',
        'other_social_links',
        'graduate_picture',

    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
    
    /**
     * Get the career goals associated with the graduate.
     */
    public function careerGoals()
    {
        return $this->hasOne(CareerGoal::class);
    }

    /**
     * Get the employment preferences associated with the graduate.
     */
    public function employmentPreferences()
    {
        return $this->hasOne(\App\Models\EmploymentPreference::class, 'graduate_id');
    }
}
