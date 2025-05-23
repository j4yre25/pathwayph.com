<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'graduate_testimonials';

    protected $fillable = [
        'author',
        'position',
        'company',
        'content',
        'file',
        'graduate_id', // Foreign key
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
