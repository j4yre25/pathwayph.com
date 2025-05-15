<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function institutionSkills()
    {
        return $this->hasMany(InstitutionSkill::class);
    }
}