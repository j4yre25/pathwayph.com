<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;

    protected $table = 'graduate_certifications';

protected $fillable = [
    'graduate_id',
    'name',
    'issuer',
    'issue_date',
    'expiry_date',
    'credential_url',
    'credential_id',
    'file_path',
];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
