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
        'company_name',
        'company_street_address',
        'company_brgy',
        'company_city',
        'company_province',
        'company_zip_code',
        'company_email',
        'company_mobile_phone',
        'company_tel_phone',
        'company_hr_first_name',
        'company_hr_last_name',
    ];

    /**
     * The user who owns the company profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jobs posted by this company.
     */
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
