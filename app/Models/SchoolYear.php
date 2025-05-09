<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SchoolYear extends Model
{
    use SoftDeletes;

    public function user() {
        return $this->belongsTo(User::class);
    }

    protected $fillable = ['school_year_range','user_id', 'term','institution_id'];
}
