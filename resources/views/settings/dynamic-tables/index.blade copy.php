@extends('dashboard.index')

@section('content')
<section class="roles-section" id="role">
    <div class="roles-header">
    <h1>Tabel Dinamis</h1>
        <button onclick="openCreateModal()" class="add-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"/>
            </svg>
            Tambah Tabel
        </button> 
    </div>
    {{-- Table --}}
    <div class="table-container">
        @if($tables->count())
            <div class="table-responsive">
                <table class="roles-table">
                    <thead>
                        <tr>
                            <th>Nama Tabel</th>
                            <th>Table Name</th>
                            <th>Kolom</th>
                            <th>Digunakan di Menu</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $table)
                            <tr>
                                <td><strong>{{ $table->name }}</strong></td>
                                <td><code>{{ $table->table_name }}</code></td>
                                <td>
                                    {{ $table->columns->count() }} kolom
                                    <a href="{{ route('settings.dynamic-table-columns', $table) }}"
                                        class="btn btn-sm btn-outline-primary ms-1">
                                        <i class="fas fa-columns"></i> Kelola
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $usedInMenus = \App\Models\DynamicMenuItem::where('link_type', 'table')
                                            ->where('link_value', $table->id)
                                            ->with('menu')
                                            ->get();
                                    @endphp
                                    @if($usedInMenus->count())
                                        @foreach($usedInMenus as $menuItem)
                                            <span class="badge badge-info">
                                                {{ $menuItem->menu->name }} > {{ $menuItem->name }}
                                            </span><br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Belum digunakan</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $table->is_active ? 'success' : 'danger' }}">
                                        {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        {{-- Edit --}}
                                        <button type="button" class="btn btn-sm btn-primary"
                                            onclick="openEditModal('{{ $table->id }}', '{{ $table->name }}', '{{ $table->columns->pluck('name')->implode(', ') }}')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"xmlns="http://www.w3.org/2000/svg"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"stroke="currentColor" stroke-width="2"stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"stroke="currentColor" stroke-width="2"stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>

                                        {{-- Delete --}}
                                        <form action="{{ route('settings.dynamic-tables.destroy', $table->id) }}"method="POST" class="delete-form"onsubmit="return confirm('Are you sure you want to delete this table?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"stroke="currentColor" stroke-width="2"stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<div id="tableModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="tableModalTitle">Tambah Tabel</h3>
            <button class="close" onclick="closeModal('tableModal')" type="button">×</button>
        </div>
        
        <form id="tableForm" method="POST" action="{{ route('settings.dynamic-tables.store') }}">
            @csrf
            <div id="methodField"></div>
            
            <div class="form-group">
                <label for="tableName">Nama Tabel *</label>
                <input type="text" id="tableName" name="name" required maxlength="255">
                <div class="error" id="nameError"></div>
            </div>
            
            <div class="form-group">
                <label for="tableDescription">Deskripsi</label>
                <textarea id="tableDescription" name="description" placeholder="Opsional - deskripsikan tabel ini"></textarea>
                <div class="error" id="descriptionError"></div>
            </div>

            <div class="form-group" id="activeGroup" style="display: none;">
                <label class="checkbox-label">
                    <input type="checkbox" id="tableActive" name="is_active" value="1">
                    <span>Aktifkan tabel</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('tableModal')" style="background:#6c757d;color:white;">Batal</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let isEditMode = false;
    
    function openCreateModal() {
        isEditMode = false;
        
        // Reset form
        resetForm();
        
        // Setup untuk create
        document.getElementById('tableForm').action = "{{ route('settings.dynamic-tables.store') }}";
        document.getElementById('tableModalTitle').textContent = "Tambah Tabel Baru";
        document.getElementById('submitText').textContent = "Simpan";
        
        // Sembunyikan checkbox is_active untuk create (biasanya default false)
        document.getElementById('activeGroup').style.display = 'none';
        
        // Hapus method field
        document.getElementById('methodField').innerHTML = '';
        
        showModal();
    }

    function openEditModal(id, name, description = '', is_active = 0) {
        isEditMode = true;
        
        // Reset form dulu
        resetForm();
        
        // Setup untuk edit
        document.getElementById('tableForm').action = `/settings/dynamic-tables/${id}`;
        document.getElementById('tableModalTitle').textContent = "Edit Tabel";
        document.getElementById('submitText').textContent = "Perbarui";
        
        // Tampilkan checkbox is_active untuk edit
        document.getElementById('activeGroup').style.display = 'block';
        
        // Inject method PUT
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        // Isi data
        document.getElementById('tableName').value = name || '';
        document.getElementById('tableDescription').value = description || '';
        document.getElementById('tableActive').checked = is_active == 1;
        
        showModal();
    }
    
    function resetForm() {
        // Clear semua input
        document.getElementById('tableName').value = '';
        document.getElementById('tableDescription').value = '';
        document.getElementById('tableActive').checked = false;
        
        // Clear error messages
        clearErrors();
    }
    
    function clearErrors() {
        document.getElementById('nameError').textContent = '';
        document.getElementById('descriptionError').textContent = '';
    }
    
    function showModal() {
        document.getElementById('tableModal').style.display = 'block';
        // Focus ke input pertama
        setTimeout(() => {
            document.getElementById('tableName').focus();
        }, 100);
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        resetForm();
    }
    
    // Handle form submission
    document.getElementById('tableForm').addEventListener('submit', function(e) {
        clearErrors();
        
        // Basic validation
        const name = document.getElementById('tableName').value.trim();
        if (!name) {
            e.preventDefault();
            document.getElementById('nameError').textContent = 'Nama tabel wajib diisi';
            document.getElementById('tableName').focus();
            return false;
        }
        
        if (name.length > 255) {
            e.preventDefault();
            document.getElementById('nameError').textContent = 'Nama tabel maksimal 255 karakter';
            document.getElementById('tableName').focus();
            return false;
        }
        
        // Disable submit button untuk prevent double submission
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Menyimpan...</span>';
    });
    
    // Close modal kalau klik di luar
    document.getElementById('tableModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('tableModal');
        }
    });
    
    // Handle ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('tableModal');
            if (modal.style.display === 'block') {
                closeModal('tableModal');
            }
        }
    });
