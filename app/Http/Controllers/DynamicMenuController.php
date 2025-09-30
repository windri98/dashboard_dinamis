<?php

namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\DynamicMenuItem;
use App\Models\DynamicTable;
use App\Models\Permission;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicMenuController extends Controller
{
    // ==========================================================================================
    // Menu Management
    // ==========================================================================================

    public function index()
    {
        $menus = DynamicMenu::with(['items' => function($query) {
            $query->withCount(['dynamicTable' => function($q) {
                $q->where('link_type', 'table');
            }]);
        }])->ordered()->get();
        
        return view('settings.dynamic-menus.index', [
            'menus' => $menus
        ]);
    }

    public function create()
    {
        return view('settings.dynamic-menus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:dynamic_menus,name',
            'icon'           => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255|unique:dynamic_menus,permission_key',
            'category'       => 'required|in:main,settings',
            'order'          => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Buat menu baru
            $menu = DynamicMenu::create($validated);

            // Auto-sync semua permissions untuk menu baru (recommended approach)
            $this->syncMenuPermissions($menu);
            
            DB::commit();
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('success', 'Menu berhasil ditambahkan dan permissions telah dibuat');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create menu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan menu: ' . $e->getMessage());
        }
    }
    

    public function show(DynamicMenu $dynamicMenu)
    {
        $dynamicMenu->load('items');
        return view('settings.dynamic-menus.show', [
            'dynamicMenu' => $dynamicMenu
        ]);
    }

    public function edit(DynamicMenu $dynamicMenu)
    {
        return view('settings.dynamic-menus.edit', [
            'dynamicMenu' => $dynamicMenu
        ]);
    }

    public function update(Request $request, DynamicMenu $dynamicMenu)
    {
    
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:dynamic_menus,name,' . $dynamicMenu->id,
            'icon' => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255|unique:dynamic_menus,permission_key,' . $dynamicMenu->id,
            'category' => 'required|in:main,settings',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            $dynamicMenu->update($validated);
            
            // Re-sync permissions setelah update
            $this->syncMenuPermissions($dynamicMenu);
            
            DB::commit();
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('success', 'Menu berhasil diperbarui dan permissions telah di-sync');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update menu', [
                'menu_id' => $dynamicMenu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui menu: ' . $e->getMessage());
        }
    }

    public function destroy(DynamicMenu $dynamicMenu)
    {
        DB::beginTransaction();
        
        try {
            // Hapus permissions terkait menu ini dulu (cascade akan handle ini juga)
            Permission::where('menu_id', $dynamicMenu->id)->delete();
            
            $dynamicMenu->delete();
            DB::commit();
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('success', 'Menu dan permissions terkait berhasil dihapus');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to delete menu', [
                'menu_id' => $dynamicMenu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('error', 'Gagal menghapus menu: ' . $e->getMessage());
        }
    }
    
    // ==========================================================================================
    // Menu Items Management
    // ==========================================================================================

    public function menuItems(DynamicMenu $dynamicMenu)
    {
        $dynamicMenu->load('items');
        $tables = DynamicTable::active()->get(); // Ambil semua tabel aktif jika tabe tidak aktif, tidak muncul di pilihan
        return view('settings.dynamic-menus.items', [
            'dynamicMenu' => $dynamicMenu,
            'tables' => $tables
        ]);
    }

    public function submenus($parentId)
    {
        $submenus = DynamicMenuItem::where('menu_id', $parentId)->get();

        return response()->json([
            'success' => true,
            'submenus' => $submenus->map(function ($submenu) {
                return [
                    'id' => $submenu->id,
                    'nama' => $submenu->nama,
                    'icon' => $submenu->icon ?? 'fas fa-circle',
                    'table_name' => $submenu->table_name ?? '-',
                ];
            }),
        ]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'dynamic_menu_id' => 'required|exists:dynamic_menus,id',
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'link_type' => 'required|in:table,route,url',
            'link_value' => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create menu item
            $menuItem = DynamicMenuItem::create($validated);
            
            // Auto-sync permissions untuk menu item baru
            $this->syncMenuItemPermissions($menuItem);
            
            DB::commit();

            return back()->with('success', 'Item menu berhasil ditambahkan dan permissions telah dibuat');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to create menu item', [
                'menu_id' => $validated['dynamic_menu_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan item menu: ' . $e->getMessage());
        }
    }

    public function updateItem(Request $request, DynamicMenuItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'link_type' => 'required|in:table,route,url',
            'link_value' => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            // Update menu item
            $item->update($validated);
            
            // Re-sync permissions setelah update
            $this->syncMenuItemPermissions($item);
            
            DB::commit();

            return back()->with('success', 'Item menu berhasil diperbarui dan permissions telah di-sync');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update menu item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui item menu: ' . $e->getMessage());
        }
    }

    public function destroyItem(DynamicMenuItem $item)
    {
        DB::beginTransaction();
        
        try {
            // Hapus permissions terkait menu item ini dulu
            Permission::where('menu_item_id', $item->id)->delete();
            
            // Hapus menu item
            $item->delete();
            
            DB::commit();
            
            return back()->with('success', 'Item menu dan permissions terkait berhasil dihapus');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to delete menu item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal menghapus item menu: ' . $e->getMessage());
        }
    }

    // ==========================================================================================
    // Permission Sync Methods
    // ==========================================================================================

    /**
     * Sync permissions untuk menu utama
     * Membuat permissions berdasarkan actions yang tersedia
     */
    private function syncMenuPermissions(DynamicMenu $menu)
    {
        $actions = Action::all();
        
        foreach ($actions as $action) {
            // Cek permission untuk menu utama (tanpa menu_item_id)
            $existingPermission = Permission::where('menu_id', $menu->id)
                                        ->whereNull('menu_item_id')
                                        ->where('action_id', $action->id)
                                        ->first();
            
            if (!$existingPermission) {
                Permission::create([
                    'menu_id' => $menu->id,
                    'menu_item_id' => null,
                    'action_id' => $action->id,
                ]);
            }
        }
    }

    /**
     * Sync permissions untuk menu item
     * Update existing permissions (menu_item_id = NULL) atau create baru jika belum ada
     */
    private function syncMenuItemPermissions(DynamicMenuItem $menuItem)
    {
        $actions = Action::all();

        foreach ($actions as $action) {
            // Cari permission yang sudah ada dengan menu_item_id = NULL
            $existingPermission = Permission::where('menu_id', $menuItem->dynamic_menu_id)
                ->whereNull('menu_item_id')
                ->where('action_id', $action->id)
                ->first();

            if ($existingPermission) {
                // UPDATE: Isi menu_item_id yang tadinya NULL
                $existingPermission->update([
                    'menu_item_id' => $menuItem->id
                ]);
            } else {
                // CREATE: Kalau memang belum ada sama sekali
                Permission::create([
                    'menu_id' => $menuItem->dynamic_menu_id,
                    'menu_item_id' => $menuItem->id,
                    'action_id' => $action->id,
                ]);
            }
        }
    }

    // ==========================================================================================
    // Maintenance & Utility Methods
    // ==========================================================================================

    /**
     * Method untuk cleanup permissions yang orphaned
     * Menghapus permissions yang referensinya sudah tidak valid
     */
    public function cleanupOrphanedPermissions()
    {
        try {
            DB::beginTransaction();

            // Hapus permissions yang menu-nya sudah tidak ada
            $deletedMenus = Permission::whereNotIn('menu_id', DynamicMenu::pluck('id'))->delete();
            
            // Hapus permissions yang menu_item-nya sudah tidak ada
            $deletedMenuItems = Permission::whereNotNull('menu_item_id')
                                        ->whereNotIn('menu_item_id', DynamicMenuItem::pluck('id'))
                                        ->delete();
        
            // Hapus permissions yang action-nya sudah tidak ada
            $deletedActions = Permission::whereNotNull('action_id')
                                    ->whereNotIn('action_id', Action::pluck('id'))
                                    ->delete();
        
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orphaned permissions berhasil dibersihkan',
                'deleted' => [
                    'orphaned_menus' => $deletedMenus,
                    'orphaned_menu_items' => $deletedMenuItems,
                    'orphaned_actions' => $deletedActions
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to cleanup orphaned permissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal membersihkan orphaned permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk bulk sync semua permissions
     * Berguna untuk initial setup atau maintenance
     */
    public function bulkSyncPermissions()
    {
        try {
            DB::beginTransaction();

            $syncedMenus = 0;
            $syncedMenuItems = 0;

            // Sync permissions untuk semua menu utama
            $menus = DynamicMenu::all();
            foreach ($menus as $menu) {
                $this->syncMenuPermissions($menu);
                $syncedMenus++;
            }
            
            // Sync permissions untuk semua menu items
            $menuItems = DynamicMenuItem::all();
            foreach ($menuItems as $menuItem) {
                $this->syncMenuItemPermissions($menuItem);
                $syncedMenuItems++;
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua permissions berhasil di-sync',
                'synced' => [
                    'menus' => $syncedMenus,
                    'menu_items' => $syncedMenuItems,
                    'total_permissions' => Permission::count()
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to bulk sync permissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal sync permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk rebuild semua permissions dari awal
     * Menghapus semua permissions lama dan membuat ulang
     */
    public function rebuildAllPermissions()
    {
        try {
            DB::beginTransaction();

            // Hapus semua permissions yang ada
            Permission::truncate();

            // Rebuild permissions untuk semua menu dan menu items
            $menus = DynamicMenu::all();
            $menuItems = DynamicMenuItem::all();
            
            foreach ($menus as $menu) {
                $this->syncMenuPermissions($menu);
            }
            
            foreach ($menuItems as $menuItem) {
                $this->syncMenuItemPermissions($menuItem);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua permissions berhasil direbuild',
                'total_permissions' => Permission::count()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to rebuild permissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal rebuild permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk mendapatkan statistik permissions
     * Berguna untuk monitoring system
     */
    public function getPermissionStats()
    {
        try {
            $stats = [
                'total_menus' => DynamicMenu::count(),
                'active_menus' => DynamicMenu::where('is_active', 1)->count(),
                'total_menu_items' => DynamicMenuItem::count(),
                'active_menu_items' => DynamicMenuItem::where('is_active', 1)->count(),
                'total_actions' => Action::count(),
                'total_permissions' => Permission::count(),
                'menu_permissions' => Permission::whereNull('menu_item_id')->count(),
                'menu_item_permissions' => Permission::whereNotNull('menu_item_id')->count(),
            ];

            // Expected permissions count
            $expectedMenuPermissions = DynamicMenu::count() * Action::count();
            $expectedMenuItemPermissions = DynamicMenuItem::count() * Action::count();
            $expectedTotal = $expectedMenuPermissions + $expectedMenuItemPermissions;

            $stats['expected_permissions'] = $expectedTotal;
            $stats['sync_status'] = $stats['total_permissions'] === $expectedTotal ? 'synced' : 'out_of_sync';

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get permission stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil statistik permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}