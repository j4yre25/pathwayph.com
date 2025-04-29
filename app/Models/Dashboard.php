<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'widgets',
        'layout',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'widgets' => 'array',
        'layout' => 'array',
    ];

    /**
     * Get the user that owns the dashboard.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}