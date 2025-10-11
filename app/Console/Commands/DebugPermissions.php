<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Action;
use App\Models\DynamicMenu;
use App\Models\Permission;
use App\Models\Roles;

class DebugPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug permission system data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== DEBUGGING PERMISSION SYSTEM ===\n");

        $this->info("1. ACTIONS:");
        $actions = Action::all();
        foreach ($actions as $action) {
            $this->line("   ID: {$action->id} | Slug: {$action->slug} | Nama: {$action->nama}");
        }

        $this->info("\n2. DYNAMIC MENUS:");
        $menus = DynamicMenu::all();
        foreach ($menus as $menu) {
            $this->line("   ID: {$menu->id} | Name: {$menu->name} | Permission Key: {$menu->permission_key}");
        }

        $this->info("\n3. PERMISSIONS:");
        $permissions = Permission::with(['menu', 'action'])->get();
        foreach ($permissions as $perm) {
            $menuName = $perm->menu->name ?? 'NULL';
            $actionSlug = $perm->action->slug ?? 'NULL';
            $this->line("   ID: {$perm->id} | Menu: {$menuName} | Action: {$actionSlug}");
        }

        $this->info("\n4. ROLES:");
        $roles = Roles::all();
        foreach ($roles as $role) {
            $this->line("   ID: {$role->id} | Role: {$role->role} | Akses: " . json_encode($role->akses));
        }

        $this->info("\n=== MASALAH YANG DITEMUKAN ===");
        
        // Check if there are menus without permission_key
        $menusWithoutPermissionKey = DynamicMenu::whereNull('permission_key')->orWhere('permission_key', '')->get();
        if ($menusWithoutPermissionKey->count() > 0) {
            $this->error("❌ Ditemukan {$menusWithoutPermissionKey->count()} menu tanpa permission_key:");
            foreach ($menusWithoutPermissionKey as $menu) {
                $this->line("   - {$menu->name} (ID: {$menu->id})");
            }
        }

        // Check if there are permissions without proper relations
        $orphanedPermissions = Permission::with(['menu', 'action'])
            ->get()
            ->filter(function($perm) {
                return !$perm->menu || !$perm->action;
            });
            
        if ($orphanedPermissions->count() > 0) {
            $this->error("❌ Ditemukan {$orphanedPermissions->count()} permission tanpa relasi yang lengkap:");
            foreach ($orphanedPermissions as $perm) {
                $this->line("   - Permission ID: {$perm->id} | Menu: " . ($perm->menu ? $perm->menu->name : 'NULL') . " | Action: " . ($perm->action ? $perm->action->slug : 'NULL'));
            }
        }

        $this->info("\n=== END DEBUG ===");
        return 0;
    }
}
