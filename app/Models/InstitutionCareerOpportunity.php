<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InstitutionProgram;

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

    public function getInstitutionProgramAttribute()
    {
        return InstitutionProgram::where('program_id', $this->program_id)
            ->where('institution_id', $this->institution_id)
            ->whereNull('deleted_at')
            ->first();
    }
}

