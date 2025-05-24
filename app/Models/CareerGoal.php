<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerGoal extends Model
{
    use HasFactory;

     protected $fillable = [
        'short_term_goals',
        'long_term_goals',
        'industries_of_interest',
        'career_path',
        'graduate_id',
    ];

    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }
    // Relationship with User
    public function user()
    {
        return $this->belongsTo(Graduate::class);
    }
}