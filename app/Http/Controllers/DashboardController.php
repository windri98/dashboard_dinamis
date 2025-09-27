<?php
namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\DynamicTable;
use App\Models\User;
use App\Models\Roles;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get user permissions as array of permission IDs (consistent with RolesController)
     * UPDATED: Better error handling and logging
     */
    /**
 * Get user permissions as array of permission IDs (FIXED JSON PARSING)
 */
private function getUserPermissions()
{
    $userRoleId = auth()->user()->role_id;
    $isSuperAdmin = $userRoleId == 1;

    $permissions = [];

    if ($isSuperAdmin) {
        Log::info("SuperAdmin detected, full access granted", ['role_id' => $userRoleId]);
    } else {
        $userRole = Roles::find($userRoleId);

        if (!$userRole) {
            Log::warning("Role not found for user", ['user_id' => auth()->id(), 'role_id' => $userRoleId]);
        } elseif (empty($userRole->akses)) {
            Log::info("No permissions set for this role", ['role_id' => $userRoleId]);
        } else {
            // FIXED: Handle "Full access"
            if ($userRole->akses === 'Full access' || $userRole->akses === 'full access') {
                $permissions = Permission::pluck('id')->toArray();
                Log::info("Full access granted", ['permissions_count' => count($permissions)]);
            } else {
                // FIXED: Enhanced JSON parsing
                $rawValue = $userRole->akses;
                
                // Check if it's already an array (Laravel cast)
                if (is_array($rawValue)) {
                    $permissions = array_map('intval', $rawValue);
                    Log::info("User permissions loaded as array", ['permissions' => $permissions]);
                } else {
                    // Handle string JSON with multiple levels of escaping
                    $jsonString = $rawValue;
                    
                    // Remove outer quotes if present
                    if (is_string($jsonString) && str_starts_with($jsonString, '"') && str_ends_with($jsonString, '"')) {
                        $jsonString = substr($jsonString, 1, -1);
                        // Unescape quotes
                        $jsonString = str_replace('\\"', '"', $jsonString);
                    }
                    
                    $decoded = json_decode($jsonString, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $permissions = array_map('intval', $decoded);
                        Log::info("User permissions loaded from JSON string", [
                            'raw' => $rawValue,
                            'processed' => $jsonString,
                            'decoded' => $permissions
                        ]);
                    } else {
                        // Fallback: try to get raw from database
                        $rawFromDb = $userRole->getRawOriginal('akses');
                        $decodedFromRaw = json_decode($rawFromDb, true);
                        
                        if (is_array($decodedFromRaw)) {
                            $permissions = array_map('intval', $decodedFromRaw);
                            Log::info("User permissions loaded from raw database", [
                                'raw_db' => $rawFromDb,
                                'decoded' => $permissions
                            ]);
                        } else {
                            $permissions = [];
                            Log::warning("Failed to parse permissions", [
                                'raw_value' => $rawValue,
                                'raw_db' => $rawFromDb,
                                'json_error' => json_last_error_msg()
                            ]);
                        }
                    }
                }
            }
        }
    }

    return [$permissions, $isSuperAdmin];
}

    /**
     * Check if user has permission berdasarkan menu dan action
     * UPDATED: Added logging and better error handling
     */
    private function hasPermission($menuKey, $actionKey)
    {
        [$permissionIds, $isSuperAdmin] = $this->getUserPermissions();
        
        if ($isSuperAdmin) {
            Log::info("SuperAdmin access granted", ['menu' => $menuKey, 'action' => $actionKey]);
            return true;
        }
        
        // UPDATED: Added query logging
        $permission = Permission::whereHas('menu', function($q) use ($menuKey) {
                $q->where('permission_key', $menuKey);
            })
            ->whereHas('action', function($q) use ($actionKey) {
                $q->where('nama', $actionKey);
            })
            ->first();
            
        if (!$permission) {
            Log::warning("Permission not found in database", [
                'menu_key' => $menuKey,
                'action_key' => $actionKey
            ]);
            return false;
        }
        
        $hasAccess = in_array($permission->id, $permissionIds);
        Log::info("Permission check result", [
            'menu' => $menuKey,
            'action' => $actionKey,
            'permission_id' => $permission->id,
            'user_permissions' => $permissionIds,
            'has_access' => $hasAccess
        ]);
        
        return $hasAccess;
    }

    /**
     * Check menu permission untuk filtering di dashboard
     * UPDATED: Consistent with blade logic and better logging
     */
    private function hasMenuPermission($menuPermissionKey)
    {
        [$permissionIds, $isSuperAdmin] = $this->getUserPermissions();
        
        if ($isSuperAdmin) {
            Log::info("SuperAdmin menu access granted", ['menu' => $menuPermissionKey]);
            return true;
        }
        
        // UPDATED: Enhanced query with better logging
        $menuPermissions = Permission::whereHas('menu', function($q) use ($menuPermissionKey) {
            $q->where('permission_key', $menuPermissionKey);
        })->pluck('id')->toArray();
        
        if (empty($menuPermissions)) {
            Log::warning("No permissions found for menu", ['menu_key' => $menuPermissionKey]);
            return false;
        }
        
        // Check apakah user punya minimal 1 permission untuk menu ini
        $hasAccess = !empty(array_intersect($menuPermissions, $permissionIds));
        
        Log::info("Menu permission check result", [
            'menu' => $menuPermissionKey,
            'menu_permissions' => $menuPermissions,
            'user_permissions' => $permissionIds,
            'intersection' => array_intersect($menuPermissions, $permissionIds),
            'has_access' => $hasAccess
        ]);
        
        return $hasAccess;
    }

    /**
     * UPDATED: Enhanced index with better error handling
     */
    public function index()
    {
        try {
            [$permissions, $isSuperAdmin] = $this->getUserPermissions();
            
            // UPDATED: Enhanced logging for debugging
            Log::info("Dashboard access", [
                'user_id' => auth()->id(),
                'role_id' => auth()->user()->role_id,
                'is_super_admin' => $isSuperAdmin,
                'permissions_count' => count($permissions)
            ]);
            
            // Fetch active dynamic menus with permission filtering
            $dynamicMenus = DynamicMenu::active()
                ->ordered()
                ->with('activeItems')
                ->get()
                ->filter(function($menu) use ($isSuperAdmin) {
                    if ($isSuperAdmin) return true;
                    
                    // UPDATED: Enhanced permission check with logging
                    $hasAccess = $this->hasMenuPermission($menu->permission_key);
                    Log::info("Menu filter result", [
                        'menu_name' => $menu->name,
                        'permission_key' => $menu->permission_key,
                        'has_access' => $hasAccess
                    ]);
                    
                    return $hasAccess;
                });

            Log::info("Filtered menus count", ['total' => $dynamicMenus->count()]);

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
            
        } catch (\Exception $e) {
            Log::error("Dashboard index error", ['error' => $e->getMessage()]);
            return view('dashboard.index', [
                'dynamicMenus' => collect(),
                'stats' => [
                    'total_menus' => 0,
                    'total_items' => 0,
                    'total_tables' => 0,
                    'total_users' => 0,
                ]
            ])->with('error', 'Terjadi kesalahan saat memuat dashboard');
        }
    }

    /**
     * NEW METHOD: Debug permissions - untuk troubleshooting
     */
    public function debugPermissions()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = auth()->user();
        [$permissions, $isSuperAdmin] = $this->getUserPermissions();

        $debugInfo = [
            'user_info' => [
                'id' => $user->id,
                'name' => $user->name,
                'role_id' => $user->role_id,
                'is_super_admin' => $isSuperAdmin
            ],
            'role_info' => [],
            'permissions_raw' => [],
            'permissions_decoded' => $permissions,
            'available_menus' => [],
            'menu_permissions' => []
        ];

        // Get role info
        if ($user->role_id) {
            $role = Roles::find($user->role_id);
            if ($role) {
                $debugInfo['role_info'] = [
                    'id' => $role->id,
                    'nama' => $role->nama,
                    'akses_raw' => $role->akses,
                    'akses_type' => gettype($role->akses)
                ];
            }
        }

        // Get all available permissions for comparison
        $allPermissions = Permission::with(['menu', 'action'])->get();
        foreach ($allPermissions as $perm) {
            $debugInfo['permissions_raw'][] = [
                'id' => $perm->id,
                'menu_name' => $perm->menu->name ?? 'N/A',
                'menu_permission_key' => $perm->menu->permission_key ?? 'N/A',
                'action_name' => $perm->action->nama ?? 'N/A',
                'user_has_access' => in_array($perm->id, $permissions)
            ];
        }

        // Get dynamic menus
        $dynamicMenus = DynamicMenu::active()->get();
        foreach ($dynamicMenus as $menu) {
            $hasAccess = $isSuperAdmin || $this->hasMenuPermission($menu->permission_key);
            $debugInfo['available_menus'][] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'permission_key' => $menu->permission_key,
                'user_has_access' => $hasAccess
            ];

            // Get permissions for this menu
            $menuPermissions = Permission::whereHas('menu', function($q) use ($menu) {
                $q->where('permission_key', $menu->permission_key);
            })->with('action')->get();

            $debugInfo['menu_permissions'][$menu->permission_key] = $menuPermissions->map(function($perm) use ($permissions) {
                return [
                    'id' => $perm->id,
                    'action_name' => $perm->action->nama ?? 'N/A',
                    'user_has_this' => in_array($perm->id, $permissions)
                ];
            });
        }

        return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
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
            Log::error('Store table data error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function updateTableData(Request $request, $table, $id)
    {
        Log::info("Update called - Table: {$table}, ID: {$id}");
        Log::info("Request data: " . json_encode($request->all()));
        
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
            Log::error('Validation error: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Update table data error: ' . $e->getMessage());
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
            Log::error('Delete table data error: ' . $e->getMessage());
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
            Log::info("Building rule for: " . $column->column_name . " - Type: " . $column->type);
        }
        
        Log::info("Final validation rules:", $rules);
        return $rules;
    }
}