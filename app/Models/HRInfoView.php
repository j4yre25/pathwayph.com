<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrInfoView extends Model
{
    protected $table = 'view_hr_user_company_info';
    public $incrementing = false;
    public $timestamps = false;

    // Make fields mass assignable if needed
    protected $guarded = [];
}
