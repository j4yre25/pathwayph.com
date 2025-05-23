<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'company_street_address',
        'company_brgy',
        'company_city',
        'company_province',
        'company_zip_code',
        'company_email',
        'company_mobile_phone',
        'company_tel_phone',
        'category_id', 
        'sector_id',
        'company_id',
    ];

    /**
     * The user who owns the company profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Jobs posted by this company.
     */
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function hrs()
    {
        return $this->hasMany(User::class);
    }
}
