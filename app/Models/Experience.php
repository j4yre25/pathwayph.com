<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'role_title',
        'company_name',
        'start_month',
        'start_year',
        'still_in_role',
        'end_month',
        'end_year',
        'job_description',
        'graduate_experience_title',
        'graduate_experience_company',
        'graduate_experience_start_date',
        'graduate_experience_end_date',
        'graduate_experience_address',
        'graduate_experience_achievements',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'still_in_role' => 'boolean',
    ];

    /**
     * Get the user that owns the experience.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}