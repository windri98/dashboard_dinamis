<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        
        $this->call(DynamicMenuSeeder::class);

        // Insert default user
        DB::table('users')->insert([
            'nama' => 'Rudalpolo',
            'username' => 'Rudalpolo',
            'password' => Hash::make('Rudalpolo011'),
            'role_id' => 1,
        ]);

        // Insert default role
        DB::table('roles')->insert([
            'role' => 'SuperAdmin',
            'akses' => 'Full access',
        ]);

    //     DB::table('modules')
    //     ->where('slug', 'manage_roles') // slug lama
    //     ->update([
    //         'slug' => 'role_management',       // slug baru
    //         'name' => 'Manajemen Role Baru',   // nama baru
    // ]);

    }
}
