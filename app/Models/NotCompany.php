<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sector_id',
    ];

    public function sector()
    {
        return $this->belongsTo(\App\Models\Sector::class);
    }
}