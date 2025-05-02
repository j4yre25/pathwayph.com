<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    public function user() {
        return $this->belongsTo(User::class);
    }
    
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
    protected $fillable = ['name', 'user_id','degree_id'];

}
