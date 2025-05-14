<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',           // <-- add this
        'last_name',            // <-- add this
        'middle_initial',       // <-- add this
        'program_id',
        'school_year_id',
        'gender',
        'dob',
        'employment_status',
        'current_job_title',
        'institution_id',       // if you use this in your update/store
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function schoolYear() {
        return $this->belongsTo(SchoolYear::class);
    }

}


