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
    /**
     * Display all roles with readable permissions
     */
    public function showrole()
    {
        $roles = Roles::with('users')->get();
        
        // Add readable permissions to each role for display
        foreach ($roles as $role) {
            $role->readable_permissions = $this->getReadablePermissions($role);
        }
        
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
            $permissions = Permission::with(['menu', 'menuItem', 'action'])->get();
            $groupedPermissions = $this->groupPermissionsByMenu($permissions);
            
            return view('dashboard.role.create', [
                'permissions' => $permissions,
                'groupedPermissions' => $groupedPermissions,
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
            $role->akses = json_encode($request->permissions ?? []);
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
            $permissions = Permission::with(['menu', 'menuItem', 'action'])->get();
            $assignedPermissions = $this->decodeRolePermissions($role->akses);
            $groupedPermissions = $this->groupPermissionsByMenu($permissions);
            
            return view('dashboard.role.edit', [
                'role' => $role,
                'permissions' => $permissions,
                'groupedPermissions' => $groupedPermissions,
                'assignedPermissions' => $assignedPermissions,
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

            // Prevent deleting SuperAdmin
            if (strtolower($role->role) === 'superadmin') {
                return redirect()->route('show.role')
                    ->with('error', 'Role Super Admin tidak dapat dihapus!');
            }

            // Check if role is still used by users
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
     * Group permissions by menu for display
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
                
                $permission->action_name = $permission->action->name ?? 'Unknown Action';
                $grouped[$menuName]['menu_items'][$menuItemName]['permissions'][] = $permission;
            } else {
                $permission->action_name = $permission->action->name ?? 'Unknown Action';
                $grouped[$menuName]['menu_permissions'][] = $permission;
            }
        }
        
        return $grouped;
    }

    /**
     * Decode role permissions from JSON with support for "Full access" and fixed parsing
     */
    private function decodeRolePermissions($akses)
    {
        // Handle full access string
        if ($akses === 'Full access' || $akses === 'full access' || $akses === true || $akses === 'true' || $akses === '1') {
            return Permission::pluck('id')->toArray();
        }
        
        // Handle array (Laravel cast)
        if (is_array($akses)) {
            return array_map('intval', $akses);
        }
        
        // Handle JSON string with enhanced parsing
        if (is_string($akses)) {
            $jsonString = $akses;
            
            // Remove outer quotes if present (double-quoted JSON)
            if (str_starts_with($jsonString, '"') && str_ends_with($jsonString, '"')) {
                $jsonString = substr($jsonString, 1, -1);
                // Unescape quotes
                $jsonString = str_replace('\\"', '"', $jsonString);
            }
            
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_map('intval', $decoded);
            }
        }
        
        return [];
    }

    /**
     * Get readable permissions for display with Full access support
     */
    private function getReadablePermissions($role)
    {
        // Handle full access
        if ($role->akses === 'Full access' || $role->akses === 'full access') {
            return collect(['Full Access' => ['All Permissions']]);
        }
        
        $permissionIds = $this->decodeRolePermissions($role->akses);
        
        if (empty($permissionIds)) {
            return collect();
        }
        
        return Permission::with(['menu', 'menuItem', 'action'])
                        ->whereIn('id', $permissionIds)
                        ->get()
                        ->groupBy(function($permission) {
                            $menuName = $permission->menu->name ?? 'Unknown Menu';
                            $menuItemName = $permission->menuItem->name ?? null;
                            
                            return $menuItemName ? 
                                $menuName . ' > ' . $menuItemName : 
                                $menuName;
                        })
                        ->map(function($permissions) {
                            return $permissions->pluck('action.name')->filter();
                        });
    }

    // ========== PUBLIC UTILITY METHODS ==========

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
        return in_array((int)$permissionId, $rolePermissions);
    }

    /**
     * Get user permissions with details
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
}