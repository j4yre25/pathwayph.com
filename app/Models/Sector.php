<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{

    use SoftDeletes;
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function categories() {
        return $this->hasMany(Category::class);
    }
    public function companies() {
        return $this->hasMany(Company::class);
    }


    protected $fillable = [
        'name',
        'user_id',
        'sector_id',
        'sector_code',
        'division_codes',
   
        
    ];
}
