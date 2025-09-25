@extends('layouts.app')

@section('content')
    <section class="#" id="create-dynamic-menu">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert success-alert">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="roles-header">
                <h1>Create Menu</h1>
                <a href="/settings/dynamic-menus" class="back-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Tambah Menu Dinamis</h4>
                        </div>
                        <div class="container-create">
                            <form method="POST" action="{{ route('settings.dynamic-menus.store') }}" class="form-container">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="icon" class="form-label">Icon (Font Awesome) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                                    id="icon" name="icon" value="{{ old('icon', 'fas fa-circle') }}" required 
                                                    placeholder="fas fa-chart-bar">
                                            <small class="text-muted">Contoh: fas fa-chart-bar, fas fa-users, fas fa-cog</small>
                                            @error('icon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                            <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                                <option value="">Pilih Kategori</option>
                                                <option value="main" {{ old('category') === 'main' ? 'selected' : '' }}>Menu Utama</option>
                                                <option value="settings" {{ old('category') === 'settings' ? 'selected' : '' }}>Pengaturan</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="order" class="form-label">Urutan <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                                    id="order" name="order" value="{{ old('order', 0) }}" min="0" required>
                                            <small class="text-muted">Urutan tampil di sidebar (0 = pertama)</small>
                                            @error('order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="permission_key" class="form-label">Permission Key</label>
                                            <input type="text" class="form-control @error('permission_key') is-invalid @enderror" 
                                                id="permission_key" name="permission_key" value="{{ old('permission_key') }}" 
                                                placeholder="analytics, reports, user_management" readonly>
                                            <small class="text-muted">Key untuk kontrol akses (opsional). Tidak bisa diedit langsung.</small>
                                            @error('permission_key')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                                
                                <div class="button-group">
                                    <button class="primary-button" type="submit">
                                        <i class="fas fa-save"></i> Simpan Menu
                                    </button>
                                    <a href="{{ route('settings.dynamic-menus.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-info">Preview Icon</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="icon-preview mb-3">
                                <i id="icon-preview" class="fas fa-circle fa-3x text-primary"></i>
                            </div>
                            <p class="text-muted">Preview icon akan muncul di sini</p>
                            <div class="mt-2">
                                <small class="text-muted" id="icon-class-display">fas fa-circle</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    Pilih nama yang jelas dan mudah dipahami
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    Gunakan icon yang relevan dengan konten menu
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    Permission key digunakan untuk kontrol akses
                                </li>
                                <li>
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    Setelah membuat menu, jangan lupa tambah sub-menu
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Contoh Icon Populer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4 mb-3">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                    <small class="d-block mt-1">fa-users</small>
                                </div>
                                <div class="col-4 mb-3">
                                    <i class="fas fa-chart-bar fa-2x text-success"></i>
                                    <small class="d-block mt-1">fa-chart-bar</small>
                                </div>
                                <div class="col-4 mb-3">
                                    <i class="fas fa-cog fa-2x text-secondary"></i>
                                    <small class="d-block mt-1">fa-cog</small>
                                </div>
                                <div class="col-4 mb-3">
                                    <i class="fas fa-box fa-2x text-info"></i>
                                    <small class="d-block mt-1">fa-box</small>
                                </div>
                                <div class="col-4 mb-3">
                                    <i class="fas fa-file-alt fa-2x text-warning"></i>
                                    <small class="d-block mt-1">fa-file-alt</small>
                                </div>
                                <div class="col-4 mb-3">
                                    <i class="fas fa-shield-alt fa-2x text-danger"></i>
                                    <small class="d-block mt-1">fa-shield-alt</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iconInput = document.getElementById('icon');
            const iconPreview = document.getElementById('icon-preview');
            const iconClassDisplay = document.getElementById('icon-class-display');
            
            // Function to update icon preview
            function updateIconPreview() {
                const iconClass = iconInput.value.trim() || 'fas fa-circle';
                iconPreview.className = iconClass + ' fa-3x text-primary';
                iconClassDisplay.textContent = iconClass;
            }
            
            // Update preview on input
            iconInput.addEventListener('input', updateIconPreview);
            
            // Initial preview update
            updateIconPreview();
            
            // Click handler for example icons
            document.querySelectorAll('.card-body .col-4').forEach(function(iconExample) {
                iconExample.addEventListener('click', function() {
                    const iconClass = this.querySelector('small').textContent;
                    iconInput.value = 'fas ' + iconClass;
                    updateIconPreview();
                });
            });
            
            // Generate permission key from name (optional helper)
            const nameInput = document.getElementById('name');
            const permissionKeyInput = document.getElementById('permission_key');

            nameInput.addEventListener('blur', function() {
                if (!permissionKeyInput.value && this.value) {
                    const permissionKey = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s]/g, '')
                        .replace(/\s+/g, '_');
                    permissionKeyInput.value = permissionKey;
                }
            });

            nameInput.addEventListener('input', function() {
                if (!this.value) {
                    permissionKeyInput.value = '';
                }
            });
        });
    </script>
@endsection