</script>

{{-- <div id="tableModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="tableModalTitle">Tambah Tabel</h3>
            <button class="close" onclick="closeModal('tableModal')" type="button">×</button>
        </div>
        
        <form id="tableForm" method="POST" action="{{ route('settings.dynamic-tables.store') }}">
            @csrf
            <div id="methodField"></div>
            
            <div class="form-group">
                <label for="tableName">Nama Tabel *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="tableName" name="name" value="{{ old('name') }}" required>
                <small id="nameError" class="text-danger"></small>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="tableDescription" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          id="tableDescription" name="description" rows="3">{{ old('description') }}</textarea>
                <small id="descriptionError" class="text-danger"></small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" id="activeGroup" style="display: none;">
                <label class="checkbox-label">
                    <input class="form-check-input" type="checkbox" id="tableActive" name="is_active" value="1">
                    <label class="form-check-label" for="tableActive">Aktifkan tabel</label>
                </label>
            </div>

            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('tableModal')" style="background:#6c757d;color:white;">Batal</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    let isEditMode = false;
    
    function openCreateModal() {
        isEditMode = false;
        
        // Reset form
        resetForm();
        
        // Setup untuk create
        document.getElementById('tableForm').action = "{{ route('settings.dynamic-tables.store') }}";
        document.getElementById('tableModalTitle').textContent = "Tambah Tabel Baru";
        document.getElementById('submitText').textContent = "Simpan";
        
        // Sembunyikan checkbox is_active untuk create (biasanya default false)
        document.getElementById('activeGroup').style.display = 'none';
        
        // Hapus method field
        document.getElementById('methodField').innerHTML = '';
        
        showModal();
    }

    function openEditModal(id, name, description = '', is_active = 0) {
        isEditMode = true;
        
        // Reset form dulu
        resetForm();
        
        // Setup untuk edit
        document.getElementById('tableForm').action = `/settings/dynamic-tables/${id}`;
        document.getElementById('tableModalTitle').textContent = "Edit Tabel";
        document.getElementById('submitText').textContent = "Perbarui";
        
        // Tampilkan checkbox is_active untuk edit
        document.getElementById('activeGroup').style.display = 'block';
        
        // Inject method PUT
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        // Isi data
        document.getElementById('tableName').value = name || '';
        document.getElementById('tableDescription').value = description || '';
        document.getElementById('tableActive').checked = is_active == 1;
        
        showModal();
    }
    
    function resetForm() {
        // Clear semua input
        document.getElementById('tableName').value = '';
        document.getElementById('tableDescription').value = '';
        document.getElementById('tableActive').checked = false;
        
        // Clear error messages
        clearErrors();
    }
    
    function clearErrors() {
        document.getElementById('nameError').textContent = '';
        document.getElementById('descriptionError').textContent = '';
    }
    
    function showModal() {
        document.getElementById('tableModal').style.display = 'block';
        // Focus ke input pertama
        setTimeout(() => {
            document.getElementById('tableName').focus();
        }, 100);
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        resetForm();
    }
    
    // Handle form submission
    document.getElementById('tableForm').addEventListener('submit', function(e) {
        clearErrors();
        
        // Basic validation
        const name = document.getElementById('tableName').value.trim();
        if (!name) {
            e.preventDefault();
            document.getElementById('nameError').textContent = 'Nama tabel wajib diisi';
            document.getElementById('tableName').focus();
            return false;
        }
        
        if (name.length > 255) {
            e.preventDefault();
            document.getElementById('nameError').textContent = 'Nama tabel maksimal 255 karakter';
            document.getElementById('tableName').focus();
            return false;
        }
        
        // Disable submit button untuk prevent double submission
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Menyimpan...</span>';
    });
    
    // Close modal kalau klik di luar
    document.getElementById('tableModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('tableModal');
        }
    });
    
    // Handle ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('tableModal');
            if (modal.style.display === 'block') {
                closeModal('tableModal');
            }
        }
    });
</script> --}}

@endsection
