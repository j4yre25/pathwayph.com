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
use App\Models\InstitutionProgram;
use App\Models\Institution;



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
        'id',
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

        // Company
        'company_id',
        'company_hr_first_name',
        'company_hr_last_name',

        //Graduate
        'institution_id',
        'graduate_first_name',
        'graduate_last_name',
        'graduate_middle_initial',
        'graduate_current_job_title', //changed this
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



        // Institution
        'institution_type',
        'institution_name',
        'institution_address',
        'institution_president_last_name',
        'institution_president_first_name',
        'institution_career_officer_first_name',
        'institution_career_officer_last_name',
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

    public function graduates()
    {
        return $this->hasOne(Graduate::class);
    }


    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
  
    public function hr() {
        return $this->hasOne(HumanResource::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function sectors()
    {

        return $this->hasMany(Sector::class);
    }

    public function categories()
    {
        return $this->hasManyThrough(Category::class, Sector::class);

    }
    public function programs()
    {
        return $this->hasMany(InstitutionProgram::class, 'institution_id');
    }

    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }

    public function school_years()
    {
        return $this->hasMany(SchoolYear::class, 'user_id');
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
        return $this->hasMany(JobInvitation::class, 'company_id'); // For r
    }

    public function institutionCareerOpportunities()
    {
        return $this->hasMany(InstitutionCareerOpportunity::class, 'institution_id');
    }

    // Optional: convenience to get all career titles from their links
    public function careerOpportunities()
    {
        return $this->hasManyThrough(
            CareerOpportunity::class,
            InstitutionCareerOpportunity::class,
            'institution_id',           // FK on institution_career_opportunities
            'id',                       // FK on career_opportunities
            'id',                       // Local key on users
            'career_opportunity_id'    // Local key on institution_career_opportunities
        );
    }

    public function institutionSkills()
    {
        return $this->hasMany(InstitutionSkill::class, 'institution_id');
    }

    public function institution()
    {
        return $this->hasOne(Institution::class, 'user_id');
    }

    public function institutionPrograms()
    {
        return $this->hasManyThrough(
            InstitutionProgram::class,
            Institution::class,
            'institution_id', // Foreign key on institutions table (users.id)
            'institution_id', // Foreign key on institution_programs table (institutions.id)
            'id',             // Local key on users table
            'id'              // Local key on institutions table
        );
    }

    public function graduate()
    {
        return $this->hasOne(Graduate::class, 'user_id');
    }

}
