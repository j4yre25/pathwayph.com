<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $fillable = [
        'application_id',
        'exam_type',
        'score',
        'status',
        'remarks',
        'recorded_by',
    ];

    public const STATUS_PASSED  = 'passed';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_PENDING = 'pending';

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}