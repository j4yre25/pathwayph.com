<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
  protected $fillable = [
    'graduate_id',
    'job_id',
    'company_id',
    'status',
    'certificate_path',
  ];

  protected $table = 'referrals';



  public function graduate()
  {
    return $this->belongsTo(\App\Models\Graduate::class, 'graduate_id');
  }

  public function job()
  {
    return $this->belongsTo(\App\Models\Job::class, 'job_id');
  }

  public function company()
  {
    return $this->belongsTo(\App\Models\Company::class, 'company_id');
  }
}
