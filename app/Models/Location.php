<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = ['address'];

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_location');
    }

    public function employmentPreferences(): BelongsToMany
    {
        return $this->belongsToMany(EmploymentPreference::class, 'employment_preference_location');
    }
}
