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

    /**
     * Get akses attribute dengan parsing yang robust
     */
    public function getAksesAttribute($value)
    {
        // Jika sudah array, return as is
        if (is_array($value)) {
            return $value;
        }

        // Handle "Full access" case
        if ($value === 'Full access' || $value === 'full access' || $value === '"Full access"') {
            return 'Full access';
        }

        // Handle JSON string
        if (is_string($value)) {
            // Remove outer quotes if present
            $cleanValue = trim($value, '"');
            
            // Try to decode as JSON
            $decoded = json_decode($cleanValue, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [];
            }
        }

        // Fallback to empty array
        return [];
    }

    /**
     * Set akses attribute dengan formatting yang benar
     */
    public function setAksesAttribute($value)
    {
        if ($value === 'Full access' || $value === 'full access') {
            $this->attributes['akses'] = 'Full access';
        } elseif (is_array($value)) {
            $this->attributes['akses'] = json_encode($value);
        } else {
            $this->attributes['akses'] = $value;
        }
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission($permissionId)
    {
        $permissions = $this->akses;
        
        // SuperAdmin has all permissions
        if ($permissions === 'Full access' || $permissions === 'full access') {
            return true;
        }
        
        // Check if permission ID exists in array
        if (is_array($permissions)) {
            return in_array($permissionId, $permissions);
        }
        
        return false;
    }
    
    // Model Role - Relasi ke User
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
