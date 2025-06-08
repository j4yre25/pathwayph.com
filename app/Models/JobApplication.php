<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'graduate_id',
        'user_id',
        'job_id',
        'status',
        'stage',
        'applied_at',
        'resume_id',
        'cover_letter',
        'additional_documents',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'applied_at' => 'datetime',
        'additional_documents' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'job_applications';

    /**
     * Get the user that owns the job application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the graduate that owns the job application.
     */ 
    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }

    /**
     * Get the job that the application is for.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the resume used for the application.
     */
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
   
    /**
     * Get the interviews associated with the job application.
     */
    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }
}