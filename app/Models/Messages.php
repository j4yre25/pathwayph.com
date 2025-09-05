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
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // Request Info types (unchanged)
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

    public const TYPE_INTERVIEW_INVITE      = 'interview_invitation';
    public const TYPE_INTERVIEW_RESCHEDULE  = 'interview_reschedule';
    public const TYPE_OFFER_LETTER = 'offer_letter';
    public const TYPE_EXAM_INSTRUCTIONS = 'exam_instructions';
    public const TYPE_EXAM_RESCHEDULE   = 'exam_reschedule';

    public static function template(string $type): string
    {
        return match ($type) {
            self::REQ_RESUME => 'We kindly request you to upload your updated resume to complete the screening process.',
            self::REQ_TOR => 'Please upload your Transcript of Records so we may continue the evaluation.',
            self::REQ_POLICE_CLEARANCE => 'Kindly provide a recent Police Clearance document.',
            self::REQ_PORTFOLIO => 'Please share your portfolio or sample works for further assessment.',
            self::REQ_CERT_EMPLOYMENT => 'Kindly upload your Certificate of Employment for verification.',
            default => 'Please provide the additional information requested to proceed with your application.',
        };
    }

    // Scopes
    public function scopeRequestInfo($q){ return $q->where('message_type','request_info'); }
    public function scopeExamInstructions($q){ return $q->where('message_type','exam_instructions'); }
    public function scopeOfferLetters($q){ return $q->where('message_type', self::TYPE_OFFER_LETTER); }

    public function markCompleted(): bool
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
            return $this->save();
        }
        return true;
    }
}