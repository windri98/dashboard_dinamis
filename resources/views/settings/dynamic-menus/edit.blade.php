@extends('layouts.app')

@section('content')
    <section class="#" id="update-dynamic-menu">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert success-alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="roles-header">
                <h1>Edit Menu</h1>
                <a href="/settings/dynamic-menus" class="back-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" 
                            stroke="currentColor" stroke-width="2" 
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Edit Menu Dinamis</h4>
                        </div>
                        <div class="container-create">
                            <form method="POST" action="{{ route('settings.dynamic-menus.update', $dynamicMenu->id) }}" class="from-container">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="from-group">
                                            <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" 
                                                value="{{ old('name', $dynamicMenu->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="icon" class="form-label">Icon (Font Awesome) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                                id="icon" name="icon" 
                                                value="{{ old('icon', $dynamicMenu->icon) }}" required>
                                            <small class="text-muted">Contoh: fas fa-chart-bar</small>
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
                                                <option value="main" {{ old('category', $dynamicMenu->category) === 'main' ? 'selected' : '' }}>Menu Utama</option>
                                                <option value="settings" {{ old('category', $dynamicMenu->category) === 'settings' ? 'selected' : '' }}>Pengaturan</option>
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
                                                id="order" name="order" 
                                                value="{{ old('order', $dynamicMenu->order) }}" min="0" required>
                                            <small class="text-muted">Urutan tampil di sidebar</small>
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
                                                    id="permission_key" name="permission_key" 
                                                    value="{{ old('permission_key', $dynamicMenu->permission_key) }}">
                                            <small class="text-muted">Key opsional untuk kontrol akses</small>
                                            @error('permission_key')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: center; margin-top: 1rem;">
                                    <div style="display: flex; align-items: center;">
                                        <label for="is_active" style="margin: 0 8px 0 0;">Active</label>
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" id="is_active" name="is_active" value="1"
                                            {{ old('is_active', $dynamicMenu->is_active) ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <div class="button-group">
                                        <button class="primary-button" type="submit">
                                            <i class="fas fa-save"></i> Update Menu
                                        </button>
                                    </div>
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
                                <i id="icon-preview" class="{{ $dynamicMenu->icon }} fa-3x text-primary"></i>
                            </div>
                            <p class="text-muted">{{ $dynamicMenu->icon }}</p>
                        </div>
                    </div>
                    
                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-warning">Informasi</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Sub Menu:</strong> {{ $dynamicMenu->items->count() }} item</p>
                            <p><strong>Dibuat:</strong> {{ $dynamicMenu->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Terakhir Update:</strong> {{ $dynamicMenu->updated_at->format('d/m/Y H:i') }}</p>
                            
                            <a href="{{ route('settings.dynamic-menu-items', $dynamicMenu) }}" 
                                class="btn btn-sm btn-info w-100">
                                <i class="fas fa-list"></i> Kelola Sub Menu
                            </a>
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