<?php
namespace App\Http\Controllers;

use App\Models\DynamicMenu;
use App\Models\DynamicTable;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getUserPermissions()
    {
        $userRoleId = auth()->user()->role_id;
        $isSuperAdmin = $userRoleId == 1;
        
        $permissions = [];
        if (!$isSuperAdmin) {
            $userRole = Roles::find($userRoleId);
            if ($userRole && $userRole->akses) {
                $permissions = is_string($userRole->akses) 
                    ? json_decode($userRole->akses, true) 
                    : $userRole->akses;
                $permissions = $permissions ?: [];
            }
        }
        
        return [$permissions, $isSuperAdmin];
    }

    private function hasPermission($moduleKey, $action)
    {
        [$permissions, $isSuperAdmin] = $this->getUserPermissions();
        
        if ($isSuperAdmin) return true;
        
        return isset($permissions[$moduleKey]) && 
            is_array($permissions[$moduleKey]) && 
            in_array($action, $permissions[$moduleKey]);
    }

    public function index()
    {
        
        [$permissions, $isSuperAdmin] = $this->getUserPermissions();
        
        // Fetch active dynamic menus with permission filtering
        $dynamicMenus = DynamicMenu::active()
            ->ordered()
            ->with('activeItems')
            ->get()
            ->filter(function($menu) use ($permissions, $isSuperAdmin) {
                return $menu->hasUserPermission($permissions, $isSuperAdmin);
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

        if (!$this->hasPermission('dynamic_table', 'read')) {
            abort(403, 'Anda tidak memiliki permission untuk mengakses tabel ini');
        }

        $dynamicTable = DynamicTable::with('activeColumns')->findOrFail($tableId);
        // dd( $dynamicTable);
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

    // create method updateTableData di Controller
    public function storeTableData(Request $request, $tableId)
    {
        if (!$this->hasPermission('dynamic_table', 'create')) {
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



    // Update method updateTableData di Controller
    // public function updateTableData(Request $request, $tableId, $id)
    // {
    //     if (!$this->hasPermission('dynamic_table', 'edit')) {
    //         return back()->with('error', 'Anda tidak memiliki permission untuk mengedit data');
    //     }

    //     $dynamicTable = DynamicTable::with('activeColumns')->findOrFail($tableId);
    //     try {
    //         $rules = $this->buildValidationRules($dynamicTable);
    //         $validated = $request->validate($rules);

    //         // Prepare data dengan handling null values
    //         $updateData = [];
    //         foreach ($validated as $key => $value) {
    //             // Jangan masukkan field yang kosong kecuali memang boleh null
    //             $column = $dynamicTable->activeColumns->where('column_name', $key)->first();
                
    //             if ($column) {
    //                 if (!$column->is_required && ($value === null || $value === '')) {
    //                     $updateData[$key] = null;
    //                 } else {
    //                     $updateData[$key] = $value;
    //                 }
    //             } else {
    //                 $updateData[$key] = $value;
    //             }
    //         }
            
    //         $updateData['updated_at'] = now();

    //         $affected = DB::table($dynamicTable->table_name)
    //             ->where('id', $id)
    //             ->update($updateData);

    //         if ($affected > 0) {
    //             return back()->with('success', 'Data berhasil diperbarui');
    //         } else {
    //             return back()->with('warning', 'Tidak ada perubahan data yang disimpan');
    //         }
            
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         \Log::error('Validation error: ' . json_encode($e->errors()));
    //         return back()->withErrors($e->errors())->withInput();
            
    //     } catch (\Exception $e) {
    //         \Log::error('Update table data error: ' . $e->getMessage());
    //         \Log::error('Stack trace: ' . $e->getTraceAsString());
            
    //         return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    //     }
    // }
    
    // / Method controller - sesuaikan parameter dengan route
    public function updateTableData(Request $request, $table, $id)
    {
        \Log::info("Update called - Table: {$table}, ID: {$id}");
        \Log::info("Request data: " . json_encode($request->all()));
        
        if (!$this->hasPermission('dynamic_table', 'update')) {
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
                // Allow empty string dan null untuk field non-required
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
    
    // delete
    public function destroyTableData($tableId, $id)
    {
        if (!$this->hasPermission('dynamic_table', 'delete')) {
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