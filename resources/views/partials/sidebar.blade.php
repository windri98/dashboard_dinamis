@php
    // Redirect to forbidden page if not logged in
    if (!auth()->check()) {
        header('Location: /login');
        exit;
    }

    // Get the current user's role_id
    $userRoleId = auth()->user()->role_id;

    // Check if super admin
    $isSuperAdmin = $userRoleId == 1;

    // Get user's role with access permissions
    $userRole = App\Models\Roles::find($userRoleId);

    // Parse permissions from the role's akses field
    $permissions = [];
    if (!$isSuperAdmin && $userRole && $userRole->akses) {
        $permissions = is_string($userRole->akses) ? json_decode($userRole->akses, true) : $userRole->akses;
        $permissions = $permissions ?: [];
    }

    // Fungsi untuk memeriksa apakah pengguna memiliki izin tertentu
    function hasPermission($moduleKey, $actionKey, $permissions, $isSuperAdmin) {
        if ($isSuperAdmin) return true;
        
        // Jika module ada dalam permissions dan actionKey ada dalam array nilai module
        if (isset($permissions[$moduleKey]) && 
            is_array($permissions[$moduleKey]) && 
            in_array($actionKey, $permissions[$moduleKey])) {
            return true;
        }
        
        return false;
    }

    // Fungsi untuk memeriksa apakah pengguna memiliki salah satu izin (read, create, edit, delete)
    function hasAnyPermission($moduleKey, $permissions, $isSuperAdmin) {
        if ($isSuperAdmin) return true;
        
        $actions = ['read', 'create', 'edit', 'delete'];
        
        foreach ($actions as $action) {
            if (hasPermission($moduleKey, $action, $permissions, $isSuperAdmin)) {
                return true;
            }
        }
        
        return false;
    }

    // PERBAIKAN: Hapus relasi dynamicTable yang bermasalah
    $dynamicMainMenus = \App\Models\DynamicMenu::active()
        ->byCategory('main')
        ->ordered()
        ->with('activeItems') // Hapus ->dynamicTable dulu
        ->get()
        ->filter(function($menu) use ($permissions, $isSuperAdmin) {
            return $menu->hasUserPermission($permissions, $isSuperAdmin);
        });

    $dynamicSettingsMenus = \App\Models\DynamicMenu::active()
        ->byCategory('settings')
        ->ordered()
        ->with('activeItems') // Hapus ->dynamicTable dulu
        ->get()
        ->filter(function($menu) use ($permissions, $isSuperAdmin) {
            return $menu->hasUserPermission($permissions, $isSuperAdmin);
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
                        @if($item->hasUserPermission($permissions, $isSuperAdmin))
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
        <div class="menu-item dropdown">
            <div class="dropdown-header">
                <i class="fa fa-cogs" aria-hidden="true"></i>
                <span class="menu-text">Setting</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>
            <div class="dropdown-content">
                <div class="sub-menu-item">
                    <a href="{{ route('settings.dynamic-menus.index') }}">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                        <span class="menu-text">Kelola Menu</span>
                    </a>
                </div>

                <div class="sub-menu-item">
                    <a href="{{ route('settings.dynamic-tables.index') }}">
                        <i class="fa fa-table" aria-hidden="true"></i>
                        <span class="menu-text">Kelola Tabel</span>
                    </a>
                </div>

                <div class="sub-menu-item">
                    <a href="#" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.694 13.716a5.277 5.277 0 0 0 5.366-5.187a5.28 5.28 0 0 0-5.366-5.186c-.454 0-.906.055-1.347.165A4.93 4.93 0 0 0 10.882.75a4.855 4.855 0 0 0-4.9 4.342a4.38 4.38 0 0 0-4.043 4.3a4.4 4.4 0 0 0 4.471 4.322zm3.551 9.534v-6.534m-1.307 0h2.613m-2.613 6.534h2.613m-10.454 0v-2.614m.003 0h1.96a1.96 1.96 0 1 0 0-3.92H11.1zM1.949 23.25l.737-4.92a1.9 1.9 0 0 1 3.752 0l.738 4.92m-4.884-2.287h4.541"/></svg>
                        <span class="menu-text">Kelola API</span>
                    </a>
                </div>

            </div>
        </div>
        
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
                        @if($item->hasUserPermission($permissions, $isSuperAdmin))
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

        <div class="menu-item dropdown">
            <div class="dropdown-header">
                <i class="fa-solid fa-shield"></i>
                <span class="menu-text">Privilege</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>
            <div class="dropdown-content">
                <div class="sub-menu-item">
                    <a href="/showrole">
                        <i class="fas fa-user-cog"></i>
                        <span class="menu-text">Role</span>
                    </a>
                </div>
                <div class="sub-menu-item">
                    <a href="/showuser">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <span class="menu-text">User</span>
                    </a>
                </div>
                <div class="sub-menu-item">
                    <a href="#" style="display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                            <path fill="currentColor" fill-rule="evenodd" d="M23.255 5.87v-.816l-.746-.328l-10-4.4l-.504-.222l-.503.222l-10 4.4l-.747.328v13.88l.746.33l10 4.41l.504.222l.504-.223l10-4.41l.746-.328zm-10 4.956l7.5-3.307v9.786l-7.5 3.307zm-2.5 0l-7.5-3.307v9.786l7.5 3.307z" clip-rule="evenodd"/>
                        </svg>
                        <span class="menu-text" style="margin-left: 10px">Module</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- Static Bottom Menus -->
        <div class="menu-item">
            <a href="#" style="text-decoration: none; color: inherit;">
                <i class="fas fa-question-circle"></i>
                <span class="menu-text">Bantuan</span>
            </a>
        </div>
        {{-- <div class="menu-title">LAINNYA</div> --}}
        {{-- <div class="menu-item">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: inherit; padding: 12px 20px; width: 100%; text-align: left; cursor: pointer; display: flex; align-items: center; text-decoration: none;">
                    <i class="fas fa-sign-out-alt" style="width: 20px; margin-right: 12px;"></i>
                    <span class="menu-text">Keluar</span>
                </button>
            </form>
        </div> --}}
    </nav>
</aside>
