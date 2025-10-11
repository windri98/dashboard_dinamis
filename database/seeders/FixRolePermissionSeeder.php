<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class FixRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fix SuperAdmin role
        $superAdmin = Roles::where('role', 'SuperAdmin')->first();
        if ($superAdmin) {
            $superAdmin->akses = 'Full access';
            $superAdmin->save();
            echo "✅ SuperAdmin role fixed: Full access\n";
        }

        // Fix Admin role - give access to basic permissions
        $admin = Roles::where('role', 'Admin')->first();
        if ($admin) {
            $basicPermissions = Permission::whereIn('id', [1, 2, 3, 4])->pluck('id')->toArray();
            $admin->akses = $basicPermissions;
            $admin->save();
            echo "✅ Admin role fixed: " . json_encode($basicPermissions) . "\n";
        }

        // Show final result
        echo "\n=== FINAL ROLE STATUS ===\n";
        $roles = Roles::all();
        foreach ($roles as $role) {
            echo "Role: {$role->role}\n";
            echo "  Raw akses: {$role->getRawOriginal('akses')}\n";
            echo "  Processed akses: " . json_encode($role->akses) . "\n";
            echo "  Type: " . gettype($role->akses) . "\n\n";
        }
    }
}