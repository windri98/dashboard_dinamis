@extends('layouts.app')

@section('content')

<!-- Modern Alert Container -->
<div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
    @if(session('success'))
        <div class="modern-alert alert-success" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Berhasil!</strong>
                    <p>{{ session('success') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('error'))
        <div class="modern-alert alert-error" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Gagal!</strong>
                    <p>{{ session('error') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('warning'))
        <div class="modern-alert alert-warning" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-text">
                    <strong>Peringatan!</strong>
                    <p>{{ session('warning') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('info'))
        <div class="modern-alert alert-info" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Info!</strong>
                    <p>{{ session('info') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif
</div>
<section class="roles-section" id="role">
    <div class="roles-header">
        <h1>Roles</h1>
        <a href="{{ route('settings.roles.create') }}" class="add-button">Add New Role</a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Akses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $index => $role)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $role->role }}</td>
                            <td>
                                <!-- Button untuk toggle permissions -->
                                <button type="button" class="btn btn-sm" onclick="togglePermissionsSlide(this)">
                                    <span class="toggle-text">Lihat Akses</span>
                                    <svg class="toggle-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                    </svg>
                                </button>
                                
                                <!-- Container permissions yang bisa di-toggle -->
                                <div class="permissions-container">
                                    @php
                                        // Decode permission IDs dari akses
                                        $permissionIds = [];
                                        if ($role->akses === true || $role->akses === 'true' || $role->akses === '1') {
                                            $permissionIds = 'full_access';
                                        } elseif (is_string($role->akses)) {
                                            $decoded = json_decode($role->akses, true);
                                            $permissionIds = is_array($decoded) ? $decoded : [];
                                        } elseif (is_array($role->akses)) {
                                            $permissionIds = $role->akses;
                                        }
                                        
                                        // Get readable permissions
                                        $readablePermissions = [];
                                        if ($permissionIds === 'full_access') {
                                            $readablePermissions = 'full_access';
                                        } elseif (!empty($permissionIds)) {
                                            $permissions = App\Models\Permission::with(['menu', 'menuItem', 'action'])
                                                            ->whereIn('id', $permissionIds)
                                                            ->get();
                                            $grouped = [];
                                            foreach ($permissions as $permission) {
                                                $menuName = $permission->menu->name ?? 'Unknown Menu';
                                                $menuItemName = $permission->menuItem->name ?? null;
                                                $actionName = $permission->action->slug ?? 'Unknown Action';
                                                if ($menuItemName) {
                                                    $key = $menuName . ' > ' . $menuItemName;
                                                } else {
                                                    $key = $menuName;
                                                }
                                                
                                                if (!isset($grouped[$key])) {
                                                    $grouped[$key] = [];
                                                }
                                                $grouped[$key][] = $actionName;
                                            }
                                            $readablePermissions = $grouped;
                                        }
                                    @endphp
                                    
                                    <div class="permissions-content"><div class="permissions-content">
                                        @if($readablePermissions === 'full_access')
                                            <span class="badge bg-primary">Full Access</span>
                                        @elseif(empty($readablePermissions))
                                            <span class="badge bg-secondary">No Access</span>
                                        @else
                                            @foreach($readablePermissions as $menuPath => $actions)
                                                <div class="permission-item" style="margin-bottom: 8px;">
                                                    <strong style="color: #333;">{{ $menuPath }}:</strong>
                                                    <div style="margin-top: 4px;">
                                                        @foreach($actions as $action)
                                                            @php
                                                                $badgeClass = match(strtolower($action)) {
                                                                    'create' => 'bg-success',
                                                                    'read', 'view' => 'bg-info',
                                                                    'update', 'edit' => 'bg-warning',
                                                                    'delete' => 'btn-danger',
                                                                    default => 'bg-secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }}" style="margin-right: 4px; margin-bottom: 2px;">{{ $action }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="action-cell">
                                <div class="action-buttons">
                                    <form action="{{ route('settings.roles.edit', $role->id) }}">
                                        <button type="submit" class="btn btn-sm btn-primary" style="border: none">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            {{-- Edit --}}
                                        </button>
                                    </form>
                                    
                                    {{-- <a href="{{ route('edit.role', $role->id) }}" class="edit-button">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                        Edit
                                    </a> --}}
                                    <form action="{{ route('settings.roles.destroy', $role->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" style="border: none;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            {{-- Delete --}}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data role</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@endsection