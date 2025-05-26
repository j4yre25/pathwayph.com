<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'graduate_achievements';


    protected $fillable = [
        'title',
        'issuer',
        'date',
        'description',
        'url',
        'type',
        'credential_picture',
        'graduate_id'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
