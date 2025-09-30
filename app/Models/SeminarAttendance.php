<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarAttendance extends Model
{
    protected $fillable = ['seminar_request_id', 'attendee_name', 'gender', 'age'];

    public function seminarRequest()
    {
        return $this->belongsTo(SeminarRequest::class);
    }
}