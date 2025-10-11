<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiEndpoint;
use App\Models\DynamicTable;
use App\Models\Permission;
use App\Models\Action;
use App\Models\DynamicMenu;
use Illuminate\Support\Facades\DB;

class ApiManagementController extends Controller
{
    /**
     * Display API endpoints management page
     */
    public function index()
    {
        $apiEndpoints = ApiEndpoint::with(['permission.menu', 'permission.action'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();

        return view('settings.api.index', compact('apiEndpoints'));
    }

    /**
     * Show form to create new API endpoint
     */
    public function create()
    {
        $dynamicTables = DynamicTable::where('is_active', true)->get();
        $permissions = Permission::with(['menu', 'action'])->get();
        $actions = Action::all();

        return view('settings.api.create', compact('dynamicTables', 'permissions', 'actions'));
    }

    /**
     * Store new API endpoint
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:api_endpoints',
            'endpoint' => 'required|string|max:255|unique:api_endpoints',
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'table_name' => 'required|string|exists:dynamic_tables,table_name',
            'permission_id' => 'nullable|exists:permissions,id',
            'is_active' => 'boolean',
            'use_ip_restriction' => 'boolean',
            'ip_whitelist' => 'nullable|string',
            'ip_blacklist' => 'nullable|string',
            'use_rate_limit' => 'boolean',
            'rate_limit_max' => 'nullable|integer|min:1',
            'rate_limit_period' => 'nullable|integer|min:1',
            'description' => 'nullable|string'
        ]);

        // Process IP lists
        if ($validated['ip_whitelist']) {
            $validated['ip_whitelist'] = array_map('trim', explode(',', $validated['ip_whitelist']));
        }
        if ($validated['ip_blacklist']) {
            $validated['ip_blacklist'] = array_map('trim', explode(',', $validated['ip_blacklist']));
        }

        ApiEndpoint::create($validated);

        return redirect()->route('settings.api.index')
                        ->with('success', 'API endpoint berhasil dibuat!');
    }

    /**
     * Show form to edit API endpoint
     */
    public function edit(ApiEndpoint $apiEndpoint)
    {
        $dynamicTables = DynamicTable::where('is_active', true)->get();
        $permissions = Permission::with(['menu', 'action'])->get();
        $actions = Action::all();

        return view('settings.api.edit', compact('apiEndpoint', 'dynamicTables', 'permissions', 'actions'));
    }

    /**
     * Update API endpoint
     */
    public function update(Request $request, ApiEndpoint $apiEndpoint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:api_endpoints,slug,' . $apiEndpoint->id,
            'endpoint' => 'required|string|max:255|unique:api_endpoints,endpoint,' . $apiEndpoint->id,
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'table_name' => 'required|string|exists:dynamic_tables,table_name',
            'permission_id' => 'nullable|exists:permissions,id',
            'is_active' => 'boolean',
            'use_ip_restriction' => 'boolean',
            'ip_whitelist' => 'nullable|string',
            'ip_blacklist' => 'nullable|string',
            'use_rate_limit' => 'boolean',
            'rate_limit_max' => 'nullable|integer|min:1',
            'rate_limit_period' => 'nullable|integer|min:1',
            'description' => 'nullable|string'
        ]);

        // Process IP lists
        if ($validated['ip_whitelist']) {
            $validated['ip_whitelist'] = array_map('trim', explode(',', $validated['ip_whitelist']));
        } else {
            $validated['ip_whitelist'] = null;
        }
        
        if ($validated['ip_blacklist']) {
            $validated['ip_blacklist'] = array_map('trim', explode(',', $validated['ip_blacklist']));
        } else {
            $validated['ip_blacklist'] = null;
        }

        $apiEndpoint->update($validated);

        return redirect()->route('settings.api.index')
                        ->with('success', 'API endpoint berhasil diupdate!');
    }

