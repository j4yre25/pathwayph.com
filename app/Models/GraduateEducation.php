<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduateEducation extends Model
{
    use SoftDeletes;
    protected $table = 'graduate_educations';

    protected $fillable = [
        'graduate_id',
        'education_id',
        'level_of_education',
        'program',
        'school_name',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'achievement',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];

    public function education()
    {
        return $this->belongsTo(\App\Models\Education::class);
    }

    public function graduate()
    {
        return $this->belongsTo(\App\Models\Graduate::class);
    }

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'school_name', 'institution_name');
    }

}
