<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'role', 
        'akses'
        
    ];
    protected $casts = [
        'akses' => 'array'  // Otomatis convert JSON ke array
    ];
    
    public function hasPermission($permissionId)
    {
        $permissions = $this->akses ?? [];
        return in_array($permissionId, $permissions);
    }
    // Model Role
    public function users()
    {
        return $this->hasMany(User::class , 'role_id');
    }

        // Model User
    public function role()
    {
        return $this->hasMany(User::class , 'role_id');
    }

    public function permission()
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }

}
