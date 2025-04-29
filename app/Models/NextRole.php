<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NextRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'availability',
        'work_types',
        'locations',
        'right_to_work',
        'salary_expectation',
        'sectors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'work_types' => 'array',
        'locations' => 'array',
        'sectors' => 'array',
    ];

    /**
     * Get the user that owns the next role preferences.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}