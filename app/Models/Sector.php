<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{

    use SoftDeletes;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function notcompanies()
    {
        return $this->hasMany(NotCompany::class);
    }

    public function peso()
    {
        return $this->belongsTo(Peso::class, 'user_id', 'user_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'sector_id');
    }

    protected $fillable = [
        'name',
        'user_id',
        'sector_id',
        'sector_code',
        'division_codes',


    ];
}
