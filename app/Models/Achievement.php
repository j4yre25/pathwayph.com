<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduate_achievement_title',
        'graduate_achievement_issuer',
        'graduate_achievement_date',
        'graduate_achievement_description',
        'graduate_achievement_url',
        'graduate_achievement_type',
        'credential_picture_url',
        'user_id'
    ];

    protected $casts = [
        'graduate_achievement_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}