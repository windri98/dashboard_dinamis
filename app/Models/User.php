<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }

    // Ambil semua permission user (via role)
    public function permissions()
    {
        if ($this->role) {
            return $this->role->permissions ?? collect([]);
        }

        return collect([]);
    }

    // Helper: cek apakah user punya permission tertentu
    public function hasPermission($moduleSlug, $actionSlug): bool
    {
        return $this->permissions()->contains(function ($perm) use ($moduleSlug, $actionSlug) {
            return $perm->module->slug === $moduleSlug && $perm->action->slug === $actionSlug;
        });
    }



































    /**
     * Check if the user has a specific permission
     *
     * @param string $permission The permission to check
     * @return bool
     */
    // public function hasPermission($permission)
    // {
    //     // SuperAdmin memiliki semua izin
    //     if ($this->role_id == 1) {
    //         return true;
    //     }

    //     // Ambil role pengguna
    //     $role = $this->roles;

    //     // Jika tidak ada role atau akses, kembalikan false
    //     if (!$role || !$role->akses) {
    //         return false;
    //     }

    //     // Parse permissions dari role
    //     $permissions = json_decode($role->akses, true) ?: [];

    //     // Format 1: Permission dalam format manage-module.action
    //     if (strpos($permission, '.') !== false) {
    //         $parts = explode('.', $permission);

    //         // Format manage-module.action.operation (misalnya manage-bukti-jackpot.buktijackpot.read)
    //         if (count($parts) === 3) {
    //             $module = $parts[0] . '.' . $parts[1]; // manage-bukti-jackpot.buktijackpot

    //             // Cek apakah ada akses ke module (boolean true)
    //             if (isset($permissions[$module]) && $permissions[$module] === true) {
    //                 return true;
    //             }

    //             // Cek dengan format lengkap
    //             if (isset($permissions[$permission]) && $permissions[$permission] === true) {
    //                 return true;
    //             }
    //         }
    //         // Format manage-module.action (misalnya manage-bukti-jackpot.buktijackpot)
    //         else if (count($parts) === 2) {
    //             // Cek langsung
    //             if (isset($permissions[$permission]) && $permissions[$permission] === true) {
    //                 return true;
    //             }
    //         }
    //     }
    //     // Format 2: Permission dalam format lama (read, create, update, delete)
    //     else {
    //         // Jika izin disimpan sebagai array sederhana (backwards compatibility)
    //         if (isset($permissions[0])) {
    //             return in_array($permission, $permissions);
    //         }
    //     }

    //     return false;
    // }

    /**
     * Check if the user has any of the given permissions
     *
     * @param array|string $permissions
     * @return bool
     */
    // public function hasAnyPermission($permissions)
    // {
    //     if (is_string($permissions)) {
    //         return $this->hasPermission($permissions);
    //     }

    //     foreach ($permissions as $permission) {
    //         if ($this->hasPermission($permission)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    /**
     * Check if the user has all the given permissions
     *
     * @param array|string $permissions
     * @return bool
     */
    // public function hasAllPermissions($permissions)
    // {
    //     if (is_string($permissions)) {
    //         return $this->hasPermission($permissions);
    //     }

    //     foreach ($permissions as $permission) {
    //         if (!$this->hasPermission($permission)) {
    //             return false;
    //         }
    //     }

    //     return true;
    // }

    // Remove this line since migration uses timestamps
    // public $timestamps = false;

}
