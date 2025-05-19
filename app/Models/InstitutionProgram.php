<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutionProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'degree_id',
        'program_id',
        'program_code',
        'institution_id',
    ];

    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }


    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}