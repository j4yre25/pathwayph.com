<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Education extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'educations';

    protected $fillable = [
        'name',
        'order_rank',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */

    /**
     * Get the user that owns the education record.
     */

    /**
     * Get the institution associated with the education record.
     */
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'institution_id');
    }
    public function programRelation()
    {
        return $this->belongsTo(\App\Models\Program::class, 'program', 'name'); // or use program_id if available
    }

    public function graduateEducations()
    {
        return $this->hasMany(GraduateEducation::class, 'education_id');
    }
}