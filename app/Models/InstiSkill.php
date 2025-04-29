<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstiSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function programs()
    {
        return $this->belongsToMany(Program::class);
    }

    public function careerOpportunities()
    {
        return $this->belongsToMany(CareerOpportunity::class);
    }
}
