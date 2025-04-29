<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduate_testimonials_name',
        'graduate_testimonials_role_title',
        'graduate_testimonials_relationship',
        'graduate_testimonials_testimonial',
        'user_id', // Foreign key
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}