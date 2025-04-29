<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'middle_initial',
        'program_id', 'school_year_id', 'employment_status',
        'current_job_title',
    ];

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function schoolYear() {
        return $this->belongsTo(SchoolYear::class);
    }

    public function getFullNameAttribute() {
        return $this->first_name . ' ' . ($this->middle_initial ? $this->middle_initial . '. ' : '') . $this->last_name;
    }
}


