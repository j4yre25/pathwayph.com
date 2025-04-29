<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutMe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'about_me'; 
    protected $fillable = [
        'user_id',
        'professional_title',
        'personal_summary',
    ];

    /**
     * Get the user that owns the about me section.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}