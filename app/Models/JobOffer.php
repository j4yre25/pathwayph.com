<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    protected $table = 'job_offers';

    protected $fillable = [
        'job_application_id',
        'message_id',
        'job_title',
        'body',
        'start_date',
        'file_path',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'responded_at' => 'datetime',
    ];

    public const STATUS_SENT      = 'sent';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_DECLINED  = 'declined';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function message()
    {
        return $this->belongsTo(Messages::class, 'message_id');
    }

    public static function createFromMessage(array $data): self
    {
        return static::create([
            'job_application_id' => $data['application_id'],
            'message_id'         => $data['message_id'],
            'job_title'          => $data['job_title'],
            'body'               => $data['body'],
            'start_date'         => $data['start_date'],
            'file_path'          => $data['file_path'],
            'status'             => self::STATUS_SENT,
        ]);
    }

    public function markAccepted(): bool
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->responded_at = now();
        return $this->save();
    }

    public function markDeclined(): bool
    {
        $this->status = self::STATUS_DECLINED;
        $this->responded_at = now();
        return $this->save();
    }
}