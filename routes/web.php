<?php

use App\Models\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DynamicMenuController;
use App\Http\Controllers\DynamicTableController;
use App\Http\Controllers\auth\LoginRegisterController;

// Root Route - Redirect to appropriate page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
})->name('home');

// Authentication Routes (Public)
Route::get('/login', [LoginRegisterController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginRegisterController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginRegisterController::class, 'logout'])->name('logout');

// Protected Routes (Butuh Login & Permission)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Routes - No permission check needed (all authenticated users can access)
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/table/{table}', [DashboardController::class, 'showTable'])->name('table');
        Route::post('/table/{table}/store', [DashboardController::class, 'storeTableData'])->name('table.store');
        Route::put('/table/{table}/{id}', [DashboardController::class, 'updateTableData'])->name('table.update');
        Route::delete('/table/{table}/{id}', [DashboardController::class, 'destroyTableData'])->name('table.destroy');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        
        // Dynamic Menus Management - Permission: menus
        Route::middleware(['permission:menus'])->group(function () {
            Route::resource('dynamic-menus', DynamicMenuController::class);
            Route::get('/dynamic-menus/{dynamicMenu}/items', [DynamicMenuController::class, 'menuItems'])->name('dynamic-menu-items');
            Route::post('/dynamic-menu-items', [DynamicMenuController::class, 'storeItem'])->name('dynamic-menu-items.store');
            Route::put('/dynamic-menu-items/{item}', [DynamicMenuController::class, 'updateItem'])->name('dynamic-menu-items.update');
            Route::delete('/dynamic-menu-items/{item}', [DynamicMenuController::class, 'destroyItem'])->name('dynamic-menu-items.destroy');
            Route::get('/dynamic-menu-items/{parentId}/submenus', [DynamicMenuController::class, 'submenus'])->name('dynamic-menu-items.submenus');
        });

        // Dynamic Tables Management - Permission: tables
        Route::middleware(['permission:tables'])->group(function () {
            Route::resource('dynamic-tables', DynamicTableController::class);
            Route::get('/dynamic-tables/{dynamicTable}/columns', [DynamicTableController::class, 'columns'])->name('dynamic-table-columns');
            Route::post('/dynamic-tables/{dynamicTable}/columns', [DynamicTableController::class, 'storeColumn'])->name('dynamic-table-columns.store');
            Route::put('/table-columns/{column}', [DynamicTableController::class, 'updateColumn'])->name('table-columns.update');
            Route::delete('/table-columns/{column}', [DynamicTableController::class, 'destroyColumn'])->name('table-columns.destroy');
        });
        
        // Roles Management - Permission: roles
        Route::middleware(['permission:roles'])->group(function () {
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [RolesController::class, 'index'])->name('index');
                Route::get('/create', [RolesController::class, 'create'])->name('create');
                Route::post('/', [RolesController::class, 'store'])->name('store');
                Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('edit');
                Route::put('/{role}', [RolesController::class, 'update'])->name('update');
                Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');
            });
        });

        // Users Management - Permission: users
        Route::middleware(['permission:users'])->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                
                // Password Management (semua user yg login bisa akses ini)
                Route::withoutMiddleware(['permission:users'])->group(function () {
                    Route::get('/password/change', [UserController::class, 'changePassword'])->name('password.change');
                    Route::put('/password/update', [UserController::class, 'updatePassword'])->name('password.update');
                });
            });
        });

        // Permissions Maintenance - Permission: permissions
        Route::middleware(['permission:permissions'])->group(function () {
            Route::prefix('permissions')->name('permissions.')->group(function () {
                Route::post('/cleanup', [DynamicMenuController::class, 'cleanupOrphanedPermissions'])->name('cleanup');
                Route::post('/bulk-sync', [DynamicMenuController::class, 'bulkSyncPermissions'])->name('bulk-sync');
                Route::post('/rebuild', [DynamicMenuController::class, 'rebuildAllPermissions'])->name('rebuild');
                Route::get('/stats', [DynamicMenuController::class, 'getPermissionStats'])->name('stats');
            });
        });

        // API Management - Permission: api (or permissions for now)
        Route::middleware(['permission:permissions'])->group(function () {
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/', [App\Http\Controllers\ApiManagementController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\ApiManagementController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\ApiManagementController::class, 'store'])->name('store');
                Route::get('/{apiEndpoint}', [App\Http\Controllers\ApiManagementController::class, 'show'])->name('show');
                Route::get('/{apiEndpoint}/edit', [App\Http\Controllers\ApiManagementController::class, 'edit'])->name('edit');
                Route::put('/{apiEndpoint}', [App\Http\Controllers\ApiManagementController::class, 'update'])->name('update');
                Route::delete('/{apiEndpoint}', [App\Http\Controllers\ApiManagementController::class, 'destroy'])->name('destroy');
                Route::patch('/{apiEndpoint}/toggle', [App\Http\Controllers\ApiManagementController::class, 'toggleStatus'])->name('toggle');
                Route::post('/generate', [App\Http\Controllers\ApiManagementController::class, 'generateForTable'])->name('generate');
            });
        });
    });
});

// Include debug routes if needed
if (app()->environment(['local', 'staging'])) {
    require __DIR__ . '/debug.php';
}