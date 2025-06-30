<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraduateEducation extends Model
{
    use SoftDeletes;

    protected $table = 'graduate_educations';

    protected $fillable = [
        'education',
        'graduate_id',
        'institution_id',
        'program',
        'field_of_study',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'achievements',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function graduate()
    {
        return $this->belongsTo(\App\Models\Graduate::class, 'graduate_id');
    }
}
