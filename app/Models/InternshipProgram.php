<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'institution_id', 'is_active'
    ];

    public function institution() { return $this->belongsTo(Institution::class); }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'internship_program_program');
    }

    public function careerOpportunities()
    {
        return $this->belongsToMany(CareerOpportunity::class, 'internship_program_career_opportunity');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'internship_program_skill');
    }

    public function graduates()
    {
        return $this->belongsToMany(Graduate::class, 'graduate_internship_program');
    }
}
