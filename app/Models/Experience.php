<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'graduate_experiences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'graduate_id',      // <-- add this
        'title',
        'company_name',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'address',
        'employment_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * Get the user that owns the experience.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the experience.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
