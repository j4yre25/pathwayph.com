<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'graduate_id',
        'title',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_primary',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}