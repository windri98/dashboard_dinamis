<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        
        // SuperAdmin memiliki akses penuh
        if ($user->role_id == 1) {
            return $next($request);
        }
        
        // Ambil role dan permissions
        $role = $user->roles;
        if (!$role || !$role->akses) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini!');
        }
        
        // Parse permissions
        $permissions = json_decode($role->akses, true) ?: [];
        
        // Tentukan aksi berdasarkan HTTP method
        $action = 'read'; // default untuk GET
        if ($request->isMethod('post')) {
            $action = 'create';
        } elseif ($request->isMethod('put') || $request->isMethod('patch')) {
            $action = 'edit';
        } elseif ($request->isMethod('delete')) {
            $action = 'delete';
        }
        
        // Izinkan akses jika memiliki permission dan aksi yang diperlukan
        if (isset($permissions[$permission]) && 
            is_array($permissions[$permission]) && 
            in_array($action, $permissions[$permission])) {
            return $next($request);
        }
        
        // Coba format permission lama (untuk backward compatibility)
        if (in_array($action, $permissions)) {
            return $next($request);
        }
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return back()->with('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini!');

        // return $next($request);
    }
}
