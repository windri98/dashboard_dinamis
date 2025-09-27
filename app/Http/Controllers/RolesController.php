<?php

namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use App\Models\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function showrole()
    {
        $roles = Roles::with('users')->get();
        return view('dashboard.role.roles', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show form to add new role
     */
        public function addrole()
    {
        try {
            // Tambahkan 'action' ke with()
            $permissions = Permission::with(['menu', 'menuItem', 'action'])->get();
            $actions = Action::all();
            
            $groupedPermissions = $this->groupPermissionsByMenu($permissions);
            return view('dashboard.role.create', [
                'permissions' => $permissions,
                'groupedPermissions' => $groupedPermissions,
                'actions' => $actions
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading create role: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created role
     */
    public function createrole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:255|unique:roles,role',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $role = new Roles();
            $role->role = $request->role;
            $role->akses = $request->permissions ?? [];
            $role->save();
            
            DB::commit();
            
            return redirect()->route('show.role')
                ->with('success', 'Role berhasil dibuat!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal membuat role: ' . $e->getMessage()]);
        }
    }

    /**
     * Show form to edit role
     */
        public function editrole($id)
    {
        try {
            $role = Roles::findOrFail($id);
            // Tambahkan 'action' ke with()
            $permissions = Permission::with(['menu', 'menuItem', 'action'])->get();
            $actions = Action::all();
            
            $assignedPermissions = $this->decodeRolePermissions($role->akses);
            $groupedPermissions = $this->groupPermissionsByMenu($permissions);
            
            return view('dashboard.role.edit', [
                'role' => $role,
                'permissions' => $permissions,
                'groupedPermissions' => $groupedPermissions,
                'assignedPermissions' => $assignedPermissions,
                'actions' => $actions
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading edit role: ' . $e->getMessage()]);
        }
    }


    /**
     * Update role
     */
    public function updaterole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|max:255|unique:roles,role,'. $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $role = Roles::findOrFail($id);
            $role->role = $request->role;
            $role->akses = json_encode($request->permissions ?? []);
            $role->save();
            
            DB::commit();
            
            return redirect()->route('show.role')
                ->with('success', 'Role berhasil diperbarui!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Gagal memperbarui role: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete role
     */
    public function deleterole($id)
    {
        try {
            $role = Roles::findOrFail($id);

            // Cegah hapus SuperAdmin
            if (strtolower($role->role) === 'superadmin') {
                return redirect()->route('show.role')
                    ->with('error', 'Role Super Admin tidak dapat dihapus!');
            }

            // Cek apakah role masih digunakan user
            if ($role->users()->count() > 0) {
                return redirect()->route('show.role')
                    ->with('error', 'Role masih digunakan oleh ' . $role->users()->count() . ' user!');
            }

            $role->delete();
            return redirect()->route('show.role')
                ->with('success', 'Role berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting role: ' . $e->getMessage()]);
        }
    }

    // ========== HELPER METHODS ==========

    /**
     * Group permissions berdasarkan menu untuk tampilan
     */
        private function groupPermissionsByMenu($permissions)
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $menuName = $permission->menu->name ?? 'Unknown Menu';
            
            if (!isset($grouped[$menuName])) {
                $grouped[$menuName] = [
                    'menu' => $permission->menu,
                    'menu_permissions' => [],
                    'menu_items' => []
                ];
            }
            
            if ($permission->menuItem) {
                $menuItemName = $permission->menuItem->name;
                
                if (!isset($grouped[$menuName]['menu_items'][$menuItemName])) {
                    $grouped[$menuName]['menu_items'][$menuItemName] = [
                        'menu_item' => $permission->menuItem,
                        'permissions' => []
                    ];
                }
                
                // Tambahkan info action name ke permission
                $permission->action_name = $permission->action->name ?? 'Unknown Action';
                $grouped[$menuName]['menu_items'][$menuItemName]['permissions'][] = $permission;
            } else {
                // Permission langsung untuk menu
                $permission->action_name = $permission->action->name ?? 'Unknown Action';
                $grouped[$menuName]['menu_permissions'][] = $permission;
            }
        }
        
        return $grouped;
    }

    /**
     * Decode role permissions dari JSON
     */
    private function decodeRolePermissions($akses)
    {
        // Handle full access
        if ($akses === true || $akses === 'true' || $akses === '1') {
            return Permission::pluck('id')->toArray();
        }
        
        // Handle JSON string
        if (is_string($akses)) {
            $decoded = json_decode($akses, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        // Handle array
        if (is_array($akses)) {
            return $akses;
        }
        
        return [];
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $permissionId)
    {
        $user = User::with('role')->find($userId);
        if (!$user || !$user->role) {
            return false;
        }

        $rolePermissions = $this->decodeRolePermissions($user->role->akses);
        return in_array($permissionId, $rolePermissions);
    }

    /**
     * Get user permissions dengan detail
     */
    public function getUserPermissions($userId)
    {
        $user = User::with('role')->find($userId);
        if (!$user || !$user->role) {
            return collect();
        }

        $rolePermissions = $this->decodeRolePermissions($user->role->akses);
        
        return Permission::with(['menu', 'menuItem', 'action'])
                        ->whereIn('id', $rolePermissions)
                        ->get();
    }

    /**
 * Get permissions dalam format readable
 */
    public function getReadablePermissions($roleId)
    {
        $role = Roles::findOrFail($roleId);
        $permissionIds = $this->decodeRolePermissions($role->akses);

        return Permission::with(['menu', 'menuItem', 'action'])
                            ->whereIn('id', $permissionIds)
                            ->get()
                            ->map(function($permission) {
                                return [
                                    'id' => $permission->id,
                                    'menu' => $permission->menu->name ?? 'Unknown',
                                    'menu_item' => $permission->menuItem->name ?? null,
                                    'action' => $permission->action->name ?? 'Unknown',
                                    'full_name' => sprintf('%s%s - %s', 
                                        $permission->menu->name ?? 'Unknown',
                                        $permission->menuItem ? ' > ' . $permission->menuItem->name : '',
                                        $permission->action->name ?? 'Unknown'
                                    )
                                ];
                            });
    }
}
