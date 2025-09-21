<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'actions';
    protected $fillable = [
        'slug', 
        'name'
    ];

    //Relasi ke Permission
    public function permissions()
    {
        return $this->hasMany(Permission::class);  
    }
}
