<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HumanResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'first_name',
        'middle_name',
        'last_name',
        'mobile_number',
        'dob',
        'gender',
        'is_main_hr',
    ];


    protected $attributes = [
        'is_main_hr' => false,
    ];
    
    protected $appends = ['full_name']; // <-- Add this line


    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'is_main_hr' => 'boolean',
        ];
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
    /**
     * The user account for this HR (used for login, etc.).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The company this HR works for.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


}
