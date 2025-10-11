<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;
use App\Models\DynamicMenu;
use App\Models\Action;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // SuperAdmin (role_id = 1) memiliki akses penuh ke semua fitur
        if ($user->role_id == 1) {
            Log::info("SuperAdmin access granted", [
                'user_id' => $user->id,
                'permission' => $permission,
                'method' => $request->method()
            ]);
            return $next($request);
        }
        
        // Ambil role user
        $role = $user->role;
        
        // Validasi role dan permissions ada
        if (!$role || !$role->akses) {
            return $this->unauthorizedResponse(
                $request, 
                'Anda tidak memiliki izin untuk mengakses halaman ini!'
            );
        }
        
        // Parse permissions dari role (sistem ID-based)
        $userPermissionIds = $this->parseUserPermissions($role->akses);
        
        // Validasi permissions tidak kosong
        if (empty($userPermissionIds)) {
            return $this->unauthorizedResponse(
                $request, 
                'Role Anda tidak memiliki permission yang terdaftar!'
            );
        }
        
        // Tentukan aksi berdasarkan HTTP method
        $action = $this->getActionFromMethod($request->method());
        
        // Cari permission di database berdasarkan permission key dan action
        $requiredPermission = $this->findPermission($permission, $action);
        
        if (!$requiredPermission) {
            Log::warning("Permission not found", [
                'permission_key' => $permission,
                'action' => $action,
                'method' => $request->method()
            ]);
            
            return $this->unauthorizedResponse(
                $request, 
                'Permission tidak ditemukan dalam sistem!'
            );
        }
        
        // Cek apakah user memiliki permission ini
        if (in_array($requiredPermission->id, $userPermissionIds)) {
            Log::info("Permission granted", [
                'user_id' => $user->id,
                'permission_key' => $permission,
                'action' => $action,
                'permission_id' => $requiredPermission->id
            ]);
            return $next($request);
        }
        
        // Akses ditolak
        Log::warning("Permission denied", [
            'user_id' => $user->id,
            'permission_key' => $permission,
            'action' => $action,
            'required_permission_id' => $requiredPermission->id,
            'user_permissions' => $userPermissionIds
        ]);
        
        return $this->unauthorizedResponse(
            $request, 
            'Anda tidak memiliki izin untuk melakukan tindakan ini!'
        );
    }
    
    /**
     * Parse user permissions dari role akses
     *
     * @param mixed $akses
     * @return array
     */
    private function parseUserPermissions($akses): array
    {
        // Handle "Full access"
        if ($akses === 'Full access' || $akses === 'full access') {
            return Permission::pluck('id')->toArray();
        }
        
        // Handle array (dari Laravel casting)
        if (is_array($akses)) {
            return array_map('intval', $akses);
        }
        
        // Handle JSON string
        if (is_string($akses)) {
            $decoded = json_decode($akses, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_map('intval', $decoded);
            }
        }
        
        return [];
    }
    
    /**
     * Cari permission berdasarkan permission key dan action
     *
     * @param string $permissionKey
     * @param string $actionSlug
     * @return Permission|null
     */
    private function findPermission(string $permissionKey, string $actionSlug): ?Permission
    {
        return Permission::whereHas('menu', function($q) use ($permissionKey) {
                $q->where('permission_key', $permissionKey);
            })
            ->whereHas('action', function($q) use ($actionSlug) {
                $q->where('slug', $actionSlug);
            })
            ->first();
    }
    
    /**
     * Tentukan aksi berdasarkan HTTP method
     *
     * @param string $method
     * @return string
     */
    private function getActionFromMethod(string $method): string
    {
        return match(strtoupper($method)) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'edit', 
            'DELETE' => 'delete',
            default => 'read',
        };
    }
    
    /**
     * Response untuk unauthorized access
     *
     * @param Request $request
     * @param string $message
     * @return Response
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }
        
        return redirect()->route('dashboard.index')->with('error', $message);
    }
}