<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    use HasFactory;

    protected $fillable = [
        'message',
        'certificate_path',
        'read_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
