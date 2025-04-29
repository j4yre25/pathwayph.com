<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'personal_summary',
        'education',
        'experience_level',
        'career_history',
        'skills',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'skills' => 'array',
    ];

    /**
     * Get the user that owns the profile section.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}