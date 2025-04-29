<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_range',
        'term',
        'program_id', // Change this to program_id
    ];

    public function graduates()
    {
        return $this->hasMany(Graduate::class, 'program_id');
    }
    public function program() 
{ 
    return $this->belongsTo(Program::class, 'program_id'); 
}

}

