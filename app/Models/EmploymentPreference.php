<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_id',
        'employment_min_salary',
        'employment_max_salary',
        'work_environment',
        'graduate_id',
        'job_type',
        'location',
        'additional_notes'
        // add other fields as needed
    ];

    protected $appends = [
        'job_types_array',
        'work_environments_array',
        'locations_array',
    ];

    protected $casts = [
        'job_type'         => 'array',
        'work_environment' => 'array',
        'location'         => 'array',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }


    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'employment_preference_job_type');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'employment_preference_location');
    }

    public function workEnvironments()
    {
        return $this->belongsToMany(WorkEnvironment::class, 'employment_preference_work_environment');
    }

    public function salary()
    {
        return $this->belongsTo(Salary::class, 'salary_id');
    }

    public function getJobTypesArrayAttribute()
    {
        if ($this->relationLoaded('jobTypes') && $this->jobTypes->count()) {
            return $this->jobTypes->map(fn($jt) => ['name'=>$jt->name])->values();
        }
        if ($this->job_type) {
            return collect(is_string($this->job_type) ? explode(',', $this->job_type) : (array)$this->job_type)
                ->map(fn($v) => ['name'=>trim($v)])->values();
        }
        return [];
    }

    public function getWorkEnvironmentsArrayAttribute()
    {
        if ($this->relationLoaded('workEnvironments') && $this->workEnvironments->count()) {
            return $this->workEnvironments->map(fn($we) => ['name'=>$we->name])->values();
        }
        if ($this->work_environment) {
            return collect(is_string($this->work_environment) ? explode(',', $this->work_environment) : (array)$this->work_environment)
                ->map(fn($v) => ['name'=>trim($v)])->values();
        }
        return [];
    }

    public function getLocationsArrayAttribute()
    {
        if ($this->relationLoaded('locations') && $this->locations->count()) {
            return $this->locations->map(fn($loc) => [
                'name' => $loc->name ?? null,
                'address' => $loc->address ?? ($loc->name ?? null),
            ])->values();
        }
        if ($this->location) {
            return collect(is_string($this->location) ? explode(',', $this->location) : (array)$this->location)
                ->map(fn($v) => ['name'=>trim($v), 'address'=>trim($v)])->values();
        }
        return [];
    }

    // Optional: unified accessors (if you want to use directly in serialization later)
    public function getJobTypeValuesAttribute()
    {
        return $this->normalizeMultiValue(
            $this->job_type ??
            $this->getRawOriginal('job_type')
        );
    }

    public function getWorkEnvironmentValuesAttribute()
    {
        return $this->normalizeMultiValue(
            $this->work_environment ??
            $this->getRawOriginal('work_environment')
        );
    }

    protected function normalizeMultiValue($value): array
    {
        if (is_array($value)) return array_values(array_filter(array_map('trim',$value)));
        if (is_null($value) || $value === '') return [];
        if (is_string($value)) {
            // Try JSON
            $trim = trim($value);
            if ((str_starts_with($trim,'[') && str_ends_with($trim,']')) ||
                (str_starts_with($trim,'{') && str_ends_with($trim,'}'))) {
                $decoded = json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded)) {
                        // Flatten if associative with 'name'
                        if (isset($decoded[0]) || empty($decoded)) {
                            return array_values(array_filter(array_map(function($v){
                                if (is_array($v) && isset($v['name'])) return trim($v['name']);
                                if (is_string($v)) return trim($v);
                                return null;
                            }, $decoded)));
                        }
                    }
                }
            }
            // Fallback CSV
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }
        return [];
    }
}
