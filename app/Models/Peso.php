<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peso extends Model
{
    use HasFactory;

    protected $table = 'peso';

    public $timestamps = false; // Add this line


    protected $fillable = [
        'user_id',
        'peso_first_name',
        'peso_last_name',
        'peso_middle_name',
        'description',
        'contact_number',
        'telephone_number',
        'address',
        'logo',
    ];

    /**
     * Get the user associated with the peso record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
