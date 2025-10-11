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
        // Run specific seeders
        $this->call([
            ActionSeeder::class,
        ]);
        
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
    }
}
