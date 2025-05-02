<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use SoftDeletes;

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    protected $fillable = ['user_id', 'type'];
}
