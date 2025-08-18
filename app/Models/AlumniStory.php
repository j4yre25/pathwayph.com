<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumniStory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'graduate_alumni_stories';

    protected $fillable = [
        'graduate_id',
        'title',
        'content',
        'image',
        'video_url',
        'is_featured'
    ];

    // Relationship with Graduate
    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }
}