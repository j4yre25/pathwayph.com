<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutionDegree extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'degree_id',
        'degree_code',
        'institution_id',
    ];

    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }


    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}