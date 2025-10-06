<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutionSkill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'skill_id',
        'career_opportunity_id',
        'program_id',
    ];

    public function institution()
    {
        return $this->belongsTo(User::class, 'institution_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function careerOpportunity()
    {
         return $this->belongsTo(CareerOpportunity::class, 'career_opportunity_id')->withTrashed();
    }
    public function program()
    {
         return $this->belongsTo(Program::class, 'program_id')->withTrashed();
    }
}
