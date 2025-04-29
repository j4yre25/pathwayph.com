<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'institution_id',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
        'grade',
        'activities',
        'description',
        'graduate_education_institution_id',
        'graduate_education_program',
        'graduate_education_field_of_study',
        'graduate_education_start_date',
        'graduate_education_end_date',
        'graduate_education_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'graduate_education_start_date' => 'date',
        'graduate_education_end_date' => 'date',
    ];

    /**
     * Get the user that owns the education record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the institution associated with the education record.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}