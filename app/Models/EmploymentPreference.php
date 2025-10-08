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

    // Relationships kept (job types & work environments still via pivot)
    public function user(){ return $this->belongsTo(User::class); }
    public function graduate(){ return $this->belongsTo(Graduate::class); }
    public function jobTypes(){ return $this->belongsToMany(JobType::class, 'employment_preference_job_type'); }
    // Removed: locations() pivot
    public function workEnvironments(){ return $this->belongsToMany(WorkEnvironment::class, 'employment_preference_work_environment'); }
    public function salary(){ return $this->belongsTo(Salary::class, 'salary_id'); }

    public function getJobTypesArrayAttribute()
    {
        if ($this->relationLoaded('jobTypes') && $this->jobTypes->count()) {
            return $this->jobTypes->map(fn($jt) => ['name'=>$jt->name ?? $jt->type])->values();
        }
        $raw = $this->job_type;
        if (!$raw) return collect();
        return collect(is_array($raw) ? $raw : (is_string($raw) ? explode(',',$raw) : []))
            ->map(fn($v)=>['name'=>trim($v)])->values();
    }

    public function getWorkEnvironmentsArrayAttribute()
    {
        if ($this->relationLoaded('workEnvironments') && $this->workEnvironments->count()) {
            return $this->workEnvironments->map(fn($we) => ['name'=>$we->name ?? $we->environment_type])->values();
        }
        $raw = $this->work_environment;
        if (!$raw) return collect();
        return collect(is_array($raw) ? $raw : (is_string($raw) ? explode(',',$raw) : []))
            ->map(fn($v)=>['name'=>trim($v)])->values();
    }

    public function getLocationsArrayAttribute()
    {
        $raw = $this->location;
        if (!$raw) return collect();
        return collect(is_array($raw) ? $raw : (is_string($raw) ? explode(',',$raw) : []))
            ->map(fn($v)=>['address'=>trim($v)])->filter(fn($r)=>$r['address']!=='')->values();
    }
}
