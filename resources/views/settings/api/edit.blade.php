@extends('layouts.app')

@section('title', 'Edit API Endpoint')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit API Endpoint
                    </h3>
                </div>
                <form action="{{ route('settings.api.update', $apiEndpoint) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama API <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $apiEndpoint->name) }}" required onkeyup="generateSlug()">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $apiEndpoint->slug) }}" required>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">URL: <strong id="slug-preview">{{ url('/settings/api/' . $apiEndpoint->slug) }}</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="method" class="form-label">HTTP Method <span class="text-danger">*</span></label>
                                    <select name="method" id="method" class="form-select @error('method') is-invalid @enderror" required>
                                        <option value="">-- Pilih Method --</option>
                                        <option value="GET" {{ old('method', $apiEndpoint->method) == 'GET' ? 'selected' : '' }}>GET</option>
                                        <option value="POST" {{ old('method', $apiEndpoint->method) == 'POST' ? 'selected' : '' }}>POST</option>
                                        <option value="PUT" {{ old('method', $apiEndpoint->method) == 'PUT' ? 'selected' : '' }}>PUT</option>
                                        <option value="DELETE" {{ old('method', $apiEndpoint->method) == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                    </select>
                                    @error('method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endpoint" class="form-label">Endpoint <span class="text-danger">*</span></label>
                                    <input type="text" name="endpoint" id="endpoint" class="form-control @error('endpoint') is-invalid @enderror" 
                                           value="{{ old('endpoint', $apiEndpoint->endpoint) }}" required>
                                    @error('endpoint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="table_name" class="form-label">Target Table <span class="text-danger">*</span></label>
                                    <select name="table_name" id="table_name" class="form-select @error('table_name') is-invalid @enderror" required>
                                        <option value="">-- Pilih Tabel --</option>
                                        @foreach($dynamicTables as $table)
                                            <option value="{{ $table->table_name }}" 
                                                    {{ old('table_name', $apiEndpoint->table_name) == $table->table_name ? 'selected' : '' }}>
                                                {{ $table->name }} ({{ $table->table_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('table_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="permission_id" class="form-label">Permission (Opsional)</label>
                            <select name="permission_id" id="permission_id" class="form-select @error('permission_id') is-invalid @enderror">
                                <option value="">-- Tanpa Permission Khusus --</option>
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->id }}" 
                                            {{ old('permission_id', $apiEndpoint->permission_id) == $permission->id ? 'selected' : '' }}>
                                        {{ $permission->menu->name ?? 'N/A' }}.{{ $permission->action->slug ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('permission_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $apiEndpoint->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h5><i class="fas fa-shield-alt me-2"></i>Pengaturan Keamanan</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                           {{ old('is_active', $apiEndpoint->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        API Aktif
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="use_ip_restriction" value="1" id="use_ip_restriction"
                                           {{ old('use_ip_restriction', $apiEndpoint->use_ip_restriction) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="use_ip_restriction">
                                        Gunakan IP Restriction
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="ip_section" style="{{ old('use_ip_restriction', $apiEndpoint->use_ip_restriction) ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ip_whitelist" class="form-label">IP Whitelist</label>
                                    <textarea name="ip_whitelist" id="ip_whitelist" class="form-control @error('ip_whitelist') is-invalid @enderror" 
                                              rows="3" placeholder="192.168.1.1&#10;10.0.0.0/8">{{ old('ip_whitelist', $apiEndpoint->ip_whitelist ? implode("\n", $apiEndpoint->ip_whitelist) : '') }}</textarea>
                                    @error('ip_whitelist')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Satu IP/CIDR per baris. Jika kosong, semua IP diizinkan</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ip_blacklist" class="form-label">IP Blacklist</label>
                                    <textarea name="ip_blacklist" id="ip_blacklist" class="form-control @error('ip_blacklist') is-invalid @enderror" 
                                              rows="3" placeholder="192.168.1.100">{{ old('ip_blacklist', $apiEndpoint->ip_blacklist ? implode("\n", $apiEndpoint->ip_blacklist) : '') }}</textarea>
                                    @error('ip_blacklist')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">IP yang diblokir</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="use_rate_limit" value="1" id="use_rate_limit"
                                   {{ old('use_rate_limit', $apiEndpoint->use_rate_limit) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_rate_limit">
                                Gunakan Rate Limiting
                            </label>
                        </div>

                        <div class="row" id="rate_section" style="{{ old('use_rate_limit', $apiEndpoint->use_rate_limit) ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rate_limit_max" class="form-label">Max Requests</label>
                                    <input type="number" name="rate_limit_max" id="rate_limit_max" 
                                           class="form-control @error('rate_limit_max') is-invalid @enderror" 
                                           value="{{ old('rate_limit_max', $apiEndpoint->rate_limit_max) }}" min="1">
                                    @error('rate_limit_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rate_limit_period" class="form-label">Period (detik)</label>
                                    <input type="number" name="rate_limit_period" id="rate_limit_period" 
                                           class="form-control @error('rate_limit_period') is-invalid @enderror" 
                                           value="{{ old('rate_limit_period', $apiEndpoint->rate_limit_period) }}" min="1">
                                    @error('rate_limit_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update API Endpoint
                        </button>
                        <a href="{{ route('settings.api.index') }}" class="back-button">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-generate slug from name
    function generateSlug() {
        const name = document.getElementById('name').value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '') // hapus karakter special
            .replace(/\s+/g, '-')        // ganti spasi dengan dash
            .replace(/-+/g, '-')         // replace multiple dash dengan single
            .trim('-');                  // hapus dash di awal/akhir
        
        document.getElementById('slug').value = slug;
        updateSlugPreview(slug);
    }

    function updateSlugPreview(slug) {
        const preview = document.getElementById('slug-preview');
        preview.textContent = '{{ url("/settings/api/") }}/' + (slug || '');
    }

    // Update slug preview when slug field changes manually
    document.getElementById('slug').addEventListener('input', function() {
        updateSlugPreview(this.value);
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle IP section
    document.getElementById('use_ip_restriction').addEventListener('change', function() {
        document.getElementById('ip_section').style.display = this.checked ? 'block' : 'none';
    });

    // Toggle Rate section
    document.getElementById('use_rate_limit').addEventListener('change', function() {
        document.getElementById('rate_section').style.display = this.checked ? 'block' : 'none';
    });
});
</script>
@endsection