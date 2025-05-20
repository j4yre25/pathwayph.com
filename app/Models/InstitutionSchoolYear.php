<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutionSchoolYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_year_range_id',
        'term',
        'institution_id',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_range_id');
    }


    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}