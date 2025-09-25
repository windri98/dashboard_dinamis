<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Roles;
use App\Models\Action;
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
        // Insert default role
        $role = Roles::firstOrCreate([
            'role' => 'SuperAdmin',
        ], [
            'akses' => 'Full access',
        ]);

        // Insert default user
        User::firstOrCreate([
            'username' => 'Rudalpolo',
        ], [
            'nama' => 'Rudalpolo',
            'password' => Hash::make('Rudalpolo011'),
            'role_id' => $role->id,
        ]);

        // Actions to insert
        $actions = [
            ['slug' => 'view', 'nama' => 'View/Lihat'],
            ['slug' => 'create', 'nama' => 'Create/Tambah'],
            ['slug' => 'edit', 'nama' => 'Edit/Update'],
            ['slug' => 'delete', 'nama' => 'Delete/Hapus'],
        ];

        // Insert actions secara rapi
        foreach ($actions as $action) {
            Action::firstOrCreate(['slug' => $action['slug']], $action);
        }
    }
}
