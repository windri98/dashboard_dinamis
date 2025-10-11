<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicTable;
use App\Models\ApiEndpoint;
use App\Models\Permission;
use App\Models\Action;

class DynamicApiController extends Controller
{
    /**
     * Dynamic API - GET all records from table
     */
    public function index(Request $request, $tableName)
    {
        try {
            // Validate table exists in dynamic_tables
            $dynamicTable = DynamicTable::where('table_name', $tableName)->first();
            
            if (!$dynamicTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found in dynamic tables configuration'
                ], 404);
            }

            // Check if table exists in database
            if (!Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table does not exist in database'
                ], 404);
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            $search = $request->get('search');
            $orderBy = $request->get('order_by', 'id');
            $orderDir = $request->get('order_dir', 'desc');

            // Build query
            $query = DB::table($tableName);

            // Apply search if provided
            if ($search) {
                $columns = Schema::getColumnListing($tableName);
                $query->where(function($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                });
            }

            // Apply ordering
            if (Schema::hasColumn($tableName, $orderBy)) {
                $query->orderBy($orderBy, $orderDir);
            }

            // Get total count for pagination
            $total = $query->count();

            // Apply pagination
            $data = $query->skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => ($page - 1) * $perPage + 1,
                    'to' => min($page * $perPage, $total)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dynamic API - GET single record by ID
     */
    public function show(Request $request, $tableName, $id)
    {
        try {
            // Validate table exists
            $dynamicTable = DynamicTable::where('table_name', $tableName)->first();
            
            if (!$dynamicTable || !Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }

            $record = DB::table($tableName)->where('id', $id)->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dynamic API - POST create new record
     */
    public function store(Request $request, $tableName)
    {
        try {
            // Validate table exists
            $dynamicTable = DynamicTable::where('table_name', $tableName)->first();
            
            if (!$dynamicTable || !Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }

            $data = $request->all();
            
            // Remove non-table fields
            unset($data['_token'], $data['_method']);

            // Add timestamps if columns exist
            if (Schema::hasColumn($tableName, 'created_at')) {
                $data['created_at'] = now();
            }
            if (Schema::hasColumn($tableName, 'updated_at')) {
                $data['updated_at'] = now();
            }

            $id = DB::table($tableName)->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => 'Record created successfully',
                'data' => ['id' => $id]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dynamic API - PUT update record by ID
     */
    public function update(Request $request, $tableName, $id)
    {
        try {
            // Validate table exists
            $dynamicTable = DynamicTable::where('table_name', $tableName)->first();
            
            if (!$dynamicTable || !Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }

            // Check if record exists
            $exists = DB::table($tableName)->where('id', $id)->exists();
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            $data = $request->all();
            
            // Remove non-table fields
            unset($data['_token'], $data['_method']);

            // Add updated_at if column exists
            if (Schema::hasColumn($tableName, 'updated_at')) {
                $data['updated_at'] = now();
            }

            DB::table($tableName)->where('id', $id)->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dynamic API - DELETE record by ID
     */
    public function destroy(Request $request, $tableName, $id)
    {
        try {
            // Validate table exists
            $dynamicTable = DynamicTable::where('table_name', $tableName)->first();
            
            if (!$dynamicTable || !Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }

            // Check if record exists
            $exists = DB::table($tableName)->where('id', $id)->exists();
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            DB::table($tableName)->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}