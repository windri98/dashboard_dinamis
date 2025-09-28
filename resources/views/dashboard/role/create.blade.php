@extends('layouts.app')

@section('content')

<section class="roles-section" id="create-roles">
    <div class="roles-header">
        <h1>Tambah Role</h1>
        <a href="/showrole" class="back-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            Kembali
        </a>
    </div>
    
    @if (session('success'))
        <div class="alert success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert error-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container-create">
        <form action="{{ route('create.role') }}" method="POST" class="form-container">
            @csrf
            {{-- Input Role --}}
            <div class="form-group">
                <label for="role" class="form-label">Nama Role:</label>
                <input type="text" name="role" id="role"
                    class="form-control"
                    placeholder="Masukkan nama role"
                    value="{{ old('role') }}"
                    required>
            </div>
            {{-- Permissions Table --}}
            <div class="form-group">
                <label class="form-label">Hak Akses:</label>
                <div class="permission-card">
                    <div class="table-responsive">
                        <table class="roles-table permission-table">
                            <thead>
                                <tr>
                                    <th>Modul / Sub-Modul</th>
                                    @foreach($actions as $action)
                                        <th class="text-center">{{ $action->nama }}</th>
                                    @endforeach
                                    <th class="text-center">Semua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedPermissions as $menuName => $menuData)
                                    {{-- Menu Header Row --}}
                                    <tr class="menu-header">
                                        <td colspan="{{ count($actions) + 2 }}">
                                            <strong class="menu-title">{{ $menuName }}</strong>
                                        </td>
                                    </tr>
                                    
                                    {{-- Menu-level permissions (jika ada) --}}
                                    @if(count($menuData['menu_permissions']) > 0)
                                        <tr class="menu-permission">
                                            <td class="permission-name">
                                                <span class="indent">└ {{ $menuName }} (Menu)</span>
                                            </td>
                                            @foreach($actions as $action)
                                                <td class="text-center">
                                                    @php
                                                        $menuPermission = collect($menuData['menu_permissions'])
                                                            ->where('action_id', $action->id)
                                                            ->first();
                                                    @endphp
                                                    @if($menuPermission)
                                                        <div class="checkbox-wrapper">
                                                            <input class="custom-checkbox permission-checkbox"
                                                                type="checkbox"
                                                                id="perm_{{ $menuPermission->id }}"
                                                                name="permissions[]"
                                                                value="{{ $menuPermission->id }}"
                                                                data-menu="{{ $menuData['menu']->id ?? 0 }}"
                                                                data-action="{{ $action->id }}">
                                                            <label for="perm_{{ $menuPermission->id }}"></label>
                                                        </div>
                                                    @else
                                                        <span class="no-permission">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                <div class="checkbox-wrapper">
                                                    <input class="custom-checkbox select-menu-all"
                                                        type="checkbox"
                                                        id="select_menu_{{ $menuData['menu']->id ?? 'unknown' }}"
                                                        data-menu="{{ $menuData['menu']->id ?? 0 }}">
                                                    <label for="select_menu_{{ $menuData['menu']->id ?? 'unknown' }}"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    {{-- Menu-item permissions --}}
                                    @foreach($menuData['menu_items'] as $menuItemName => $menuItemData)
                                        <tr class="menu-item-permission">
                                            <td class="permission-name">
                                                <span class="indent-sub">└── {{ $menuItemName }}</span>
                                            </td>
                                            @foreach($actions as $action)
                                                <td class="text-center">
                                                    @php
                                                        $itemPermission = collect($menuItemData['permissions'])
                                                            ->where('action_id', $action->id)
                                                            ->first();
                                                    @endphp
                                                    @if($itemPermission)
                                                        <div class="checkbox-wrapper">
                                                            <input class="custom-checkbox permission-checkbox"
                                                                type="checkbox"
                                                                id="perm_{{ $itemPermission->id }}"
                                                                name="permissions[]"
                                                                value="{{ $itemPermission->id }}"
                                                                data-menu="{{ $menuData['menu']->id ?? 0 }}"
                                                                data-menu-item="{{ $menuItemData['menu_item']->id ?? 0 }}"
                                                                data-action="{{ $action->id }}">
                                                            <label for="perm_{{ $itemPermission->id }}"></label>
                                                        </div>
                                                    @else
                                                        <span class="no-permission">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                <div class="checkbox-wrapper">
                                                    <input class="custom-checkbox select-item-all"
                                                        type="checkbox"
                                                        id="select_item_{{ $menuItemData['menu_item']->id ?? 'unknown' }}"
                                                        data-menu-item="{{ $menuItemData['menu_item']->id ?? 0 }}">
                                                    <label for="select_item_{{ $menuItemData['menu_item']->id ?? 'unknown' }}"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="select-all-footer">
                                    <td><strong>Pilih Semua</strong></td>
                                    @foreach($actions as $action)
                                        <td class="text-center">
                                            <div class="checkbox-wrapper">
                                                <input class="custom-checkbox select-all-action"
                                                    type="checkbox"
                                                    id="select_all_action_{{ $action->id }}"
                                                    data-action="{{ $action->id }}">
                                                <label for="select_all_action_{{ $action->id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        <div class="checkbox-wrapper">
                                            <input class="custom-checkbox"
                                                type="checkbox"
                                                id="select_all_permissions">
                                            <label for="select_all_permissions"></label>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            {{-- Submit --}}
            <div class="button-group">
                <button type="submit" class="primary-button">Simpan</button>
            </div>
        </form>
    </div>
