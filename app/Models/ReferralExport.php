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



  public function graduate()
  {
    return $this->belongsTo(\App\Models\Graduate::class, 'graduate_id');
  }

  public function jobInvitation()
  {
    return $this->belongsTo(\App\Models\JobInvitation::class, 'job_invitation_id');
  }
}
