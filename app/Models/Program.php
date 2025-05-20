<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'degree_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

    public function getFormattedNameAttribute()
    {
        $prefix = match ($this->degree->type) {
            'Bachelor' => 'BS in',
            'Associate' => 'AS in',
            'Master' => 'Master in',
            'Doctoral' => 'Doctor in',
            default => '',
        };

        return $prefix . ' ' . $this->name;
    }

}
