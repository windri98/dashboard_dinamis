<?php
namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\DynamicMenuItem;
use App\Models\DynamicTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DynamicMenuController extends Controller
{
    // Menu Management
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
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'category' => 'required|in:main,settings',
            'order' => 'required|integer|min:0',
        ]);

        DynamicMenu::create($validated);
        
        return redirect()->route('settings.dynamic-menus.index')
            ->with('success', 'Menu berhasil ditambahkan');
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
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'category' => 'required|in:main,settings',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $dynamicMenu->update($validated);
        
        return redirect()->route('settings.dynamic-menus.index')
            ->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(DynamicMenu $dynamicMenu)
    {
        DB::beginTransaction();
        
        try {
            $dynamicMenu->delete();
            DB::commit();
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('success', 'Menu berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('settings.dynamic-menus.index')
                ->with('error', 'Gagal menghapus menu: ' . $e->getMessage());
        }

        
    }
    
// ==========================================================================================Menu Items==================================================================================================================


    // Menu Items Management
    public function menuItems(DynamicMenu $dynamicMenu)
    {
        
        $dynamicMenu->load('items');

        $tables = DynamicTable::active()->get();
        
        return view('settings.dynamic-menus.items', [
            'dynamicMenu' => $dynamicMenu,
            'tables' => $tables
        ]);
    }

        public function submenus($parentId)
    {
        // Ambil data submenu dari database sesuai parentId
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

        // Validate link_value based on link_type
        // if ($validated['link_type'] === 'table') {
        //     $request->validate([
        //         'link_value' => 'exists:dynamic_tables,id'
        //     ]);
        // }
        DynamicMenuItem::create($validated);
        return back()->with('success', 'Item menu berhasil ditambahkan');
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

        // Validate link_value based on link_type
        // if ($validated['link_type'] === 'table') {
        //     $request->validate([
        //         'link_value' => 'exists:dynamic_tables,id'
        //     ]);
        // }

        $item->update($validated);
        return back()->with('success', 'Item menu berhasil diperbarui');
    }

    public function destroyItem(DynamicMenuItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item menu berhasil dihapus');
    }
}