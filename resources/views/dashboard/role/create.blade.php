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

    {{-- <div class="container-create">
        <form action="{{ route('create.role') }}" method="POST" class="form-container">
            @csrf
            <div class="form-group">
                <label for="role" class="form-label">Nama Role:</label>
                <input type="text" name="role" class="form-control" placeholder="Masukkan nama role" required>
            </div>

            <div class="form-group">
                <label class="form-label">Hak Akses:</label>
                <div class="permission-card">
                    <div class="table-responsive">
                        <table class="roles-table permission-table">
                            <thead>
                                <tr>
                                    <th>Modul</th>
                                    @foreach($actions as $action)
                                        <th class="text-center">{{ $action->nama }}</th>
                                    @endforeach
                                    <th class="text-center">Semua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $module)
                                    <tr>
                                        <td>{{ $module->nama }}</td>
                                        @foreach($actions as $action)
                                            <td class="text-center">
                                                <div class="checkbox-wrapper">
                                                    <input class="custom-checkbox module-action" 
                                                        type="checkbox" 
                                                        id="perm_{{ $module->id }}_{{ $action->id }}" 
                                                        name="permissions[{{ $module->id }}][]" 
                                                        value="{{ $action->id }}"
                                                        data-module="{{ $module->id }}">
                                                    <label for="perm_{{ $module->id }}_{{ $action->id }}"></label>
                                                </div>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <div class="checkbox-wrapper">
                                                <input class="custom-checkbox select-all" 
                                                    type="checkbox" 
                                                    id="select_all_{{ $module->id }}" 
                                                    data-module="{{ $module->id }}">
                                                <label for="select_all_{{ $module->id }}"></label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
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

            <div class="button-group">
                <button class="primary-button" type="submit">Simpan</button>
            </div>
        </form>
    </div> --}}

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

            {{-- Permissions --}}
            <div class="form-group">
                <label class="form-label">Hak Akses:</label>

                <div class="permission-card">
                    <div class="table-responsive">
                        <table class="roles-table permission-table">
                            <thead>
                                <tr>
                                    <th>Modul</th>
                                    @foreach($actions as $actionKey => $actionName)
                                        <th class="text-center">{{ $actionName }}</th>
                                    @endforeach
                                    <th class="text-center">Semua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permisson_key as $module)
                                    <tr>
                                        <td>{{ $module->permission_key }}</td>
                                        @foreach($actions as $actionKey => $actionName)
                                            <td class="text-center">
                                                <div class="checkbox-wrapper">
                                                    <input class="custom-checkbox module-action"
                                                        type="checkbox"
                                                        id="{{ $module->slug }}_{{ $actionKey }}"
                                                        name="permissions[{{ $module->slug }}][]"
                                                        value="{{ $actionKey }}"
                                                        data-module="{{ $module->slug }}">
                                                    <label for="{{ $module->slug }}_{{ $actionKey }}"></label>
                                                </div>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <div class="checkbox-wrapper">
                                                <input class="custom-checkbox select-all"
                                                    type="checkbox"
                                                    id="select_all_{{ $module->slug }}"
                                                    data-module="{{ $module->slug }}">
                                                <label for="select_all_{{ $module->slug }}"></label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Pilih Semua</strong></td>
                                    @foreach($actions as $actionKey => $actionName)
                                        <td class="text-center">
                                            <div class="checkbox-wrapper">
                                                <input class="custom-checkbox select-all-action"
                                                    type="checkbox"
                                                    id="select_all_{{ $actionKey }}"
                                                    data-action="{{ $actionKey }}">
                                                <label for="select_all_{{ $actionKey }}"></label>
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
    // Select all permissions
    const selectAllPermissions = document.getElementById('select_all_permissions');
    selectAllPermissions.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.module-action');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllPermissions.checked;
        });
        
        const allModuleSelectors = document.querySelectorAll('.select-all');
        allModuleSelectors.forEach(checkbox => {
            checkbox.checked = selectAllPermissions.checked;
        });
        
        const allActionSelectors = document.querySelectorAll('.select-all-action');
        allActionSelectors.forEach(checkbox => {
            checkbox.checked = selectAllPermissions.checked;
        });
        
        if (selectAllPermissions.checked) {
            alert('Perhatian: Mengaktifkan semua izin akan memberikan akses penuh ke seluruh sistem!');
        }
    });
    
    // Select all permissions for a specific module
    const selectAllModuleButtons = document.querySelectorAll('.select-all');
    selectAllModuleButtons.forEach(button => {
        button.addEventListener('change', function() {
            const module = button.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`.module-action[data-module="${module}"]`);
            
            moduleCheckboxes.forEach(checkbox => {
                checkbox.checked = button.checked;
            });
            
            updateSelectAllStatus();
        });
    });
    
    // Select all permissions for a specific action
    const selectAllActionButtons = document.querySelectorAll('.select-all-action');
    selectAllActionButtons.forEach(button => {
        button.addEventListener('change', function() {
            const action = button.getAttribute('data-action');
            const actionCheckboxes = document.querySelectorAll(`input[name$="[]"][value="${action}"]`);
            
            actionCheckboxes.forEach(checkbox => {
                checkbox.checked = button.checked;
            });
            
            updateSelectAllStatus();
        });
    });
    
    // Individual checkboxes
    const moduleActionCheckboxes = document.querySelectorAll('.module-action');
    moduleActionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateModuleSelectAllStatus(checkbox.getAttribute('data-module'));
            updateActionSelectAllStatus(checkbox.value);
            updateSelectAllStatus();
        });
    });
    
    // Function to update the status of a module's "Select All" checkbox
    function updateModuleSelectAllStatus(module) {
        const moduleCheckboxes = document.querySelectorAll(`.module-action[data-module="${module}"]`);
        const selectAllModuleCheckbox = document.getElementById(`select_all_${module}`);
        
        let allChecked = true;
        moduleCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });
        
        selectAllModuleCheckbox.checked = allChecked;
    }
    
    // Function to update the status of an action's "Select All" checkbox
    function updateActionSelectAllStatus(action) {
        const actionCheckboxes = document.querySelectorAll(`input[name$="[]"][value="${action}"]`);
        const selectAllActionCheckbox = document.getElementById(`select_all_${action}`);
        
        let allChecked = true;
        actionCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });
        
        selectAllActionCheckbox.checked = allChecked;
    }
    
    // Function to update the main "Select All" checkbox
    function updateSelectAllStatus() {
        const allCheckboxes = document.querySelectorAll('.module-action');
        const selectAllPermissionsCheckbox = document.getElementById('select_all_permissions');
        
        let allChecked = true;
        allCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });
        
        selectAllPermissionsCheckbox.checked = allChecked;
    }
});
</script>
@endsection