<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'user_id',
        'sector_id',    
        'division_code'
        
        
    ];

    public function sector() 
    {
        return $this->belongsTo(Sector::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
