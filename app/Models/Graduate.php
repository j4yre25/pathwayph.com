<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Graduate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id','program_id', 'school_year_id','gender', 'dob',
         'employment_status','current_job_title',
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


