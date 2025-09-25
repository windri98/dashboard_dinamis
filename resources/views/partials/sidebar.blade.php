@php
    // Redirect to forbidden page if not logged in
if (!auth()->check()) {
        header('Location: /login');
        exit;
    }

    $userRoleId = auth()->user()->role_id;
    $isSuperAdmin = $userRoleId == 1;
    $userRole = App\Models\Roles::find($userRoleId);

    // FIXED: Handle casting array
    $permissionIds = [];
    if (!$isSuperAdmin && $userRole && !empty($userRole->akses)) {
        $permissionIds = is_array($userRole->akses) ? $userRole->akses : [];
    }

    // FIXED: Function to check permission based on database
    function hasPermission($menuKey, $actionKey, $permissionIds, $isSuperAdmin) {
        if ($isSuperAdmin) return true;
        
        // Query permission berdasarkan menu permission_key dan action nama
        $permission = App\Models\Permission::whereHas('menu', function($q) use ($menuKey) {
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

    // FIXED: Function to check if user has any permission for a menu
    function hasMenuAccess($menuPermissionKey, $permissionIds, $isSuperAdmin) {
        if ($isSuperAdmin) return true;
        
        // Get all permission IDs for this menu
        $menuPermissions = App\Models\Permission::whereHas('menu', function($q) use ($menuPermissionKey) {
            $q->where('permission_key', $menuPermissionKey);
        })->pluck('id')->toArray();
        
        // Check if user has at least one permission for this menu
        return !empty(array_intersect($menuPermissions, $permissionIds));
    }

    // FIXED: Load dynamic menus with proper permission filtering
    $dynamicMainMenus = \App\Models\DynamicMenu::active()
        ->byCategory('main')
        ->ordered()
        ->with('activeItems')
        ->get()
        ->filter(function($menu) use ($permissionIds, $isSuperAdmin) {
            return hasMenuAccess($menu->permission_key, $permissionIds, $isSuperAdmin);
        });

    $dynamicSettingsMenus = \App\Models\DynamicMenu::active()
        ->byCategory('settings')
        ->ordered()
        ->with('activeItems')
        ->get()
        ->filter(function($menu) use ($permissionIds, $isSuperAdmin) {
            return hasMenuAccess($menu->permission_key, $permissionIds, $isSuperAdmin);
        });
@endphp

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/dashboard" class="logo">
            <i class="fas fa-chart-line"></i>
            <span class="logo-text">Dashboard</span>
        </a>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-title">Menu Utama</div>
        <div class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="/dashboard" style="text-decoration: none; color: inherit;">
                <i class="fas fa-home"></i>
                <span class="menu-text">Beranda</span>
            </a>
        </div>

        <!-- Dynamic Main Menus -->
        @foreach($dynamicMainMenus as $menu)
            <div class="menu-item dropdown">
                <div class="dropdown-header">
                    <i class="{{ $menu->icon }}"></i>
                    <span class="menu-text">{{ $menu->name }}</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>
                <div class="dropdown-content">
                    @forelse($menu->activeItems as $item)
                        @php
                            // Check individual menu item permission
                            $hasItemAccess = $isSuperAdmin;
                            if (!$hasItemAccess && $item->permission_key) {
                                $hasItemAccess = hasMenuAccess($item->permission_key, $permissionIds, $isSuperAdmin);
                            }
                        @endphp
                        
                        @if($hasItemAccess)
                            <div class="sub-menu-item">
                                <a href="{{ $item->url }}">
                                    <i class="{{ $item->icon }}"></i>
                                    <span class="menu-text">{{ $item->name }}</span>
                                    @if($item->link_type === 'table' && $item->table_name)
                                        <small style="color: rgba(255,255,255,0.7); font-size: 11px; margin-left: 5px;">
                                            ({{ $item->table_name }})
                                        </small>
                                    @endif
                                </a>
                            </div>
                        @endif
                    @empty
                        <div class="sub-menu-item">
                            <a href="#" style="color: #999; font-style: italic;">
                                <i class="fas fa-info-circle"></i>
                                <span class="menu-text">Belum ada sub menu</span>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <!-- Pengaturan Section -->
        <div class="menu-title">Pengaturan</div>
        
        <!-- Static Settings Menu -->
        @if($isSuperAdmin || hasPermission('settings', 'View/Lihat', $permissionIds, $isSuperAdmin))
        <div class="menu-item dropdown">
            <div class="dropdown-header">
                <i class="fa fa-cogs" aria-hidden="true"></i>
                <span class="menu-text">Setting</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>

            <div class="dropdown-content">
                @if($isSuperAdmin || hasPermission('dynamic_menu', 'View/Lihat', $permissionIds, $isSuperAdmin))
                <div class="sub-menu-item">
                    <a href="{{ route('settings.dynamic-menus.index') }}">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                        <span class="menu-text">Kelola Menu</span>
                    </a>
                </div>
                @endif

                @if($isSuperAdmin || hasPermission('dynamic_table', 'View/Lihat', $permissionIds, $isSuperAdmin))
                <div class="sub-menu-item">
                    <a href="{{ route('settings.dynamic-tables.index') }}">
                        <i class="fa fa-table" aria-hidden="true"></i>
                        <span class="menu-text">Kelola Tabel</span>
                    </a>
                </div>
                @endif

                @if($isSuperAdmin || hasPermission('api_management', 'View/Lihat', $permissionIds, $isSuperAdmin))
                <div class="sub-menu-item">
                    <a href="#" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.694 13.716a5.277 5.277 0 0 0 5.366-5.187a5.28 5.28 0 0 0-5.366-5.186c-.454 0-.906.055-1.347.165A4.93 4.93 0 0 0 10.882.75a4.855 4.855 0 0 0-4.9 4.342a4.38 4.38 0 0 0-4.043 4.3a4.4 4.4 0 0 0 4.471 4.322zm3.551 9.534v-6.534m-1.307 0h2.613m-2.613 6.534h2.613m-10.454 0v-2.614m.003 0h1.96a1.96 1.96 0 1 0 0-3.92H11.1zM1.949 23.25l.737-4.92a1.9 1.9 0 0 1 3.752 0l.738 4.92m-4.884-2.287h4.541"/></svg>
                        <span class="menu-text">Kelola API</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Dynamic Settings Menus -->
        @foreach($dynamicSettingsMenus as $menu)
            <div class="menu-item dropdown">
                <div class="dropdown-header">
                    <i class="{{ $menu->icon }}"></i>
                    <span class="menu-text">{{ $menu->name }}</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>
                <div class="dropdown-content">
                    @forelse($menu->activeItems as $item)
                        @php
                            $hasItemAccess = $isSuperAdmin;
                            if (!$hasItemAccess && $item->permission_key) {
                                $hasItemAccess = hasMenuAccess($item->permission_key, $permissionIds, $isSuperAdmin);
                            }
                        @endphp
                        
                        @if($hasItemAccess)
                            <div class="sub-menu-item">
                                <a href="{{ $item->url }}">
                                    <i class="{{ $item->icon }}"></i>
                                    <span class="menu-text">{{ $item->name }}</span>
                                </a>
                            </div>
                        @endif
                    @empty
                        <div class="sub-menu-item">
                            <a href="#" style="color: #999; font-style: italic;">
                                <i class="fas fa-info-circle"></i>
                                <span class="menu-text">Belum ada sub menu</span>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <!-- Privilege Menu -->
        @if($isSuperAdmin || hasPermission('privilege', 'View/Lihat', $permissionIds, $isSuperAdmin))
        <div class="menu-item dropdown">
            <div class="dropdown-header">
                <i class="fa-solid fa-shield"></i>
                <span class="menu-text">Privilege</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>

            <div class="dropdown-content">
                @if($isSuperAdmin || hasPermission('roles', 'View/Lihat', $permissionIds, $isSuperAdmin))
                <div class="sub-menu-item">
                    <a href="/showrole">
                        <i class="fas fa-user-cog"></i>
                        <span class="menu-text">Role</span>
                    </a>
                </div>
                @endif

                @if($isSuperAdmin || hasPermission('users', 'View/Lihat', $permissionIds, $isSuperAdmin))
                <div class="sub-menu-item">
                    <a href="/showuser">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <span class="menu-text">User</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Static Bottom Menus -->
        <div class="menu-item">
            <a href="#" style="text-decoration: none; color: inherit;">
                <i class="fas fa-question-circle"></i>
                <span class="menu-text">Bantuan</span>
            </a>
        </div>
    </nav>
</aside>