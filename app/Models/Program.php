<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'degree_id'];

    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }

    public function getFormattedNameAttribute()
    {
        $prefix = match ($this->degree->type) {
            'Bachelor' => 'BS in',
            'Associate' => 'AS in',
            'Diploma' => 'Diploma in',
            'Master' => 'Master in',
            'Doctoral' => 'Doctor in',
            default => '',
        };

        return $prefix . ' ' . $this->name;
    }
}
