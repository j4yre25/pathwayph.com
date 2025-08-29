<?php
// app/Models/SeminarRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarRequest extends Model
{
    protected $fillable = [
        'institution_id', 'request_for', 'event_name', 'scheduled_at', 'description', 'status'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}