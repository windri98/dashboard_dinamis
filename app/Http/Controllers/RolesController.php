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
            // Ambil semua permissions dengan relasi menu dan menu_item
            $permissions = Permission::with(['menu', 'menuItem'])->get();
            
            // Ambil actions dari database
            $actions = Action::all();
            
            // Group permissions berdasarkan menu
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
            $permissions = Permission::with(['menu', 'menuItem'])->get();
            $actions = Action::all();
            
            // Decode permissions dari JSON
            $assignedPermissions = $this->decodeRolePermissions($role->akses);
            
            // Group permissions berdasarkan menu
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
            
            // Inisialisasi grup menu jika belum ada
            if (!isset($grouped[$menuName])) {
                $grouped[$menuName] = [
                    'menu' => $permission->menu,
                    'menu_permissions' => [],
                    'menu_items' => []
                ];
            }
            
            // Jika permission untuk menu-item
            if ($permission->menuItem) {
                $menuItemName = $permission->menuItem->name;
                
                if (!isset($grouped[$menuName]['menu_items'][$menuItemName])) {
                    $grouped[$menuName]['menu_items'][$menuItemName] = [
                        'menu_item' => $permission->menuItem,
                        'permissions' => []
                    ];
                }
                
                $grouped[$menuName]['menu_items'][$menuItemName]['permissions'][] = $permission;
            } else {
                // Permission langsung untuk menu
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
}