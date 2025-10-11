<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\Permission;
use App\Models\Action;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['key' => 'users', 'name' => 'Users Management'],
            ['key' => 'roles', 'name' => 'Roles Management'], 
            ['key' => 'menus', 'name' => 'Menus Management'],
            ['key' => 'tables', 'name' => 'Tables Management'],
            ['key' => 'permissions', 'name' => 'Permissions Management'],
        ];

        $actions = Action::whereIn('slug', ['read', 'create', 'edit', 'delete'])->get();

        foreach ($menus as $menuData) {
            // Create menu
            $menu = DynamicMenu::firstOrCreate(['permission_key' => $menuData['key']], [
                'name' => $menuData['name'],
                'icon' => 'fas fa-cog',
                'permission_key' => $menuData['key'],
                'category' => 'settings',
                'order' => 10,
                'is_active' => true
            ]);

            echo "Menu {$menuData['name']} created/found with ID: {$menu->id}\n";

            // Create permissions for each action
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate([
                    'menu_id' => $menu->id,
                    'action_id' => $action->id,
                    'menu_item_id' => null
                ]);
                
                echo "  Permission {$menuData['key']}.{$action->slug} created with ID: {$permission->id}\n";
            }
        }
    }
}