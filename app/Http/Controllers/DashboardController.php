<?php
namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\DynamicTable;
use App\Models\User;
use App\Models\Roles;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get user permissions as array of permission IDs (consistent with RolesController)
     */
        private function getUserPermissions()
    {
        $userRoleId = auth()->user()->role_id;
        $isSuperAdmin = $userRoleId == 1;
        
        $permissions = [];
        if (!$isSuperAdmin) {
            $userRole = Roles::find($userRoleId);
            if ($userRole && !empty($userRole->akses)) {
                // Karena ada casting 'akses' => 'array' di model, ini sudah pasti array
                $permissions = is_array($userRole->akses) ? $userRole->akses : [];
            }
        }
        
        return [$permissions, $isSuperAdmin];
    }

    /**
     * Check if user has permission berdasarkan menu dan action
     */
    private function hasPermission($menuKey, $actionKey)
    {
        [$permissionIds, $isSuperAdmin] = $this->getUserPermissions();
        
        if ($isSuperAdmin) return true;
        
        // Cari permission berdasarkan menu permission_key dan action nama
        $permission = Permission::whereHas('menu', function($q) use ($menuKey) {
                $q->where('permission_key', $menuKey);
            })
            ->whereHas('action', function($q) use ($actionKey) {
                $q->where('nama', $actionKey);
            })
            ->first();
            
        if (!$permission) {
            return false;
        }
        
        return in_array($permission->id, $permissionIds);
    }

    /**
     * Check menu permission untuk filtering di dashboard
     */
    private function hasMenuPermission($menuPermissionKey)
    {
        [$permissionIds, $isSuperAdmin] = $this->getUserPermissions();
        
        if ($isSuperAdmin) return true;
        
        // Cari permission untuk menu dengan permission_key
        $menuPermissions = Permission::whereHas('menu', function($q) use ($menuPermissionKey) {
            $q->where('permission_key', $menuPermissionKey);
        })->pluck('id')->toArray();
        
        // Check apakah user punya minimal 1 permission untuk menu ini
        return !empty(array_intersect($menuPermissions, $permissionIds));
    }

    public function index()
    {
        [$permissions, $isSuperAdmin] = $this->getUserPermissions();
        
        // Fetch active dynamic menus with permission filtering
        $dynamicMenus = DynamicMenu::active()
            ->ordered()
            ->with('activeItems')
            ->get()
            ->filter(function($menu) use ($isSuperAdmin) {
                if ($isSuperAdmin) return true;
                
                // Check menu permission
                return $this->hasMenuPermission($menu->permission_key);
            });

        // Get dashboard statistics
        $stats = [
            'total_menus' => $dynamicMenus->count(),
            'total_items' => $dynamicMenus->sum(function($menu) { 
                return $menu->activeItems->count(); 
            }),
            'total_tables' => DynamicTable::active()->count(),
            'total_users' => User::count(),
        ];

        return view('dashboard.index', [
            'dynamicMenus' => $dynamicMenus,
            'stats' => $stats
        ]);
    }

    // ==========================================TABEL======================================

    public function showTable(Request $request, $tableId)
    {
        // Check permission untuk read/view tabel
        if (!$this->hasPermission('master_data', 'View/Lihat')) {
            abort(403, 'Anda tidak memiliki permission untuk mengakses tabel ini');
        }

        $dynamicTable = DynamicTable::with('activeColumns')->findOrFail($tableId);
        
        // Check if the actual database table exists
        if (!$dynamicTable->tableExists()) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Tabel database tidak ditemukan');
        }

        // Pass permissions to view
        [$permissions, $isSuperAdmin] = $this->getUserPermissions();

        $query = DB::table($dynamicTable->table_name);      

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $searchableColumns = $dynamicTable->activeColumns
                ->where('is_searchable', true)
                ->pluck('column_name');

            if ($searchableColumns->count() > 0) {
                $query->where(function($q) use ($searchableColumns, $searchTerm) {
                    foreach ($searchableColumns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
                    }
                });
            }
        }

        // Date search functionality
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateColumns = $dynamicTable->activeColumns
                ->whereIn('type', ['date', 'datetime'])
                ->pluck('column_name');

            foreach ($dateColumns as $dateColumn) {
                if ($request->filled('date_from')) {
                    $query->where($dateColumn, '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->where($dateColumn, '<=', $request->date_to);
                }
            }
        }

        // Sorting functionality
        if ($request->filled('sort')) {
            $sortColumn = $request->sort;
            $direction = $request->get('direction', 'asc');
            
            $sortableColumns = $dynamicTable->activeColumns
                ->where('is_sortable', true)
                ->pluck('column_name');

            if ($sortableColumns->contains($sortColumn)) {
                $query->orderBy($sortColumn, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = $request->get('per_page', 15);
        $data = $query->paginate($perPage);

        return view('dashboard.table', [
            'dynamicTable' => $dynamicTable,
            'data' => $data,
            'permissions' => $permissions,
            'isSuperAdmin' => $isSuperAdmin
        ]);
    }

    public function storeTableData(Request $request, $tableId)
    {
        if (!$this->hasPermission('master_data', 'Create/Tambah')) {
            return back()->with('error', 'Anda tidak memiliki permission untuk menambah data');
        }

        $dynamicTable = DynamicTable::with('activeColumns')->findOrFail($tableId);
        
        try {
            // Build validation rules
            $rules = $this->buildValidationRules($dynamicTable);
            $validated = $request->validate($rules);

            // Prepare data for insertion
            $insertData = array_filter($validated, function($value) {
                return $value !== null && $value !== '';
            });
            
            $insertData['created_at'] = now();
            $insertData['updated_at'] = now();

            DB::table($dynamicTable->table_name)->insert($insertData);
            return back()->with('success', 'Data berhasil ditambahkan');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Store table data error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function updateTableData(Request $request, $table, $id)
    {
        \Log::info("Update called - Table: {$table}, ID: {$id}");
        \Log::info("Request data: " . json_encode($request->all()));
        
        if (!$this->hasPermission('master_data', 'Edit/Update')) {
            return back()->with('error', 'Anda tidak memiliki permission untuk mengedit data');
        }

        $dynamicTable = DynamicTable::with('activeColumns')->findOrFail($table);
        
        try {
            // Check if record exists
            $existingRecord = DB::table($dynamicTable->table_name)
                ->where('id', $id)
                ->first();
                
            if (!$existingRecord) {
                return back()->with('error', 'Data tidak ditemukan');
            }

            // Build validation rules
            $rules = $this->buildValidationRules($dynamicTable);
            $validated = $request->validate($rules);

            // Prepare data for update - improved version
            $updateData = [];
            foreach ($validated as $key => $value) {
                $column = $dynamicTable->activeColumns->where('column_name', $key)->first();
                
                if ($column && !$column->is_required && ($value === null || $value === '')) {
                    $updateData[$key] = null;
                } else if ($value !== null && $value !== '') {
                    $updateData[$key] = $value;
                }
            }
            
            $updateData['updated_at'] = now();

            $affected = DB::table($dynamicTable->table_name)
                ->where('id', $id)
                ->update($updateData);
                
            if ($affected > 0) {
                return back()->with('success', 'Data berhasil diperbarui');
            } else {
                return back()->with('warning', 'Tidak ada perubahan yang disimpan');
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Update table data error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
    
    public function destroyTableData($tableId, $id)
    {
        if (!$this->hasPermission('master_data', 'Delete/Hapus')) {
            return back()->with('error', 'Anda tidak memiliki permission untuk menghapus data');
        }

        try {
            $dynamicTable = DynamicTable::findOrFail($tableId);
            
            $affected = DB::table($dynamicTable->table_name)
                ->where('id', $id)
                ->delete();

            if ($affected > 0) {
                return back()->with('success', 'Data berhasil dihapus');
            } else {
                return back()->with('error', 'Data tidak ditemukan');
            }
            
        } catch (\Exception $e) {
            \Log::error('Delete table data error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function buildValidationRules($dynamicTable)
    {
        $rules = [];
        
        foreach ($dynamicTable->activeColumns as $column) {
            // Skip auto-increment and system columns
            if (in_array($column->column_name, [
                'id', 
                'created_at', 
                'updated_at'
                ])) {
                continue;
            }
            
            $columnRules = [];
            
            if ($column->is_required) {
                $columnRules[] = 'required';
            } else {
                $columnRules[] = 'nullable';
            }
            
            switch ($column->type) {
                case 'string':
                    $columnRules[] = 'string|max:255';
                    break;
                case 'text':
                    $columnRules[] = 'string';
                    break;
                case 'integer':
                    $columnRules[] = 'integer';
                    break;
                case 'decimal':
                    $columnRules[] = 'numeric';
                    break;
                case 'date':
                    $columnRules[] = 'date';
                    break;
                case 'datetime':
                    $columnRules[] = 'date';
                    break;
                case 'boolean':
                    $columnRules[] = 'boolean';
                    break;
                case 'enum':
                    if (isset($column->options['values']) && is_array($column->options['values'])) {
                        $columnRules[] = 'in:' . implode(',', $column->options['values']);
                    }
                    break;
            }
            
            $rules[$column->column_name] = implode('|', $columnRules);
            \Log::info("Building rule for: " . $column->column_name . " - Type: " . $column->type);
        }
        
        \Log::info("Final validation rules:", $rules);
        return $rules;
    }
}