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
        'profile_picture',
        'archived_at',
        'last_login_at'

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
            'archived_at' => 'datetime',
            'last_login_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'graduate_year_graduated' => 'date',
            'is_approved' => 'boolean'


        ];
    }
    public function companyThroughHR()
    {
        return $this->hasOneThrough(
            \App\Models\Company::class,        // Final model
            \App\Models\HumanResource::class,  // Intermediate model
            'user_id',                         // Foreign key on HumanResource table
            'id',                              // Foreign key on Company table
            'id',                              // Local key on User
            'company_id'                       // Local key on HumanResource
        );
    }

    public function getCompanyAttribute($value)
    {
        // If relation is loaded and null, return null, else return the relation
        return $this->getRelationValue('company') ?? null;
    }

    public function hr()
    {
        return $this->hasOne(\App\Models\HumanResource::class, 'user_id');
    }

    public function getHrAttribute($value)
    {
        return $this->getRelationValue('hr') ?? null;
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
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
    public function employmentPreference()
    {
        return $this->hasOne(EmploymentPreference::class);
    }

    // Relationship with Career Goals through Graduate
    public function careerGoals()
    {
        return $this->hasOneThrough(
            CareerGoal::class,
            Graduate::class,
            'user_id', // Foreign key on graduates table
            'graduate_id', // Foreign key on career_goals table
            'id', // Local key on users table
            'id' // Local key on graduates table
        );
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

    public function company()
    {
        return $this->hasOne(Company::class, 'user_id');
    }

    public function peso()
    {
        return $this->hasOne(Peso::class, 'user_id');
    }

    public function institution()
    {
        return $this->hasOne(Institution::class, 'user_id');
    }

    public function graduate()
    {
        return $this->hasOne(Graduate::class, 'user_id');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function isArchived()
    {
        return !is_null($this->archived_at);
    }
}
