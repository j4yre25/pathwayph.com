<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarAttendance extends Model
{
    protected $fillable = [    'seminar_request_id', 'attendee_name', 'gender', 'age', 'seminar_id', 'address', 'contact_number'
];

    public function seminarRequest()
    {
        return $this->belongsTo(SeminarRequest::class);
    }


    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
