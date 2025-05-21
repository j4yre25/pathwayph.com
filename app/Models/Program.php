<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'degree_id'];


    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }

    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }


    public function careerOpportunities()
    {
        return $this->hasMany(CareerOpportunity::class);
    }

}
