<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortTermGoals',
        'longTermGoals',
        'industriesOfInterest',
        'careerPath',
        'user_id', // Foreign key
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}