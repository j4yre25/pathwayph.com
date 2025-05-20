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
        'id',
        'email',
        'password',
        'role',
        'is_approved',
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_co4des',
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


    public function jobs() {

        return $this->hasMany(Job::class, 'user_id');
    }

    public function hr() {
        return $this->hasOne(HumanResource::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function peso()
{
    return $this->hasMany(Peso::class);
}


    public function sectors() {

        return $this->hasMany(Sector::class);
    }

    public function categories()
    {
        return $this->hasManyThrough(Category::class, Sector::class);

    }
    public function programs()
{
    return $this->hasMany(Program::class, 'institution_id', 'id');
}

    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }

    public function school_years()
    {
        return $this->hasMany(SchoolYear::class);
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

}
