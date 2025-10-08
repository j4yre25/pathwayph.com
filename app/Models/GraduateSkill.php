<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduateSkill extends Model
{
    use SoftDeletes;

    protected $table = 'graduate_skills';

    protected $fillable = [
        'graduate_id',
        'skill_id',
        'proficiency_type',
        'type',
        'years_experience',
        'months_experience',

    ];
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
