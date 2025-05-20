<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use SoftDeletes;

    protected $fillable = ['school_year_range'];

    public function institutionSchoolYears()
    {
        return $this->hasMany(InstitutionSchoolYear::class, 'school_year_range_id');
    }
}
