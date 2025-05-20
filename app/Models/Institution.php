<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'institution_name',
        'institution_type',
        'institution_address',
        'email',
        'website',
        'contact_number',
        'institution_contact_number',
        'telephone_number',
        'institution_president_first_name',
        'institution_president_last_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

      public function schoolYears()
    {
        return $this->hasMany(SchoolYear::class, 'institution_id');
    }
     public function institutionSchoolYears()
    {
        return $this->hasMany(InstitutionSchoolYear::class, 'institution_id');
    }
    
    public function graduates()
    {
        return $this->hasMany(Graduate::class, 'institution_id');
    }

    public function programs()
    {
        return $this->hasMany(Program::class, 'institution_id');
    }

    public function institutionPrograms()
    {
        return $this->hasMany(InstitutionProgram::class, 'institution_id');
    }

    public function institutionDegrees()
    {
        return $this->hasMany(InstitutionDegree::class, 'institution_id');
    }

    public function institutionSkills()
    {
        return $this->hasMany(InstitutionSkill::class, 'institution_id');
    }

    public function institutionCareerOpportunities()
    {
        return $this->hasMany(InstitutionCareerOpportunity::class, 'institution_id');
    }
}