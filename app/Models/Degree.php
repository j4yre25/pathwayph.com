<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'type'];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
