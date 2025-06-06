<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use SoftDeletes;

    protected $fillable = ['type'];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

}
