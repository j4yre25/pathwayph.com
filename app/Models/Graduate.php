<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'graduate_first_name',
        'graduate_last_name',
        'graduate_middle_name',
        'current_job_title',
        'employment_status',
        'contact_number',
        'location',
        'ethnicity',
        'address',
        'about_me',
        'institution_id',
        'degree_id',
        'program_id',
        'school_year_id',
        'linkedin_url',
        'github_url',
        'personal_website',
        'other_social_links',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
