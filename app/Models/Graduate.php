<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'graduate_first_name',
        'graduate_last_name',
        'graduate_middle_initial',
        'graduate_current_job_title',
        'graduate_school_graduated_from',
        'graduate_program_completed',
        'graduate_degree_completed',
        'graduate_skills',
        'graduate_location',
        'graduate_ethnicity',
        'graduate_address',
        'graduate_about_me',
        'graduate_picture_url',
        'employment_status',
        'gender',
        'dob',
        'email',
        'institution_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}


