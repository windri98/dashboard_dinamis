@extends('layouts.app')

@section('title', 'Kolom Tabel - ' . $dynamicTable->name)

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

<div class="container-fluid">
    <div class="roles-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-table me-2"></i>Kolom: {{ $dynamicTable->name }}
            </h1>
            <p class="mb-0 text-muted">Kelola struktur kolom tabel <code>{{ $dynamicTable->table_name }}</code></p>
        </div>
        <div class="btn">
            <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Tambah Kolom
            </button>
            <a href="{{ route('settings.dynamic-tables.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-columns"></i> Daftar Kolom Tabel
            </h6>
        </div>
        <div class="card-body">
            @if($dynamicTable->columns->count() > 0)
                <div class="table-responsive">
                    <table class="roles-table">
                        <thead class="table-light">
                            <tr>
                                <th width="200">Nama Kolom</th>
                                <th width="150">Database Column</th>
                                <th width="120">Tipe Data</th>
                                <th width="200">Properties</th>
                                <th width="80">Urutan</th>
                                <th width="80">Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dynamicTable->columns->sortBy('order') as $column)
                                <tr>
                                    <td>
                                        <strong>{{ $column->name }}</strong>
                                        @if($column->is_required)
                                            <span class="badge bg-danger ms-1">Required</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $column->column_name }}</code></td>
                                    <td>
                                        <span class="badge bg-info">{{ $column->type_label ?? ucfirst($column->type) }}</span>
                                        @if($column->type === 'enum' && $column->options)
                                            <br><small class="text-muted mt-1">
                                                Options: {{ implode(', ', $column->options['values'] ?? []) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($column->is_searchable)
                                                <span class="badge bg-success">Searchable</span>
                                            @endif
                                            @if($column->is_sortable)
                                                <span class="badge bg-primary">Sortable</span>
                                            @endif
                                            @if($column->show_in_list)
                                                <span class="badge bg-info">Show in List</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $column->order }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $column->is_active ? 'success' : 'danger' }}">
                                            {{ $column->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary" 
                                                    onclick="openEditModalSafe(this)"
                                                    data-column-id="{{ $column->id }}"
                                                    data-column-name="{{ $column->name }}"
                                                    data-column-type="{{ $column->type }}"
                                                    data-column-order="{{ $column->order }}"
                                                    data-is-required="{{ $column->is_required ? '1' : '0' }}"
                                                    data-is-searchable="{{ $column->is_searchable ? '1' : '0' }}"
                                                    data-is-sortable="{{ $column->is_sortable ? '1' : '0' }}"
                                                    data-show-in-list="{{ $column->show_in_list ? '1' : '0' }}"
                                                    data-is-active="{{ $column->is_active ? '1' : '0' }}"
                                                    data-options='@json($column->options)'
                                                    title="Edit Kolom">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deleteColumn({{ $column->id }})"
                                                    title="Hapus Kolom">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-columns fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Belum ada kolom</h5>
                    <p class="text-gray-400">Tambahkan kolom pertama untuk tabel {{ $dynamicTable->name }}</p>
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Tambah Kolom Pertama
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Column Modal -->
<div id="addColumnModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-plus"></i> Tambah Kolom Baru
            </h5>
            <button type="button" class="close" onclick="closeModal('addColumnModal')">&times;</button>
        </div>
        <form id="addColumnForm" method="POST" action="{{ route('settings.dynamic-table-columns.store', $dynamicTable) }}">
            @csrf
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="add_name" class="form-label">
                                Nama Kolom <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_name" name="name" required 
                                placeholder="Contoh: Nama Lengkap">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="add_type" class="form-label">
                                Tipe Data <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="add_type" name="type" required onchange="toggleEnumOptions('add')">
                                <option value="">Pilih Tipe Data</option>
                                <option value="string">Text Pendek (String)</option>
                                <option value="text">Text Panjang (Text)</option>
                                <option value="integer">Angka Bulat (Integer)</option>
                                <option value="decimal">Angka Desimal (Decimal)</option>
                                <option value="date">Tanggal (Date)</option>
                                <option value="datetime">Tanggal & Waktu (DateTime)</option>
                                <option value="boolean">Ya/Tidak (Boolean)</option>
                                <option value="select">Pilihan (Select)</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="file">📁 File Upload</option>
                                <option value="image">🖼️ Image Upload</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-2" id="enumWarning" style="display: none;" role="alert">
                    <strong>Catatan:</strong> Jika menggunakan tipe data <code>enum</code>, 
                    pastikan penulisan opsi enum sudah benar sejak awal.
                </div>
                
                <div class="form-group d-none" id="add_enum_options">
                    <label class="form-label">
                        Pilihan Enum <span class="text-danger">*</span>
                    </label>
                    <div id="add_enum_values">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="options[values][]" placeholder="Pilihan 1">
                            <button type="button" class="btn btn-outline-danger" onclick="removeEnumOption(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="options[values][]" placeholder="Pilihan 2">
                            <button type="button" class="btn btn-outline-danger" onclick="removeEnumOption(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEnumOption('add')">
                        <i class="fas fa-plus"></i> Tambah Pilihan
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="add_order" class="form-label">
                                Urutan <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="add_order" name="order" 
                                value="0" min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <label class="form-label">Pengaturan Kolom</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_is_required" 
                                        name="is_required" value="1">
                                    <label class="form-check-label" for="add_is_required">Wajib Diisi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_is_searchable" 
                                        name="is_searchable" value="1" checked>
                                    <label class="form-check-label" for="add_is_searchable">Bisa Dicari</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_is_sortable" 
                                        name="is_sortable" value="1" checked>
                                    <label class="form-check-label" for="add_is_sortable">Bisa Diurutkan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_show_in_list" 
                                        name="show_in_list" value="1" checked>
                                    <label class="form-check-label" for="add_show_in_list">Tampil di List</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addColumnModal')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" id="addSubmitBtn">
                    <i class="fas fa-save"></i> Simpan Kolom
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Column Modal -->
<div id="editColumnModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-edit"></i> Edit Kolom
            </h5>
            <button type="button" class="close" onclick="closeModal('editColumnModal')">&times;</button>
        </div>
        <form id="editColumnForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_name" class="form-label">
                                Nama Kolom <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_type" class="form-label">
                                Tipe Data <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="edit_type" name="type" required onchange="toggleEnumOptions('edit')">
                                <option value="">Pilih Tipe Data</option>
                                <option value="string">Text Pendek (String)</option>
                                <option value="text">Text Panjang (Text)</option>
                                <option value="integer">Angka Bulat (Integer)</option>
                                <option value="decimal">Angka Desimal (Decimal)</option>
                                <option value="date">Tanggal (Date)</option>
                                <option value="datetime">Tanggal & Waktu (DateTime)</option>
                                <option value="boolean">Ya/Tidak (Boolean)</option>
                                <option value="select">Pilihan (Select)</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="file">📁 File Upload</option>
                                <option value="image">🖼️ Image Upload</option>
                            </select>
                            <small class="text-muted">Pastikan perubahan tipe data sesuai kebutuhan.</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group d-none" id="edit_enum_options">
                    <label class="form-label">Pilihan Enum</label>
                    <div id="edit_enum_values"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEnumOption('edit')">
                        <i class="fas fa-plus"></i> Tambah Pilihan
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_order" class="form-label">
                                Urutan <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_order" name="order" 
                                min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <label class="form-label">Pengaturan Kolom</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_required" 
                                        name="is_required" value="1">
                                    <label class="form-check-label" for="edit_is_required">Wajib Diisi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_searchable" 
                                        name="is_searchable" value="1">
                                    <label class="form-check-label" for="edit_is_searchable">Bisa Dicari</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_sortable" 
                                        name="is_sortable" value="1">
                                    <label class="form-check-label" for="edit_is_sortable">Bisa Diurutkan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_show_in_list" 
                                        name="show_in_list" value="1">
                                    <label class="form-check-label" for="edit_show_in_list">Tampil di List</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" 
                                name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Kolom Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editColumnModal')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                    <i class="fas fa-save"></i> Update Kolom
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeAllModals() {
        const modals = ['addColumnModal', 'editColumnModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        });
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }

    function openAddModal() {
        closeAllModals();
        const form = document.getElementById('addColumnForm');
        if (form) form.reset();
        
        const searchable = document.getElementById('add_is_searchable');
        const sortable = document.getElementById('add_is_sortable');
        const showInList = document.getElementById('add_show_in_list');
        const order = document.getElementById('add_order');
        
        if (searchable) searchable.checked = true;
        if (sortable) sortable.checked = true;
        if (showInList) showInList.checked = true;
        if (order) order.value = 0;
        
        toggleEnumOptions('add');
        
        const modal = document.getElementById('addColumnModal');
        if (modal) modal.style.display = 'block';
    }

    function openEditModalSafe(button) {
        try {
            const columnData = {
                id: button.dataset.columnId,
                name: button.dataset.columnName,
                type: button.dataset.columnType,
                order: button.dataset.columnOrder,
                is_required: button.dataset.isRequired === '1',
                is_searchable: button.dataset.isSearchable === '1',
                is_sortable: button.dataset.isSortable === '1',
                show_in_list: button.dataset.showInList === '1',
                is_active: button.dataset.isActive === '1',
                options: JSON.parse(button.dataset.options || '{}')
            };
            
            openEditModal(columnData.id, columnData);
        } catch (error) {
            console.error('Error parsing column data:', error);
            showAlert('error', 'Gagal memuat data kolom');
        }
    }

    function openEditModal(id, column) {
        closeAllModals();
        
        const form = document.getElementById('editColumnForm');
        if (form) form.action = '/settings/table-columns/' + id;
        
        const nameField = document.getElementById('edit_name');
        const typeField = document.getElementById('edit_type');
        const orderField = document.getElementById('edit_order');
        
        if (nameField) nameField.value = column.name || '';
        if (typeField) typeField.value = column.type || '';
        if (orderField) orderField.value = column.order || 0;
        
        const checkboxFields = [
            { id: 'edit_is_required', value: column.is_required },
            { id: 'edit_is_searchable', value: column.is_searchable },
            { id: 'edit_is_sortable', value: column.is_sortable },
            { id: 'edit_show_in_list', value: column.show_in_list },
            { id: 'edit_is_active', value: column.is_active }
        ];
        
        checkboxFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) element.checked = field.value || false;
        });
        
        handleEditEnumOptions(column);
        toggleEnumOptions('edit');
        
        const modal = document.getElementById('editColumnModal');
        if (modal) modal.style.display = 'block';
    }

    function handleEditEnumOptions(column) {
        const enumOptionsDiv = document.getElementById('edit_enum_options');
        const enumValuesContainer = document.getElementById('edit_enum_values');
        
        if (!enumOptionsDiv || !enumValuesContainer) return;
        
        enumValuesContainer.innerHTML = '';
        
        if ((column.type === 'select' || column.type === 'radio' || column.type === 'checkbox') && column.options && column.options.values && Array.isArray(column.options.values)) {
            enumOptionsDiv.classList.remove('d-none');
            
            column.options.values.forEach(function(value) {
                if (value && value.trim()) {
                    addEnumOptionWithValue('edit', value);
                }
            });
            
            if (column.options.values.length === 0) {
                addEnumOptionWithValue('edit', '');
                addEnumOptionWithValue('edit', '');
            }
        } else {
            enumOptionsDiv.classList.add('d-none');
        }
    }

    function addEnumOptionWithValue(prefix, value = '') {
        const container = document.getElementById(prefix + '_enum_values');
        if (!container) return;
        
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="options[values][]" value="${value}" placeholder="Pilihan">
            <button type="button" class="btn btn-outline-danger" onclick="removeEnumOption(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function toggleEnumOptions(prefix) {
        const typeSelect = document.getElementById(prefix + '_type');
        const enumOptions = document.getElementById(prefix + '_enum_options');
        const enumWarning = document.getElementById('enumWarning');
        
        if (!typeSelect || !enumOptions) return;
        
        if (typeSelect.value === 'select' || typeSelect.value === 'radio' || typeSelect.value === 'checkbox') {
            enumOptions.classList.remove('d-none');
            
            if (enumWarning && prefix === 'add') {
                enumWarning.style.display = 'block';
            }
            
            const existingInputs = enumOptions.querySelectorAll('input[name="options[values][]"]');
            if (existingInputs.length === 0) {
                addEnumOption(prefix);
                addEnumOption(prefix);
            }
        } else {
            enumOptions.classList.add('d-none');
            
            if (enumWarning && prefix === 'add') {
                enumWarning.style.display = 'none';
            }
        }
    }

    function addEnumOption(prefix) {
        const container = document.getElementById(prefix + '_enum_values');
        if (!container) return;
        
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="options[values][]" placeholder="Pilihan baru">
            <button type="button" class="btn btn-outline-danger" onclick="removeEnumOption(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
        
        const newInput = div.querySelector('input');
        if (newInput) newInput.focus();
    }

    function removeEnumOption(button) {
        const inputGroup = button.closest('.input-group');
        const container = button.closest('[id$="_enum_values"]');
        
        if (inputGroup && container) {
            const remainingInputs = container.querySelectorAll('.input-group');
            if (remainingInputs.length > 1) {
                inputGroup.remove();
            } else {
                showAlert('error', 'Minimal harus ada 1 pilihan enum');
            }
        }
    }

    function deleteColumn(columnId) {
        if (!columnId) {
            showAlert('error', 'ID kolom tidak valid');
            return;
        }
        
        if (!confirm('Yakin ingin menghapus kolom ini? Data di kolom akan hilang permanen!')) {
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/settings/table-columns/' + columnId;
        form.style.display = 'none';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.onclick = function(event) {
            const modals = ['addColumnModal', 'editColumnModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && event.target === modal) {
                    closeModal(modalId);
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });
        
        toggleEnumOptions('add');
        
        const addForm = document.getElementById('addColumnForm');
        const editForm = document.getElementById('editColumnForm');
        
        if (addForm) {
            addForm.onsubmit = function() {
                const btn = document.getElementById('addSubmitBtn');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                    btn.disabled = true;
                }
            }
        }
        
        if (editForm) {
            editForm.onsubmit = function() {
                const btn = document.getElementById('editSubmitBtn');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
                    btn.disabled = true;
                }
            }
        }
    });
</script>

@endsection