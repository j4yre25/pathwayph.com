<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduate_projects_title',
        'graduate_projects_description',
        'graduate_projects_role',
        'graduate_projects_start_date',
        'graduate_projects_end_date',
        'graduate_projects_url',
        'graduate_projects_tech',
        'graduate_projects_key_accomplishments',
        'user_id',
    ];

    protected $casts = [
        'graduate_projects_tech' => 'array',
    ];
}