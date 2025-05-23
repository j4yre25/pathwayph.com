<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_types',          
        'salary_expectations', 
        'preferred_locations',
        'work_environment',
        'availability',
        'additional_notes',
        'graduate_id',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}