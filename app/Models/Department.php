<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'human_resources_id',
        'company_id', 
        'department_name'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function hr()
    {
        return $this->belongsTo(HumanResource::class, 'human_resources_id');
    }
}
