<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_primary',
        'graduate_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
    public function deleteFile()
    {
        if ($this->file && Storage::exists($this->file)) {
            Storage::delete($this->file);
        }
    }
}
