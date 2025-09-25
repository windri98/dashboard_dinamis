<?php

use App\Models\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DynamicMenuController;
use App\Http\Controllers\DynamicTableController;
use App\Http\Controllers\auth\LoginRegisterController;

// Route::get('/', function () {
//     return view('homepage.home');
// });

// Authentication Routes
Route::get('/login', [LoginRegisterController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginRegisterController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginRegisterController::class, 'logout'])->name('logout');

// ---------------------------------------------------------------------------------Seadbar and menu-----------------------------------------------------------------------------------------------

Route::middleware(['auth'])->group(function () {
    
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Dashboard Routes
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/table/{table}', [DashboardController::class, 'showTable'])->name('table');
        Route::post('/table/{table}/store', [DashboardController::class, 'storeTableData'])->name('table.store');
        Route::put('/table/{table}/{id}', [DashboardController::class, 'updateTableData'])->name('table.update');
        Route::delete('/table/{table}/{id}', [DashboardController::class, 'destroyTableData'])->name('table.destroy');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        
        // Dynamic Menus Management
        Route::resource('dynamic-menus', DynamicMenuController::class); //create, read, update, delete dynamic menus
        Route::get('/dynamic-menus/{dynamicMenu}/items', [DynamicMenuController::class, 'menuItems'])->name('dynamic-menu-items');
        Route::post('/dynamic-menu-items', [DynamicMenuController::class, 'storeItem'])->name('dynamic-menu-items.store');
        Route::put('/dynamic-menu-items/{item}', [DynamicMenuController::class, 'updateItem'])->name('dynamic-menu-items.update');
        Route::delete('/dynamic-menu-items/{item}', [DynamicMenuController::class, 'destroyItem'])->name('dynamic-menu-items.destroy');
        Route::get('/dynamic-menu-items/{parentId}/submenus', [DynamicMenuController::class, 'submenus'])->name('dynamic-menu-items.submenus');

        // Dynamic Tables Management
        Route::resource('dynamic-tables', DynamicTableController::class); //create, read, update, delete dynamic tables
        Route::get('/dynamic-tables/{dynamicTable}/columns', [DynamicTableController::class, 'columns'])->name('dynamic-table-columns');
        Route::post('/dynamic-tables/{dynamicTable}/columns', [DynamicTableController::class, 'storeColumn'])->name('dynamic-table-columns.store');
        Route::put('/table-columns/{column}', [DynamicTableController::class, 'updateColumn'])->name('table-columns.update');
        Route::delete('/table-columns/{column}', [DynamicTableController::class, 'destroyColumn'])->name('table-columns.destroy');
    });

// ---------------------------------------------------------------------------------role-----------------------------------------------------------------------------------------------
    // Maintenance routes
    Route::post('/admin/permissions/cleanup', [DynamicMenuController::class, 'cleanupOrphanedPermissions']);
    Route::post('/admin/permissions/bulk-sync', [DynamicMenuController::class, 'bulkSyncPermissions']);
    Route::post('/admin/permissions/rebuild', [DynamicMenuController::class, 'rebuildAllPermissions']);
    Route::get('/admin/permissions/stats', [DynamicMenuController::class, 'getPermissionStats']);

    // roles
    Route::get('/showrole', [RolesController::class, 'showrole'])->name('show.role');
    Route::get('/addrole', [RolesController::class, 'addrole'])->name('add.role');
    Route::get('/role/{id}/edit', [RolesController::class, 'editrole'])->name('edit.role');

    // create, update, delete(role)
    Route::post('/create/role', [RolesController::class, 'createrole'])->name('create.role');
    Route::put('/role/update/{id}', [RolesController::class, 'updaterole'])->name('update.role');
    Route::delete('/role/delete/{id}', [RolesController::class, 'deleterole'])->name('delete.role');

    Route::get('/editrole/{id}', [RolesController::class, 'editrole'])->name('edit.role');
    Route::put('/updaterole/{id}', [RolesController::class, 'updaterole'])->name('update.role');
    // user
    Route::get('/showuser', [UserController::class, 'showuser'])->name('show.user');
    Route::get('/addtuser', [UserController::class, 'adduser'])->name('add.user');
    Route::get('/edituser/{id}/edit', [UserController::class, 'edituser'])->name('edit.user');
    Route::get('/user/password', [UserController::class, 'ubahpassword'])->name('ubah.password');

    // create, update, delete(user)
    Route::post('/create/user', [UserController::class, 'createuser'])->name('create.user');
    Route::put('/user/update/{id}', [UserController::class, 'updateuser'])->name('update.user');
    Route::delete('/user/delete/{id}', [UserController::class, 'deleteuser'])->name('delete.user');
    Route::put('/update/password', [UserController::class, 'updatepassword'])->name('update.password');

});
