<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutionCareerOpportunity extends Model
{
    use SoftDeletes;

    protected $fillable = ['career_opportunity_id', 'program_id', 'institution_id'];

    public function careerOpportunity()
    {
        return $this->belongsTo(CareerOpportunity::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function institution()
    {
        return $this->belongsTo(User::class, 'institution_id');
    }
}

