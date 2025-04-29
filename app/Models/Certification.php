<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'graduate_certification_name',
        'graduate_certification_issuer',
        'graduate_certification_issue_date',
        'graduate_certification_expiry_date',
        'graduate_certification_credential_id',
        'graduate_certification_credential_url',
        'user_id', // Foreign key
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}