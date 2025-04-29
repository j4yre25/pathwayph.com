<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'is_approved',
        'dob',
        'gender',
        'telephone_number',
        'contact_number',

        // PESO
        'peso_first_name',
        'peso_last_name',


        'graduate_first_name',
        'graduate_last_name',
        'graduate_middle_initial',
        'graduate_professional_title',
        'graduate_email',
        'graduate_phone',
        'graduate_location', 
        'graduate_birthdate',
        'graduate_gender',
        'graduate_ethnicity',
        'graduate_address',
        'graduate_about_me',
        'graduate_picture_url',
       
        'graduate_education_institution_id', 
        'graduate_education_program',
        'graduate_education_field_of_study',
        'graduate_education_start_date',
        'graduate_education_end_date', 
        'graduate_education_description',
        
        'graduate_skills_name', 
        'graduate_skills_proficiency',
        'graduate_skills_type',
        'graduate_skills_years_experience', 

        // Company
        'company_name',
        'company_street_address',
        'company_brgy',
        'company_city',
        'company_province',
        'company_zip_code',
        'company_company_email',
        'company_contact_number',
        'company_hr_first_name',
        'company_hr_last_name',

        // Institution
        'institution_type',
        'institution_name',
        'institution_address',
        'institution_president_last_name',
        'institution_president_first_name',
        'institution_career_officer_first_name',
        // 'institution_career_officer_last_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected $attributes = [
        'is_approved' => null, 
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'graduate_year_graduated' => 'date',
            'is_approved' => 'boolean'
            
        ];
    }

    public function jobs() {
        return $this->hasMany(Job::class);

    }
    public function sectors() {
        return $this->hasMany(Sector::class);

    }

    public function categories() {
        return $this->hasManyThrough(Category::class, Sector::class);

    }

    /**
     * Get the user's settings.
     */
    public function settings()
    {
        return $this->hasOne(Settings::class);
    }

    /**
     * Get the user's dashboard.
     */
    public function dashboard()
    {
        return $this->hasOne(Dashboard::class);
    }

    /**
     * Get the user's education entries.
     */
    public function education()
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Get the user's job applications.
     */
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the user's institution.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
  
    }

    public function getGraduateSkillsAttribute($value)
    {
        return json_decode($value, true) ?? []; // Decode JSON to array
    }
    
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    // Relationship with Education
    public function educationEntries()
    {
        return $this->hasMany(Education::class);
    }

    // Relationship with Experience
    public function experienceEntries()
    {
        return $this->hasMany(Experience::class);
    }


    // Relationship with Certifications
    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    // Relationship with Achievements
    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    // Relationship with Testimonials
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    // Relationship with Employment Preferences
    public function employmentPreferences()
    {
        return $this->hasOne(EmploymentPreference::class);
    }

    // Relationship with Career Goals
    public function careerGoals()
    {
        return $this->hasOne(CareerGoal::class);
    }

    // Relationship with Resume
    public function resume()
    {
        return $this->hasOne(Resume::class);
    }

    // Relationship with Job Invitations
    public function jobInvitations()
    {
        return $this->hasMany(JobInvitation::class, 'graduate_id'); // For graduates
    }

    public function jobInvitationsSent()
    {
        return $this->hasMany(JobInvitation::class, 'company_id'); // For companies
    }

}
