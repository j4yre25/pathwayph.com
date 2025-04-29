<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobInvitation extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'job_invitations';

    // Mass assignable attributes
    protected $fillable = [
        'job_id', 
        'graduate_id', 
        'company_id', 
        'status', 
        'message'
    ];

    // Defining relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function graduate()
    {
        return $this->belongsTo(User::class, 'graduate_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}
