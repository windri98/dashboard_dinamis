@extends('layouts.app')

@section('content')
    <section class="#" id="create-dynamic-menu">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert success-alert">
                    {{ session('success') }}
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Tambah Menu Dinamis</h4>
                        </div>
                        <div class="container-create">
                            <form method="POST" action="{{ route('settings.dynamic-menus.store') }}" class="from-container">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="from-group">
                                            <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"id="name" name="name" value="{{ old('name') }}" required>
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
                                                    placeholder="analytics, reports, user_management">
                                            <small class="text-muted">Key untuk kontrol akses (opsional). Kosongkan jika semua user bisa akses.</small>
                                            @error('permission_key')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="{{ route('settings.dynamic-menus.store') }}">
                                    @csrf
                                    <div class="d-flex gap-2">
                                        <div class="button-group">
                                            <button class="primary-button" type="submit">
                                                <i class="fas fa-save"></i> Simpan Menu
                                            </button>
                                        </div>
                                    </div>
                                </form>
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
                                <li>
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    Setelah membuat menu, jangan lupa tambah sub-menu
                                </li>
                            </ul>
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
        
        iconInput.addEventListener('input', function() {
            const iconClass = this.value || 'fas fa-circle';
            iconPreview.className = iconClass + ' fa-3x text-primary';
        });
    });
</script>
@endsection