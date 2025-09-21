@extends('dashboard.index')

@section('content')
    


    <div class="content-header">
        <h1 id="pageTitle">Kelola Menu</h1>
        <p id="pageDescription">Tambah, edit, atau hapus menu dropdown</p>
    </div>

    <div class="content-body" id="contentArea">
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Tambah Menu Baru
            </button>
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
                        <th>Sub Menu</th>
                        <th>Status</th>
                        <th>Action</th>
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
                                <span id="submenu-count-{{ $item->id }}">{{ $item->children_count ?? '0' }} sub menu</span>
                                <button class="btn btn-sm btn-success" onclick="openSubmenuModal({{ $item->id }})" style="margin-left: 10px;">
                                    <i class="fas fa-plus"></i> Tambah Sub
                                </button>

                                <button class="btn btn-sm btn-secondary" onclick="toggleSubmenu({{ $item->id }})" style="margin-left: 10px;">
                                    <i class="fas fa-chevron-down" id="chevron-{{ $item->id }}"></i> 
                                    <span id="toggle-text-{{ $item->id }}">Tampilkan</span>
                                </button>
                                
                            </td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='openEditModal(@json($item))'>
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
                        
                        {{-- Baris untuk menampilkan submenu --}}
                        <tr id="submenu-row-{{ $item->id }}" class="submenu-row" style="display: none; background-color: #f8f9fa;">
                            <td colspan="6" style="padding: 15px;">
                                <div id="submenu-content-{{ $item->id }}">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat submenu...
                                    </div>
                                </div>
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
                            placeholder="Contoh: Kelola Pengguna">
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
                                    placeholder="users, products, reports">
                                <small class="text-muted">Kosongkan jika semua user bisa akses</small>
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

                    {{-- Checkbox untuk Edit Mode --}}
                    <div class="form-group" id="submenuActiveGroup" style="display: none;">
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
                            placeholder="Contoh: Master Data">
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
        // ===== FUNGSI UNTUK MENU UTAMA =====
        
        /**
         * Buka modal untuk menambah menu baru
         */
        function openCreateModal() {
            document.getElementById('tableModalTitle').innerText = 'Tambah Menu Baru';
            document.getElementById('tableForm').action = "{{ route('settings.dynamic-menu-items.store') }}";
            document.getElementById('methodField').innerHTML = '';// reset method
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan Menu';

            // Reset semua field
            document.getElementById('menuId').value = '';
            document.getElementById('menuName').value = '';
            document.getElementById('menuIcon').value = '';
            document.getElementById('linkType').value = 'table';
            document.getElementById('linkValue').value = '';
            document.getElementById('permissionKey').value = '';
            document.getElementById('menuOrder').value = 0;

            document.getElementById('tableModal').style.display = 'block';
        }

        /**
         * Buka modal untuk edit menu
         */
        function openEditModal(menu) {
            document.getElementById('tableModalTitle').innerText = 'Edit Menu';
            document.getElementById('tableForm').action = "/settings/dynamic-menu-items/" + menu.id;
            document.getElementById('methodField').innerHTML = '@method("PUT")';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Menu';

            // Isi field dengan data menu
            document.getElementById('menuId').value = menu.id;
            document.getElementById('menuName').value = menu.name;
            document.getElementById('menuIcon').value = menu.icon;
            document.getElementById('linkType').value = menu.link_type;
            document.getElementById('linkValue').value = menu.link_value;
            document.getElementById('permissionKey').value = menu.permission_key ?? '';
            document.getElementById('menuOrder').value = menu.order;

            document.getElementById('tableModal').style.display = 'block';
        }

        // ===== FUNGSI UNTUK SUBMENU =====

        /**
         * Toggle tampilan submenu dengan animasi
         */
        function toggleSubmenu(parentId) {
        
            const submenuRow = document.getElementById('submenu-row-' + parentId);
            const chevron = document.getElementById('chevron-' + parentId);
            const toggleText = document.getElementById('toggle-text-' + parentId);
            
            if (submenuRow.style.display === 'none' || submenuRow.style.display === '') {
                // Tampilkan submenu
                submenuRow.style.display = 'table-row';
                chevron.className = 'fas fa-chevron-up';
                toggleText.textContent = 'Sembunyikan';
                
                // Load submenu data jika belum dimuat
                loadSubmenuData(parentId);
            } else {
                // Sembunyikan submenu
                submenuRow.style.display = 'none';
                chevron.className = 'fas fa-chevron-down';
                toggleText.textContent = 'Tampilkan';
            }
        }
        /**
         * Load data submenu via AJAX
         */
        function loadSubmenuData(parentId) {
            const contentDiv = document.getElementById('submenu-content-' + parentId);

            // Selalu reload submenu (jangan cache, karena bisa berubah)
            contentDiv.innerHTML = `<div class="text-center text-muted">
            <i class="fas fa-spinner fa-spin"></i> Memuat submenu...
            </div>`;

            fetch(`/settings/dynamic-menu-items/${parentId}/submenus`)
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.submenus)) {
                renderSubmenuList(contentDiv, data.submenus, parentId);
                } else {
                contentDiv.innerHTML = '<div class="text-center text-muted">Tidak ada submenu</div>';
                }
            })
            .catch(error => {
                console.error('Error loading submenu:', error);
                contentDiv.innerHTML = '<div class="text-center text-danger">Gagal memuat submenu</div>';
            });
        }

        /**
         * Render daftar submenu
         */
        function renderSubmenuList(container, submenus, parentId) {
            if (submenus.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">Belum ada submenu</div>';
                return;
            }

            let html = '<div class="submenu-list">';
            html += '<h5><i class="fas fa-list"></i> Daftar Sub Menu</h5>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Nama</th><th>Icon</th><th>Link</th><th>Urutan</th><th>Aksi</th></tr></thead>';
            html += '<tbody>';

            submenus.forEach(submenu => {
                html += `
                    <tr>
                        <td><strong>${submenu.name}</strong></td>
                        <td><i class="${submenu.icon}"></i> ${submenu.icon}</td>
                        <td><small>${submenu.link_type}: ${submenu.link_value}</small></td>
                        <td>${submenu.order}</td>
                        <td>
                            <button class="btn btn-xs btn-primary" onclick="openEditSubmenuModal(${JSON.stringify(submenu).replace(/"/g, '&quot;')})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-xs btn-danger" onclick="deleteSubmenu(${submenu.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div></div>';
            container.innerHTML = html;
        }

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
        
        // ✅ FIX: Set parent ID dan dynamic_menu_id dari parent item
        document.getElementById('submenuParentId').value = parentId;
        
        // ✅ TAMBAHAN: Ambil dynamic_menu_id dari parent item
        // Cara 1: Pass dynamic_menu_id dari button
        // Cara 2: Ambil dari data attribute
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
            document.getElementById('submenuIsActive').checked = submenu.is_active || false;
            
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
                fetch(`{{ route('settings.dynamic-menu-items.destroy', '') }}/${submenuId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
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
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
        });

        // ===== INISIALISASI =====
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize submenu form
            toggleSubmenuInputs();
        });
    </script>
    
@endsection