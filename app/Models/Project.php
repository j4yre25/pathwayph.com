<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'graduate_projects';

  protected $fillable = [
        'graduate_id',
        'title',
        'description',
        'role',
        'start_date',
        'end_date',
        'url',
        'key_accomplishments',
        'file',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'graduate_projects_tech' => 'array',
    ];
}
