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

    public function users()
    {
        return $this->hasMany(User::class , 'role_id');
    }

    public function hasPermission($permission)
    {
        // Asumsikan akses disimpan sebagai JSON atau array serialized
        $permissions = json_decode($this->akses, true);
        
        // Jika akses bukan array valid, kembalikan false
        if (!is_array($permissions)) {
            return false;
        }
        
        // Periksa apakah permission tertentu ada dalam array akses
        return in_array($permission, $permissions);
    }


    
    public function role()
    {
        return $this->hasMany(User::class , 'role_id');
    }




    // Relasi ke Permission (many-to-many)
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,      // model terkait
            'role_permission',      // nama pivot table
            'role_id',              // foreign key di pivot untuk Role
            'permission_id'         // foreign key di pivot untuk Permission
        );
    }

}
