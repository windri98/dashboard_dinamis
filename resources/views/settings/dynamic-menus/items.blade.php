@extends('layouts.app')

@section('content')
    <div class="content-header">
        <h1 id="pageTitle">Kelola Menu : {{ $dynamicMenu->name }}</h1>
        <p id="pageDescription">Tambah, edit, atau hapus menu dropdown</p>
    </div>
    <div class="content-body" id="contentArea">
        <div class="btn" style="display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn btn-primary" onclick="openSubmenuModal({{ $dynamicMenu->id }})">
                <i class="fas fa-plus"></i> Tambah Sub Menu 
            </button>

            <a href="{{ route('settings.dynamic-menus.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>Nama Menu</th>
                        <th>Icon</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dynamicMenu->items as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td><i class="{{ $item->icon }}"></i> {{ $item->icon }}</td>
                            <td>
                                <span class="badge 
                                    {{ $item->link_type === 'table' ? 'badge-primary' : 
                                    ($item->link_type === 'route' ? 'badge-success' : 'badge-info') }}">
                                    {{ $item->link_type === 'table' ? 'Menu Utama' : ucfirst($item->link_type) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-{{ $item->is_active ? 'success' : 'danger' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='openEditSubmenuModal(@json($item))'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('settings.dynamic-menu-items.destroy', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus menu ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create/Edit Sub Menu --}}
    <div id="submenuModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="submenuModalTitle">Tambah Sub Menu</h3>
                <button class="close" onclick="closeModal('submenuModal')">&times;</button>
            </div>
            <form id="submenuForm" method="POST" action="{{ route('settings.dynamic-menu-items.store') }}">
                @csrf
                <div id="submenuMethodField"></div>

                {{-- Dynamic Menu ID --}}
                <input type="hidden" name="dynamic_menu_id" value="{{ $dynamicMenu->id ?? 1 }}">
                <input type="hidden" id="submenuParentId" name="parent_id">
                <input type="hidden" id="submenuId" name="id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="submenuName" class="form-label">
                            Nama Sub Menu <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="submenuName" name="name" required 
                            placeholder="Contoh: Kelola Pengguna" maxlength="50">
                        <small class="text-muted">Maksimal 50 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="submenuIcon" class="form-label">
                            Icon Font Awesome <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="submenuIcon" name="icon" required 
                            placeholder="fas fa-users" value="fas fa-link">
                        <small class="text-muted">Contoh: fas fa-users, fas fa-chart-bar</small>
                    </div>

                    <div class="form-group">
                        <label for="submenuLinkType" class="form-label">
                            Tipe Link <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="submenuLinkType" name="link_type" required onchange="toggleSubmenuInputs()">
                            <option value="table">Tabel Dinamis</option>
                            <option value="route">Route Laravel</option>
                            <option value="url">URL Eksternal</option>
                        </select>
                    </div>

                    {{-- Input untuk Tabel Dinamis --}}
                    <div class="form-group" id="submenuTableGroup">
                        <label for="submenuTableSelect" class="form-label">
                            Pilih Tabel <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="submenuTableSelect" name="link_value">
                            <option value="">Pilih Tabel</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">{{ $table->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input untuk Route Laravel --}}
                    <div class="form-group" id="submenuRouteGroup" style="display: none;">
                        <label for="submenuRouteInput" class="form-label">
                            Nama Route <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="submenuRouteInput" name="link_value_route" 
                            placeholder="dashboard.analytics">
                        <small class="text-muted">Contoh: dashboard.index, reports.monthly</small>
                    </div>

                    {{-- Input untuk URL Eksternal --}}
                    <div class="form-group" id="submenuUrlGroup" style="display: none;">
                        <label for="submenuUrlInput" class="form-label">
                            URL Eksternal <span class="text-danger">*</span>
                        </label>
                        <input type="url" class="form-control" id="submenuUrlInput" name="link_value_url" 
                            placeholder="https://example.com">
                        <small class="text-muted">URL lengkap dengan http:// atau https://</small>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="submenuPermissionKey" class="form-label">Permission Key</label>
                                <input type="text" class="form-control" id="submenuPermissionKey" name="permission_key" 
                                    placeholder="ex: kelola_pengguna" maxlength="50">
                                <small class="text-muted">Kosongkan jika semua user bisa akses. Otomatis terisi dari nama jika kosong.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="submenuOrder" class="form-label">
                                    Urutan <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="submenuOrder" name="order" 
                                    value="0" min="0" required>
                            </div>
                        </div>
                    </div>

                    {{-- Checkbox untuk Edit Mode dengan hidden input untuk handle unchecked state --}}
                    <div class="form-group" id="submenuActiveGroup" style="display: none;">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="submenuIsActive" name="is_active" value="1">
                            <label class="form-check-label" for="submenuIsActive">
                                Sub Menu Aktif
                            </label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('submenuModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submenuSubmitBtn">
                        <i class="fas fa-save"></i> Simpan Sub Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Create/Edit Menu Utama --}}
    <div id="tableModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="tableModalTitle">Tambah Menu Baru</h3>
                <button class="close" onclick="closeModal('tableModal')">×</button>
            </div>
            <form id="tableForm" method="POST" action="{{ route('settings.dynamic-menu-items.store') }}">
                @csrf
                <div id="methodField"></div>

                {{-- Dynamic Menu ID --}}
                <input type="hidden" name="dynamic_menu_id" value="{{ $dynamicMenu->id ?? 1 }}">
                <input type="hidden" id="menuId" name="id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="menuName" class="form-label">
                            Nama Menu <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="menuName" name="name" required 
                            placeholder="Contoh: Master Data" maxlength="50">
                        <small class="text-muted">Maksimal 50 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="menuIcon" class="form-label">
                            Icon Font Awesome <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="menuIcon" name="icon" required
                            placeholder="fas fa-link" value="fas fa-link">
                        <small class="text-muted">Contoh: fas fa-database, fas fa-cog</small>
                    </div>

                    <div class="form-group">
                        <label for="linkType" class="form-label">
                            Tipe Link <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" name="link_type" id="linkType" required>
                            <option value="table">Table</option>
                            <option value="route">Route</option>
                            <option value="url">URL</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="linkValue" class="form-label">
                            Nilai Link <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="linkValue" name="link_value" 
                            placeholder="contoh : dashboard" required>
                        <small class="text-muted">Sesuaikan dengan tipe link yang dipilih</small>
                    </div>

                    <div class="form-group">
                        <label for="permissionKey" class="form-label">Permission Key</label>
                        <input type="text" class="form-control" id="permissionKey" name="permission_key" 
                            placeholder="ex: menu.manage">
                        <small class="text-muted">Kosongkan jika semua user bisa akses</small>
                    </div>

                    <div class="form-group">
                        <label for="menuOrder" class="form-label">
                            Urutan <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="menuOrder" name="order" 
                            value="0" min="0" required>
                    </div>

                    {{-- Checkbox untuk menu utama dengan hidden input untuk handle unchecked state --}}
                    <div class="form-group" id="menuActiveGroup" style="display: none;">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check d-flex align-items-center justify-content-between">
                            <label class="form-check-label mb-0" for="menuIsActive" style="font-weight: 500;">
                                Menu Aktif
                            </label>
                            <input class="form-check-input" type="checkbox" id="menuIsActive" name="is_active" value="1" checked>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('tableModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    // ===== FUNGSI UNTUK SUBMENU =====

    /**
     * Buka modal untuk tambah submenu
     */
    function openSubmenuModal(parentId) {
        // Reset form
        document.getElementById('submenuForm').reset();
        
        // Set modal title dan tombol submit
        document.getElementById('submenuModalTitle').textContent = 'Tambah Sub Menu';
        document.getElementById('submenuSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan Sub Menu';
        
        // Set form action untuk create
        document.getElementById('submenuForm').action = '{{ route("settings.dynamic-menu-items.store") }}';
        document.getElementById('submenuMethodField').innerHTML = '';
        
        // Set parent ID
        document.getElementById('submenuParentId').value = parentId;
        document.getElementById('submenuId').value = '';
        
        // Hide checkbox aktif untuk mode tambah
        document.getElementById('submenuActiveGroup').style.display = 'none';
        
        // Set default values
        document.getElementById('submenuName').value = '';
        document.getElementById('submenuIcon').value = 'fas fa-link';
        document.getElementById('submenuLinkType').value = 'table';
        document.getElementById('submenuPermissionKey').value = '';
        document.getElementById('submenuOrder').value = '0';
        
        // Reset input link
        toggleSubmenuInputs();
        
        // Tampilkan modal
        document.getElementById('submenuModal').style.display = 'block';
    }

    /**
     * Buka modal untuk edit submenu
     */
    function openEditSubmenuModal(submenu) {
        // Set modal title dan tombol submit
        document.getElementById('submenuModalTitle').textContent = 'Edit Sub Menu';
        document.getElementById('submenuSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Sub Menu';
        
        // Set form action untuk update
        document.getElementById('submenuForm').action = `/settings/dynamic-menu-items/${submenu.id}`;
        document.getElementById('submenuMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        // Set ID fields
        document.getElementById('submenuId').value = submenu.id;
        document.getElementById('submenuParentId').value = submenu.parent_id;
        
        // Isi form fields
        document.getElementById('submenuName').value = submenu.name || '';
        document.getElementById('submenuIcon').value = submenu.icon || 'fas fa-link';
        document.getElementById('submenuLinkType').value = submenu.link_type || 'table';
        document.getElementById('submenuPermissionKey').value = submenu.permission_key || '';
        document.getElementById('submenuOrder').value = submenu.order || 0;
        
        // Tampilkan checkbox aktif untuk mode edit
        document.getElementById('submenuActiveGroup').style.display = 'block';
        document.getElementById('submenuIsActive').checked = !!submenu.is_active;
        
        // Toggle input link berdasarkan tipe
        toggleSubmenuInputs();
        
        // Set nilai link berdasarkan tipe
        setTimeout(() => {
            switch(submenu.link_type) {
                case 'table':
                    document.getElementById('submenuTableSelect').value = submenu.link_value || '';
                    break;
                case 'route':
                    document.getElementById('submenuRouteInput').value = submenu.link_value || '';
                    break;
                case 'url':
                    document.getElementById('submenuUrlInput').value = submenu.link_value || '';
                    break;
            }
        }, 100);
        
        // Tampilkan modal
        document.getElementById('submenuModal').style.display = 'block';
    }

    /**
     * Hapus submenu
     */
    function deleteSubmenu(submenuId) {
        if (confirm('Yakin ingin menghapus submenu ini?')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const token = csrfToken ? csrfToken.getAttribute('content') : '';
            
            fetch(`{{ route('settings.dynamic-menu-items.destroy', '') }}/${submenuId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus submenu');
            });
        }
    }

    /**
     * Toggle input fields berdasarkan tipe link submenu
     */
    function toggleSubmenuInputs() {
        const linkType = document.getElementById('submenuLinkType').value;
        const tableGroup = document.getElementById('submenuTableGroup');
        const routeGroup = document.getElementById('submenuRouteGroup');
        const urlGroup = document.getElementById('submenuUrlGroup');

        // Sembunyikan semua input group
        tableGroup.style.display = 'none';
        routeGroup.style.display = 'none';
        urlGroup.style.display = 'none';

        // Hapus atribut name untuk menghindari konflik
        document.getElementById('submenuTableSelect').removeAttribute('name');
        document.getElementById('submenuRouteInput').removeAttribute('name');
        document.getElementById('submenuUrlInput').removeAttribute('name');

        // Tampilkan group yang relevan dan set atribut name
        switch(linkType) {
            case 'table':
                tableGroup.style.display = 'block';
                document.getElementById('submenuTableSelect').setAttribute('name', 'link_value');
                break;
            case 'route':
                routeGroup.style.display = 'block';
                document.getElementById('submenuRouteInput').setAttribute('name', 'link_value');
                break;
            case 'url':
                urlGroup.style.display = 'block';
                document.getElementById('submenuUrlInput').setAttribute('name', 'link_value');
                break;
        }
    }

    // ===== FUNGSI UMUM =====

    /**
     * Tutup modal
     */
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        
        // Reset form validation states
        const form = modalId === 'submenuModal' ? 
            document.getElementById('submenuForm') : 
            document.getElementById('tableForm');
        
        if (form) {
            form.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                feedback.remove();
            });
        }
    }

    /**
     * Tutup modal ketika klik di luar modal
     */
    window.onclick = function(event) {
        const submenuModal = document.getElementById('submenuModal');
        const tableModal = document.getElementById('tableModal');
        
        if (event.target === submenuModal) {
            submenuModal.style.display = 'none';
        }
        if (event.target === tableModal) {
            tableModal.style.display = 'none';
        }
    }

    /**
     * Tutup modal dengan tombol Escape
     */
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('submenuModal');
            closeModal('tableModal');
        }
    });

    /**
     * Validasi form submenu sebelum submit
     */
    document.getElementById('submenuForm').addEventListener('submit', function(e) {
        const linkType = document.getElementById('submenuLinkType').value;
        let linkValue = '';
        
        // Ambil nilai link berdasarkan tipe yang dipilih
        switch(linkType) {
            case 'table':
                linkValue = document.getElementById('submenuTableSelect').value;
                break;
            case 'route':
                linkValue = document.getElementById('submenuRouteInput').value;
                break;
            case 'url':
                linkValue = document.getElementById('submenuUrlInput').value;
                break;
        }
        
        // Validasi bahwa nilai link harus diisi
        if (!linkValue.trim()) {
            e.preventDefault();
            alert('Silakan pilih/isi nilai untuk tipe link yang dipilih.');
            return false;
        }
        
        // Tampilkan loading state
        const submitBtn = document.getElementById('submenuSubmitBtn');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
        
        // Reset loading state jika ada error
        setTimeout(() => {
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
        }, 5000);
    });

    /**
     * Validasi form menu utama sebelum submit
     */
    document.getElementById('tableForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
        
        // Reset loading state jika ada error
        setTimeout(() => {
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
        }, 5000);
    });

    /**
     * Validasi real-time untuk nama menu
     */
    function setupInputValidation() {
        const menuNameInput = document.getElementById('menuName');
        const submenuNameInput = document.getElementById('submenuName');
        
        [menuNameInput, submenuNameInput].forEach(input => {
            if (input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 50) {
                        this.classList.add('is-invalid');
                        let feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            this.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = 'Nama menu maksimal 50 karakter';
                    } else {
                        this.classList.remove('is-invalid');
                        const feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (feedback) feedback.remove();
                    }
                });
            }
        });
    }

    // ===== INISIALISASI =====
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize submenu form
        toggleSubmenuInputs();
        
        // Setup input validation
        setupInputValidation();
        
        // Auto dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }
            }, 5000);
        });
    });


    
</script>
    
@endsection