@extends('layouts.app')

@section('title', 'Kolom Tabel - ' . $dynamicTable->name)

@section('content')
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" onclick="this.closest('.alert').style.display='none'"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" onclick="this.closest('.alert').style.display='none'"></button>
        </div>
    @endif

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
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="openEditModalSafe(this)"
                                                    data-column-id="{{ $column->id }}"
                                                    data-column-data="{{ base64_encode(json_encode([
                                                        'id' => $column->id,
                                                        'name' => $column->name,
                                                        'type' => $column->type,
                                                        'is_required' => $column->is_required,
                                                        'is_searchable' => $column->is_searchable,
                                                        'is_sortable' => $column->is_sortable,
                                                        'show_in_list' => $column->show_in_list,
                                                        'is_active' => $column->is_active,
                                                        'order' => $column->order,
                                                        'options' => $column->options
                                                    ])) }}"
                                                    title="Edit Kolom">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-danger" 
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
                                <option value="time">Waktu (Time)</option>
                                <option value="datetime">Tanggal & Waktu (DateTime)</option>
                                <option value="boolean">Ya/Tidak (Boolean)</option>
                                <option value="enum">Pilihan (Enum)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Info untuk enum -->
                <div class="alert alert-info mt-2" id="enumWarning" style="display: none;" role="alert">
                    <strong>Catatan:</strong> Jika menggunakan tipe data <code>enum</code>, 
                    maka <u>data tidak dapat diedit</u> setelah dibuat.<br>
                    Pastikan penulisan opsi enum sudah benar sejak awal.
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
                                <option value="time">Waktu (Time)</option>
                                <option value="datetime">Tanggal & Waktu (DateTime)</option>
                                <option value="boolean">Ya/Tidak (Boolean)</option>
                                <option value="enum">Pilihan (Enum)</option>
                            </select>
                            <small class="text-muted">Pastikan perubahan tipe data sesuai kebutuhan.</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group d-none" id="edit_enum_options">
                    <label class="form-label">Pilihan Enum</label>
                    <div id="edit_enum_values">
                        {{-- Data enum akan diisi via JS saat openEditModal --}}
                    </div>
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
    /**
     * Tutup semua modal yang terbuka
     */
    function closeAllModals() {
        const modals = ['addColumnModal', 'editColumnModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    /**
     * Tutup modal tertentu
     */
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    /**
     * Tutup alert
     */
    function closeAlert(button) {
        const alert = button.closest('.alert');
        if (alert) {
            alert.remove();
        }
    }

    /**
     * Show alert message dengan auto dismiss yang benar
     */
    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-auto-dismiss');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show alert-auto-dismiss`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" onclick="closeAlert(this)"></button>
        `;
        
        // Insert after roles-header
        const rolesHeader = document.querySelector('.roles-header');
        if (rolesHeader) {
            rolesHeader.insertAdjacentElement('afterend', alertDiv);
        } else {
            // Fallback jika roles-header tidak ada
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertAdjacentElement('afterbegin', alertDiv);
            }
        }
        
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 4000);
    }

    /**
     * Buka modal tambah kolom
     */
    function openAddModal() {
        // Tutup modal lain yang mungkin terbuka
        closeAllModals();
        
        // Reset form
        const form = document.getElementById('addColumnForm');
        if (form) {
            form.reset();
        }
        
        // Set default values dengan safety check
        const searchable = document.getElementById('add_is_searchable');
        const sortable = document.getElementById('add_is_sortable');
        const showInList = document.getElementById('add_show_in_list');
        const order = document.getElementById('add_order');
        
        if (searchable) searchable.checked = true;
        if (sortable) sortable.checked = true;
        if (showInList) showInList.checked = true;
        if (order) order.value = 0;
        
        // Hide enum options initially
        toggleEnumOptions('add');
        
        // Show modal
        const modal = document.getElementById('addColumnModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    /**
     * Fungsi aman untuk membuka modal edit menggunakan data attributes
     */
    function openEditModalSafe(button) {
        try {
            const columnId = button.dataset.columnId;
            const columnDataBase64 = button.dataset.columnData;
            
            if (!columnDataBase64) {
                showAlert('error', 'Data kolom tidak ditemukan');
                return;
            }
            
            // Decode base64 dan parse JSON
            const columnData = JSON.parse(atob(columnDataBase64));
            
            // Call original function dengan data yang sudah di-decode
            openEditModal(columnId, columnData);
            
        } catch (error) {
            console.error('Error parsing column data:', error);
            showAlert('error', 'Gagal memuat data kolom');
        }
    }

    /**
     * Buka modal edit kolom (original function, tapi improved)
     */
    function openEditModal(id, column) {
        // Tutup modal lain yang mungkin terbuka
        closeAllModals();
        
        // Set form action dengan safety check
        const form = document.getElementById('editColumnForm');
        if (form) {
            form.action = '/settings/table-columns/' + id;
        }
        
        // Fill form data dengan safety check
        const nameField = document.getElementById('edit_name');
        const typeField = document.getElementById('edit_type');
        const orderField = document.getElementById('edit_order');
        
        if (nameField) nameField.value = column.name || '';
        if (typeField) typeField.value = column.type || '';
        if (orderField) orderField.value = column.order || 0;
        
        // Handle checkboxes dengan safety check
        const checkboxFields = [
            { id: 'edit_is_required', value: column.is_required },
            { id: 'edit_is_searchable', value: column.is_searchable },
            { id: 'edit_is_sortable', value: column.is_sortable },
            { id: 'edit_show_in_list', value: column.show_in_list },
            { id: 'edit_is_active', value: column.is_active }
        ];
        
        checkboxFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.checked = field.value || false;
            }
        });
        
        // Handle enum options
        handleEditEnumOptions(column);
        
        // Toggle enum options setelah set type
        toggleEnumOptions('edit');
        
        // Show modal
        const modal = document.getElementById('editColumnModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    /**
     * Handle enum options untuk edit modal
     */
    function handleEditEnumOptions(column) {
        const enumOptionsDiv = document.getElementById('edit_enum_options');
        const enumValuesContainer = document.getElementById('edit_enum_values');
        
        if (!enumOptionsDiv || !enumValuesContainer) {
            console.warn('Enum options containers not found');
            return;
        }
        
        // Clear existing options
        enumValuesContainer.innerHTML = '';
        
        if (column.type === 'enum' && column.options && column.options.values && Array.isArray(column.options.values)) {
            enumOptionsDiv.classList.remove('d-none');
            
            column.options.values.forEach(function(value) {
                if (value && value.trim()) {
                    addEnumOptionWithValue('edit', value);
                }
            });
            
            // Jika tidak ada nilai, tambahkan minimal 2 input kosong
            if (column.options.values.length === 0) {
                addEnumOptionWithValue('edit', '');
                addEnumOptionWithValue('edit', '');
            }
        } else {
            enumOptionsDiv.classList.add('d-none');
        }
    }

    /**
     * Add enum option dengan nilai tertentu
     */
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

    /**
     * Toggle enum options visibility
     */
    function toggleEnumOptions(prefix) {
        const typeSelect = document.getElementById(prefix + '_type');
        const enumOptions = document.getElementById(prefix + '_enum_options');
        const enumWarning = document.getElementById('enumWarning');
        
        if (!typeSelect || !enumOptions) return;
        
        if (typeSelect.value === 'enum') {
            enumOptions.classList.remove('d-none');
            
            // Show warning hanya untuk add modal
            if (enumWarning && prefix === 'add') {
                enumWarning.style.display = 'block';
            }
            
            // Pastikan ada minimal 2 input enum
            const existingInputs = enumOptions.querySelectorAll('input[name="options[values][]"]');
            if (existingInputs.length === 0) {
                addEnumOption(prefix);
                addEnumOption(prefix);
            }
        } else {
            enumOptions.classList.add('d-none');
            
            // Hide warning
            if (enumWarning && prefix === 'add') {
                enumWarning.style.display = 'none';
            }
        }
    }

    /**
     * Tambah option enum baru
     */
    function addEnumOption(prefix) {
        const container = document.getElementById(prefix + '_enum_values');
        if (!container) {
            console.warn('Container not found for prefix:', prefix);
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="options[values][]" placeholder="Pilihan baru">
            <button type="button" class="btn btn-outline-danger" onclick="removeEnumOption(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
        
        // Focus pada input yang baru ditambahkan
        const newInput = div.querySelector('input');
        if (newInput) {
            newInput.focus();
        }
    }

    /**
     * Hapus option enum
     */
    function removeEnumOption(button) {
        const inputGroup = button.closest('.input-group');
        const container = button.closest('[id$="_enum_values"]');
        
        if (inputGroup && container) {
            // Pastikan minimal ada 1 input yang tersisa
            const remainingInputs = container.querySelectorAll('.input-group');
            if (remainingInputs.length > 1) {
                inputGroup.remove();
            } else {
                showAlert('error', 'Minimal harus ada 1 pilihan enum');
            }
        }
    }

    /**
     * Delete column dengan konfirmasi
     */
    function deleteColumn(columnId) {
        if (!columnId) {
            showAlert('error', 'ID kolom tidak valid');
            return;
        }
        
        if (!confirm('Yakin ingin menghapus kolom ini? Data di kolom akan hilang permanen!')) {
            return;
        }
        
        // Create form dinamis untuk delete
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/settings/table-columns/' + columnId;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }
        
        // Add method field
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Validasi form sebelum submit
     */
    function validateForm(formData, isEdit = false) {
        const errors = [];
        
        // Validasi nama
        const name = formData.get('name');
        if (!name || name.trim().length === 0) {
            errors.push('Nama kolom wajib diisi');
        }
        
        // Validasi tipe
        const type = formData.get('type');
        if (!type) {
            errors.push('Tipe data wajib dipilih');
        }
        
        // Validasi order
        const order = formData.get('order');
        if (order === null || order < 0) {
            errors.push('Urutan harus angka positif');
        }
        
        // Validasi enum options
        if (type === 'enum') {
            const enumValues = formData.getAll('options[values][]');
            const validEnumValues = enumValues.filter(value => value && value.trim());
            
            if (validEnumValues.length === 0) {
                errors.push('Minimal harus ada 1 pilihan untuk tipe enum');
            }
            
            // Check duplicate values
            const uniqueValues = [...new Set(validEnumValues.map(v => v.trim().toLowerCase()))];
            if (uniqueValues.length !== validEnumValues.length) {
                errors.push('Pilihan enum tidak boleh ada yang sama');
            }
        }
        
        return errors;
    }

    /**
     * Handle ADD form submit
     */
    function handleAddFormSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('addSubmitBtn');
        if (!submitBtn) return;
        
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        const form = this;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }
        
        // Add all form fields
        formData.append('name', form.name.value.trim());
        formData.append('type', form.type.value);
        formData.append('order', form.order.value || 0);
        
        // Handle checkboxes - kirim 1 atau 0
        formData.append('is_required', form.is_required.checked ? '1' : '0');
        formData.append('is_searchable', form.is_searchable.checked ? '1' : '0');
        formData.append('is_sortable', form.is_sortable.checked ? '1' : '0');
        formData.append('show_in_list', form.show_in_list.checked ? '1' : '0');
        
        // Handle enum options
        const enumInputs = form.querySelectorAll('input[name="options[values][]"]');
        enumInputs.forEach(input => {
            if (input.value && input.value.trim()) {
                formData.append('options[values][]', input.value.trim());
            }
        });
        
        // Validasi form
        const validationErrors = validateForm(formData, false);
        if (validationErrors.length > 0) {
            showAlert('error', validationErrors.join('<br>'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }
        
        // Submit
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                showAlert('success', 'Kolom berhasil ditambahkan');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                return response.json().then(data => {
                    throw new Error(data.message || 'Gagal menambah kolom');
                });
            }
        })
        .catch(error => {
            showAlert('error', error.message || 'Terjadi kesalahan saat menambah kolom');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    /**
     * Handle EDIT form submit
     */
    function handleEditFormSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('editSubmitBtn');
        if (!submitBtn) return;
        
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        const form = this;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }
        
        // Add method
        formData.append('_method', 'PUT');
        
        // Add all form fields
        formData.append('name', form.name.value.trim());
        formData.append('type', form.type.value);
        formData.append('order', form.order.value || 0);
        
        // Handle checkboxes - kirim 1 atau 0
        formData.append('is_required', form.is_required.checked ? '1' : '0');
        formData.append('is_searchable', form.is_searchable.checked ? '1' : '0');
        formData.append('is_sortable', form.is_sortable.checked ? '1' : '0');
        formData.append('show_in_list', form.show_in_list.checked ? '1' : '0');
        formData.append('is_active', form.is_active.checked ? '1' : '0');
        
        // Handle enum options
        const enumInputs = form.querySelectorAll('input[name="options[values][]"]');
        enumInputs.forEach(input => {
            if (input.value && input.value.trim()) {
                formData.append('options[values][]', input.value.trim());
            }
        });
        
        // Validasi form
        const validationErrors = validateForm(formData, true);
        if (validationErrors.length > 0) {
            showAlert('error', validationErrors.join('<br>'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }
        
        // Submit
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                showAlert('success', 'Kolom berhasil diupdate');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                return response.json().then(data => {
                    throw new Error(data.message || 'Gagal mengupdate kolom');
                });
            }
        })
        .catch(error => {
            showAlert('error', error.message || 'Terjadi kesalahan saat mengupdate kolom');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    /**
     * Event listeners untuk close modal
     */
    function initializeEventListeners() {
        // Close modal ketika klik di luar
        window.onclick = function(event) {
            const modals = ['addColumnModal', 'editColumnModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && event.target === modal) {
                    closeModal(modalId);
                }
            });
        }

        // Close modal dengan ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });
    }

    /**
     * Initialize pada saat page load
     */
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing...');
        
        // Initialize event listeners
        initializeEventListeners();
        
        // Initialize enum options untuk add form
        toggleEnumOptions('add');
        
        // Bind form submit events
        const addForm = document.getElementById('addColumnForm');
        if (addForm) {
            addForm.addEventListener('submit', handleAddFormSubmit);
            console.log('Add form event listener attached');
        }

        const editForm = document.getElementById('editColumnForm');
        if (editForm) {
            editForm.addEventListener('submit', handleEditFormSubmit);
            console.log('Edit form event listener attached');
        }
        
        // Auto-dismiss existing alerts setelah 4 detik
        const existingAlerts = document.querySelectorAll('.alert:not(.alert-auto-dismiss)');
        existingAlerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.remove();
                }
            }, 4000);
        });
        
        console.log('JavaScript initialization complete');
    });
</script>

@endsection