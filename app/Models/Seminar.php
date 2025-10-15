<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    protected $fillable = ['title', 'date', 'description', 'archived_at'];

    public function attendances()
    {
        return $this->hasMany(SeminarAttendance::class);
    }
}