</section>

<script>
        document.addEventListener('DOMContentLoaded', function() {
        // Select all permissions (master checkbox)
        const selectAllPermissions = document.getElementById('select_all_permissions');
        selectAllPermissions.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllPermissions.checked;
            });
            
            // Update semua checkbox "select all" lainnya
            updateAllSelectAllStatus();
            
            if (selectAllPermissions.checked) {
                alert('Perhatian: Mengaktifkan semua izin akan memberikan akses penuh ke seluruh sistem!');
            }
        });
        
        // Select all permissions untuk action tertentu
        const selectAllActionButtons = document.querySelectorAll('.select-all-action');
        selectAllActionButtons.forEach(button => {
            button.addEventListener('change', function() {
                const actionId = button.getAttribute('data-action');
                const actionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-action="${actionId}"]`);
                
                actionCheckboxes.forEach(checkbox => {
                    checkbox.checked = button.checked;
                });
                
                updateMasterSelectAllStatus();
            });
        });
        
        // Select all permissions untuk menu tertentu
        const selectMenuAllButtons = document.querySelectorAll('.select-menu-all');
        selectMenuAllButtons.forEach(button => {
            button.addEventListener('change', function() {
                const menuId = button.getAttribute('data-menu');
                const menuCheckboxes = document.querySelectorAll(`.permission-checkbox[data-menu="${menuId}"]`);
                
                menuCheckboxes.forEach(checkbox => {
                    checkbox.checked = button.checked;
                });
                
                updateMasterSelectAllStatus();
            });
        });
        
        // Select all permissions untuk menu-item tertentu
        const selectItemAllButtons = document.querySelectorAll('.select-item-all');
        selectItemAllButtons.forEach(button => {
            button.addEventListener('change', function() {
                const menuItemId = button.getAttribute('data-menu-item');
                const itemCheckboxes = document.querySelectorAll(`.permission-checkbox[data-menu-item="${menuItemId}"]`);
                
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = button.checked;
                });
                
                updateMasterSelectAllStatus();
            });
        });
        
        // Individual permission checkboxes
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        permissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllStatusForAction(checkbox.getAttribute('data-action'));
                updateSelectAllStatusForMenu(checkbox.getAttribute('data-menu'));
                updateSelectAllStatusForMenuItem(checkbox.getAttribute('data-menu-item'));
                updateMasterSelectAllStatus();
            });
        });
        
        // Update status "select all" untuk action tertentu
        function updateSelectAllStatusForAction(actionId) {
            const actionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-action="${actionId}"]`);
            const selectAllActionCheckbox = document.getElementById(`select_all_action_${actionId}`);
            
            let allChecked = true;
            actionCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });
            
            if (selectAllActionCheckbox) {
                selectAllActionCheckbox.checked = allChecked;
            }
        }
        
        // Update status "select all" untuk menu tertentu
        function updateSelectAllStatusForMenu(menuId) {
            if (!menuId) return;
            
            const menuCheckboxes = document.querySelectorAll(`.permission-checkbox[data-menu="${menuId}"]`);
            const selectMenuAllCheckbox = document.querySelector(`.select-menu-all[data-menu="${menuId}"]`);
            
            let allChecked = true;
            menuCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });
            
            if (selectMenuAllCheckbox) {
                selectMenuAllCheckbox.checked = allChecked;
            }
        }
        
        // Update status "select all" untuk menu-item tertentu
        function updateSelectAllStatusForMenuItem(menuItemId) {
            if (!menuItemId) return;
            
            const itemCheckboxes = document.querySelectorAll(`.permission-checkbox[data-menu-item="${menuItemId}"]`);
            const selectItemAllCheckbox = document.querySelector(`.select-item-all[data-menu-item="${menuItemId}"]`);
            
            let allChecked = true;
            itemCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });
            
            if (selectItemAllCheckbox) {
                selectItemAllCheckbox.checked = allChecked;
            }
        }
        
        // Update master "select all" checkbox
        function updateMasterSelectAllStatus() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            const selectAllPermissionsCheckbox = document.getElementById('select_all_permissions');
            
            let allChecked = true;
            allCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });
            
            selectAllPermissionsCheckbox.checked = allChecked;
        }
        
        // Update semua select all status
        function updateAllSelectAllStatus() {
            // Update action select all
            const actions = [...new Set(Array.from(document.querySelectorAll('.permission-checkbox')).map(cb => cb.getAttribute('data-action')))];
            actions.forEach(actionId => updateSelectAllStatusForAction(actionId));
            
            // Update menu select all
            const menus = [...new Set(Array.from(document.querySelectorAll('.permission-checkbox')).map(cb => cb.getAttribute('data-menu')).filter(id => id))];
            menus.forEach(menuId => updateSelectAllStatusForMenu(menuId));
            
            // Update menu-item select all
            const menuItems = [...new Set(Array.from(document.querySelectorAll('.permission-checkbox')).map(cb => cb.getAttribute('data-menu-item')).filter(id => id))];
            menuItems.forEach(menuItemId => updateSelectAllStatusForMenuItem(menuItemId));
        }
    });
</script>
@endsection