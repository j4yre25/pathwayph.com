<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'application_id',
        'sender_id',
        'receiver_id',
        'message_type',
        'content',
        'status',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    // Request Info types
    public const REQ_RESUME                 = 'resume';
    public const REQ_TOR                    = 'transcript_of_records';
    public const REQ_POLICE_CLEARANCE       = 'police_clearance';
    public const REQ_PORTFOLIO              = 'portfolio';
    public const REQ_CERT_EMPLOYMENT        = 'certificate_of_employment';
    public const REQ_OTHER                  = 'other';

    public static array $ALLOWED_REQUESTS = [
        self::REQ_RESUME,
        self::REQ_TOR,
        self::REQ_POLICE_CLEARANCE,
        self::REQ_PORTFOLIO,
        self::REQ_CERT_EMPLOYMENT,
        self::REQ_OTHER,
    ];

    public const STATUS_UNREAD    = 'unread';
    public const STATUS_READ      = 'read';
    public const STATUS_COMPLETED = 'completed';

    // Pipeline types
    public const TYPE_INTERVIEW_INVITE      = 'interview_invitation';
    public const TYPE_INTERVIEW_RESCHEDULE  = 'interview_reschedule';
    public const TYPE_OFFER_LETTER          = 'offer_letter';
    public const TYPE_EXAM_INSTRUCTIONS     = 'exam_instructions';
    public const TYPE_EXAM_RESCHEDULE       = 'exam_reschedule';
    public const TYPE_HIRED                 = 'hired';
    public const TYPE_REJECTED              = 'rejected';
    public const TYPE_REQUEST_INFO          = 'request_info';

    public static function template(string $type): string
    {
        return match ($type) {
            self::TYPE_REQUEST_INFO       => 'Please provide the additional information requested to proceed with your application.',
            self::TYPE_INTERVIEW_INVITE   => 'You have been invited to an interview. Please review the schedule and confirm.',
            self::TYPE_INTERVIEW_RESCHEDULE => 'Your interview has been rescheduled. Please review the updated details.',
            self::TYPE_EXAM_INSTRUCTIONS  => 'Assessment instructions have been sent. Please review and follow the steps.',
            self::TYPE_EXAM_RESCHEDULE    => 'Your assessment schedule has been updated. Please check the new time.',
            self::TYPE_OFFER_LETTER       => 'Congratulations! You have received a job offer. Please review the details.',
            self::TYPE_HIRED              => 'You have been marked as Hired for this position. Welcome aboard!',
            self::TYPE_REJECTED           => 'We appreciate your interest. Unfortunately, your application will not proceed.',
            default => 'You have a new message regarding your application.',
        };
    }

    // Scopes
    public function scopeUnread($q) { return $q->whereNull('read_at'); }

    // Relationships
    public function sender()   { return $this->belongsTo(\App\Models\User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(\App\Models\User::class, 'receiver_id'); }
    public function application() { return $this->belongsTo(\App\Models\JobApplication::class, 'application_id'); }

    public function markRead(): bool
    {
        if (!$this->read_at) {
            $this->read_at = now();
            $this->status = self::STATUS_READ;
            return $this->save();
        }
        return true;
    }

    public function markCompleted(): bool
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
            return $this->save();
        }
        return true;
    }
}