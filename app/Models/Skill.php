<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduate_skills_name',
        'graduate_skills_proficiency',
        'graduate_skills_type',
        'graduate_skills_years_experience',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}