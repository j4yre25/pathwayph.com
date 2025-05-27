<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'program_id', 'career_opportunity_id', 'skills', 'description', 'is_active'
    ];

    public function program() { return $this->belongsTo(Program::class); }
    public function careerOpportunity() { return $this->belongsTo(CareerOpportunity::class); }
    public function graduates() { return $this->belongsToMany(Graduate::class, 'graduate_internship_program'); }
}
