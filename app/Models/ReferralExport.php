<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralExport extends Model
{
      protected $fillable = [
        'graduate_id',
        'job_invitation_id',
        'certificate_path',
    ];
}
