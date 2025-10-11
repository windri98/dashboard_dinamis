<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Action;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            ['slug' => 'read', 'nama' => 'View/Lihat'],
            ['slug' => 'create', 'nama' => 'Create/Tambah'],
            ['slug' => 'edit', 'nama' => 'Edit/Update'], 
            ['slug' => 'delete', 'nama' => 'Delete/Hapus'],
        ];

        foreach ($actions as $action) {
            Action::firstOrCreate(
                ['slug' => $action['slug']], 
                ['nama' => $action['nama']]
            );
        }
    }
}