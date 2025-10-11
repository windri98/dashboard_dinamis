<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DynamicTable;
use App\Models\ApiEndpoint;
use App\Models\Permission;
use App\Models\Action;
use App\Services\FileUploadService;

class DynamicApiController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
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

            // Add file metadata to record
            $recordArray = (array) $record;
            $recordArray = $this->addFileMetadata($recordArray, $tableName);

            return response()->json([
                'success' => true,
                'data' => $recordArray
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

            $data = $request->except(['_token', '_method']);
            
            // Handle file uploads
            if (count($request->allFiles()) > 0) {
                $data = $this->fileUploadService->handleFileUploads($request, $tableName, $data);
            }

            // Add timestamps if columns exist
            if (Schema::hasColumn($tableName, 'created_at')) {
                $data['created_at'] = now();
            }
            if (Schema::hasColumn($tableName, 'updated_at')) {
                $data['updated_at'] = now();
            }

            $id = DB::table($tableName)->insertGetId($data);

            // Get created record with file info
            $record = DB::table($tableName)->where('id', $id)->first();
            $recordArray = (array) $record;
            
            // Add file metadata to response
            $recordArray = $this->addFileMetadata($recordArray, $tableName);

            return response()->json([
                'success' => true,
                'message' => 'Record created successfully',
                'data' => $recordArray
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
            $existingRecord = DB::table($tableName)->where('id', $id)->first();
            if (!$existingRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            $data = $request->except(['_token', '_method']);
            
            // Handle file uploads
            if (count($request->allFiles()) > 0) {
                // Get existing file paths for cleanup if needed
                $existingFiles = [];
                foreach ($request->allFiles() as $fieldName => $file) {
                    if (isset($existingRecord->$fieldName)) {
                        $existingFiles[$fieldName] = $existingRecord->$fieldName;
                    }
                }

                $data = $this->fileUploadService->handleFileUploads($request, $tableName, $data);
                
                // Cleanup old files
                foreach ($existingFiles as $fieldName => $oldFilePath) {
                    if ($oldFilePath && isset($data[$fieldName]) && $data[$fieldName] !== $oldFilePath) {
                        $this->fileUploadService->deleteFile($oldFilePath);
                    }
                }
            }

            // Add updated_at if column exists
            if (Schema::hasColumn($tableName, 'updated_at')) {
                $data['updated_at'] = now();
            }

            DB::table($tableName)->where('id', $id)->update($data);

            // Get updated record with file info
            $record = DB::table($tableName)->where('id', $id)->first();
            $recordArray = (array) $record;
            $recordArray = $this->addFileMetadata($recordArray, $tableName);

            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully',
                'data' => $recordArray
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

    /**
     * Add file metadata to record data
     */
    protected function addFileMetadata(array $record, string $tableName): array
    {
        $columns = Schema::getColumnListing($tableName);
        
        foreach ($columns as $column) {
            if (isset($record[$column]) && $this->looksLikeFilePath($record[$column])) {
                $fileInfo = $this->fileUploadService->getFileInfo($record[$column]);
                if ($fileInfo) {
                    $record[$column . '_meta'] = $fileInfo;
                }
            }
        }

        return $record;
    }

    /**
     * Check if a string looks like a file path
     */
    protected function looksLikeFilePath($value): bool
    {
        if (!is_string($value) || empty($value)) {
            return false;
        }

        // Check if it looks like a storage path
        return str_contains($value, 'uploads/') && 
            preg_match('/\.(jpg|jpeg|png|gif|webp|pdf|doc|docx|xls|xlsx|txt|zip|rar)$/i', $value);
    }

    /**
     * Get column type information for a table
     */
    public function getTableInfo(Request $request, $tableName)
    {
        try {
            $dynamicTable = DynamicTable::where('table_name', $tableName)
                ->with('columns')
                ->first();
            
            if (!$dynamicTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }

            $columns = $dynamicTable->columns->map(function($column) {
                return [
                    'name' => $column->column_name,
                    'type' => $column->type,
                    'required' => $column->is_required,
                    'options' => $column->options
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'table_name' => $tableName,
                    'display_name' => $dynamicTable->name,
                    'description' => $dynamicTable->description,
                    'columns' => $columns
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}