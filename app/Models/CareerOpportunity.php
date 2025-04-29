<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerOpportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title'];

    public function programs()
    {
        return $this->belongsToMany(Program::class)->withTimestamps();
    }
}
