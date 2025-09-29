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
        'dob',
        'gender',
        'current_job_title',
        'employment_status',
        'profession',
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
        'company_id',
        'not_company'

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
        return $this->belongsTo(\App\Models\SchoolYear::class, 'school_year_id');
    }
    public function education()
    {
        return $this->hasMany(GraduateEducation::class);
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
    public function employmentPreference()
    {
        return $this->hasOne(EmploymentPreference::class, 'graduate_id');
    }

    public function graduateSkills()
    {
        return $this->hasMany(GraduateSkill::class);
    }

    public function graduateEducations()
    {
        return $this->hasMany(GraduateEducation::class, 'graduate_id');
    }


    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'graduate_id');
    }

    public function experience()
    {
        return $this->hasMany(Experience::class, 'graduate_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    public function resume()
    {
        return $this->hasOne(Resume::class);
    }
    public function internshipPrograms()
    {
        return $this->belongsToMany(\App\Models\InternshipProgram::class, 'graduate_internship_program');
    }
    public function institutionSchoolYear()
    {
        return $this->belongsTo(\App\Models\InstitutionSchoolYear::class, 'school_year_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function referrals()
    {
        return $this->hasMany(\App\Models\Referral::class, 'graduate_id');
    }

    public function jobSearchHistory()
    {
        return $this->hasMany(JobSearchHistory::class);
    }
    // (Optionally keep old relation name if used elsewhere)
    // public function referralExports() { return $this->referrals()->whereNotNull('certificate_path'); }
}
