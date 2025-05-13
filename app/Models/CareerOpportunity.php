<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerOpportunity extends Model
{
    use SoftDeletes;

    protected $fillable = ['title'];

    public function institutionLinks()
    {
        return $this->hasMany(InstitutionCareerOpportunity::class);
    }
}