    /**
     * Delete API endpoint
     */
    public function destroy(ApiEndpoint $apiEndpoint)
    {
        $apiEndpoint->delete();

        return redirect()->route('settings.api.index')
                        ->with('success', 'API endpoint berhasil dihapus!');
    }

    /**
     * Show API endpoint details and logs
     */
    public function show(ApiEndpoint $apiEndpoint)
    {
        $apiEndpoint->load(['permission.menu', 'permission.action', 'accessLogs' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(100);
        }]);

        return view('settings.api.show', compact('apiEndpoint'));
    }

    /**
     * Toggle API endpoint status
     */
    public function toggleStatus(ApiEndpoint $apiEndpoint)
    {
        $apiEndpoint->update(['is_active' => !$apiEndpoint->is_active]);

        $status = $apiEndpoint->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
                        ->with('success', "API endpoint berhasil {$status}!");
    }

    /**
     * Generate API endpoints for a dynamic table
     */
    public function generateForTable(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:dynamic_tables,id',
            'permission_id' => 'nullable|exists:permissions,id',
            'use_ip_restriction' => 'boolean',
            'ip_whitelist' => 'nullable|string',
            'use_rate_limit' => 'boolean',
            'rate_limit_max' => 'nullable|integer|min:1',
            'rate_limit_period' => 'nullable|integer|min:1'
        ]);

        $dynamicTable = DynamicTable::find($validated['table_id']);
        $tableName = $dynamicTable->table_name;

        // Define CRUD endpoints
        $endpoints = [
            [
                'name' => "Get All {$dynamicTable->name}",
                'endpoint' => "api/dynamic/{$tableName}",
                'method' => 'GET',
                'description' => "Retrieve all records from {$tableName} table"
            ],
            [
                'name' => "Get Single {$dynamicTable->name}",
                'endpoint' => "api/dynamic/{$tableName}/{{id}}",
                'method' => 'GET',
                'description' => "Retrieve single record from {$tableName} table"
            ],
            [
                'name' => "Create {$dynamicTable->name}",
                'endpoint' => "api/dynamic/{$tableName}",
                'method' => 'POST',
                'description' => "Create new record in {$tableName} table"
            ],
            [
                'name' => "Update {$dynamicTable->name}",
                'endpoint' => "api/dynamic/{$tableName}/{{id}}",
                'method' => 'PUT',
                'description' => "Update record in {$tableName} table"
            ],
            [
                'name' => "Delete {$dynamicTable->name}",
                'endpoint' => "api/dynamic/{$tableName}/{{id}}",
                'method' => 'DELETE',
                'description' => "Delete record from {$tableName} table"
            ]
        ];

        // Process IP whitelist
        $ipWhitelist = null;
        if ($validated['ip_whitelist']) {
            $ipWhitelist = array_map('trim', explode(',', $validated['ip_whitelist']));
        }

        $created = 0;
        foreach ($endpoints as $endpoint) {
            // Check if endpoint already exists
            $exists = ApiEndpoint::where('endpoint', $endpoint['endpoint'])
                                ->where('method', $endpoint['method'])
                                ->exists();

            if (!$exists) {
                ApiEndpoint::create([
                    'name' => $endpoint['name'],
                    'endpoint' => $endpoint['endpoint'],
                    'method' => $endpoint['method'],
                    'table_name' => $tableName,
                    'permission_id' => $validated['permission_id'],
                    'is_active' => true,
                    'use_ip_restriction' => $validated['use_ip_restriction'] ?? false,
                    'ip_whitelist' => $ipWhitelist,
                    'use_rate_limit' => $validated['use_rate_limit'] ?? false,
                    'rate_limit_max' => $validated['rate_limit_max'] ?? 60,
                    'rate_limit_period' => $validated['rate_limit_period'] ?? 60,
                    'description' => $endpoint['description']
                ]);
                $created++;
            }
        }

        return redirect()->route('settings.api.index')
                        ->with('success', "Berhasil membuat {$created} API endpoint untuk tabel {$tableName}!");
    }
}