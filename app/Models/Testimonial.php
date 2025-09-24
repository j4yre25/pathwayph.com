<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'graduate_testimonials';

    protected $fillable = [
        'graduate_id',
        'company_id',
        'company_name',
        'institution_id',
        'institution_name',
        'content',
        'file',
         'author',
    ];

    // Relationship with User
    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
