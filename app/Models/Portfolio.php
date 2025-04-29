<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'graduate_full_name',
        'graduate_professional_title',
        'graduate_email',
        'graduate_phone',
        'graduate_location',
        'graduate_about_me',
        'graduate_picture_url',
        'graduate_skills',
        'graduate_career_goals',
        'graduate_employment_preferences',
    ];

    protected $casts = [
        'graduate_skills' => 'array',
        'graduate_career_goals' => 'array',
        'graduate_employment_preferences' => 'array',
    ];
}