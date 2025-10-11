<?php

use Illuminate\Support\Facades\Route;
use App\Models\Roles;
use App\Models\Permission;
use App\Models\DynamicMenu;

// Debug route untuk troubleshoot permission issues
Route::get('/debug-permissions', function() {
    if (!auth()->check()) {
        return 'Please login first';
    }

    $user = auth()->user();
    $role = Roles::find($user->role_id);
    
    $output = [
        'user' => [
            'id' => $user->id,
            'nama' => $user->nama,
            'role_id' => $user->role_id
        ],
        'role' => [
            'id' => $role->id,
            'name' => $role->role,
            'akses_raw' => $role->getRawOriginal('akses'),
            'akses_processed' => $role->akses,
            'akses_type' => gettype($role->akses)
        ],
        'permissions' => Permission::all(),
        'dynamic_menus' => DynamicMenu::with('permissions')->get(),
    ];

    return response()->json($output, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth')->name('debug.permissions');