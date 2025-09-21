<?php

namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\Roles;
use App\Models\Module;
use App\Models\Action;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function showrole()
    {
        $roles = Roles::with('users')->get();
        
        $dynmenu = DynamicMenu::all();
        
        return view('dashboard.role.roles', [
            'roles' => $roles,
            'dynmenu' => $dynmenu

        ]);
    } 

    /**
     * Store a newly created role in storage.
     */

    public function addrole(){

    $modules = DynamicMenu::all();
    // $actions = Action::all();
    $actions = [
        'read' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Edit',
        'delete' => 'Hapus'
    ];
    return view('dashboard.role.create', [
        'modules' => $modules,
        'actions' => $actions
    ]);
    }

        public function createrole(Request $request)
    {
        $request->validate([
            'role' => 'required|unique:roles',
            'permissions' => 'array',
        ]);

        $permissions = [];

        if ($request->has('permissions')) {
            foreach ($request->permissions as $module => $actions) {
                if (!empty($actions)) {
                    $permissions[$module] = $actions;
                }
            }
        }

        // Jika semua modul memiliki semua action, tandai sebagai Full Access
        $allModules = DynamicMenu::pluck('permission_key')->toArray();
        
        $allActions = ['read', 'create', 'edit', 'delete'];

        $fullAccess = true;

        foreach ($allModules as $module) {
            if (!isset($permissions[$module]) || array_diff($allActions, $permissions[$module])) {
                $fullAccess = false;
                break;
            }
        }

        if ($fullAccess) {
            // Tandai full access dengan true
            $permissions = true;
        }

        Roles::create([
            'role' => $request->role,
            'akses' => json_encode($permissions),
        ]);

        return redirect()->route('show.role')->with('success', 'Role berhasil ditambahkan!');
    }




    /**
     * Show the form for editing the specified role.
     */
        public function editrole($id)
    {
        $role = Roles::findOrFail($id);

        // if (strtolower($role->role) === 'superadmin') {
        //     return redirect()->route('show.role')
        //                     ->with('error', 'Role Super Admin tidak dapat diedit!');
        // }

        // Decode akses ke array
        $role->akses = is_string($role->akses) 
            ? json_decode($role->akses, true) 
            : ($role->akses ?? []);

        // Ambil semua module dari DB
        $permissionModules = DynamicMenu::all();

        // Aksi standar
        $actions = [
            'read' => 'Lihat',
            'create' => 'Tambah',
            'edit' => 'Edit',
            'delete' => 'Hapus'
        ];

        return view('dashboard.role.edit', [
            'role' => $role,
            'permissionModules' => $permissionModules,
            'actions' => $actions,
            'existingPermissions' => $role->akses
        ]);
    }

    /**
     * Update the specified role in storage.
     */
        public function updaterole(Request $request, $id)
    {
        $role = Roles::findOrFail($id);

        // if (strtolower($role->role) === 'superadmin') {
        //     return redirect()->route('show.role')
        //                     ->with('error', 'Role Super Admin tidak dapat diubah!');
        // }

        $request->validate([
            'role' => 'required|unique:roles,role,' . $role->id,
            'permissions' => 'array',
        ]);

        $permissions = [];

        if ($request->has('permissions')) {
            foreach ($request->permissions as $module => $actions) {
                if (!empty($actions)) {
                    $permissions[$module] = $actions;
                }
            }
        }

        // Cek full access
        $allModules = DynamicMenu::pluck('slug')->toArray();
        $allActions = ['read', 'create', 'edit', 'delete'];

        $fullAccess = true;

        foreach ($allModules as $module) {
            if (!isset($permissions[$module]) || array_diff($allActions, $permissions[$module])) {
                $fullAccess = false;
                break;
            }
        }

        if ($fullAccess) {
            $permissions = true;
        }

        $role->update([
            'role' => $request->role,
            'akses' => json_encode($permissions),
        ]);

        return redirect()->route('show.role')
                        ->with('success', 'Role berhasil diperbarui!');
    }

    /**
     * Remove the specified role from storage.
     */
        public function deleterole($id)
    {
        $role = Roles::find($id);
        if (!$role) {
            return redirect()->route('show.role')->with('error', 'Role tidak ditemukan!');
        }

        // Cegah hapus SuperAdmin
        if (strtolower($role->role) === 'superadmin') {
            return redirect()->route('show.role')->with('error', 'Role Super Admin tidak dapat dihapus!');
        }

        $role->delete();
        return redirect()->route('show.role')->with('success', 'Role berhasil dihapus!');
    }

}
