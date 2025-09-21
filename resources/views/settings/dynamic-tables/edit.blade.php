@extends('layouts.app')

@section('title', 'Edit Tabel - ' . $dynamicTable->name)

@section('content')
<div class="container-fluid">
    <div class="roles-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Tabel: {{ $dynamicTable->name }}</h1>
            <p class="mb-0 text-muted">Ubah pengaturan tabel dinamis</p>
        </div>
        <a href="{{ route('settings.dynamic-tables.index') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    {{-- <h6 class="m-0 font-weight-bold text-primary">Form Edit Tabel</h6> --}}
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.dynamic-tables.update', $dynamicTable) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Nama Tabel <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $dynamicTable->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3">{{ old('description', $dynamicTable->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <!-- hidden untuk memastikan selalu ada nilai -->
                                <input type="hidden" name="is_active" value="0">

                                <input class="form-check-input" type="checkbox" id="is_active" 
                                    name="is_active" value="1" 
                                    {{ old('is_active', $dynamicTable->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Tabel Aktif
                                </label>
                            </div>
                        </div>


                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Tabel
                            </button>
                            {{-- <a href="{{ route('settings.dynamic-tables.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Informasi Tabel</h6>
                </div>
                <div class="card-body">
                    <p><strong>Database Table:</strong><br><code>{{ $dynamicTable->table_name }}</code></p>
                    <p><strong>Kolom:</strong> {{ $dynamicTable->columns->count() }} kolom</p>
                    <p><strong>Digunakan di Menu:</strong> {{ $dynamicTable->menuItems->count() }} menu</p>
                    <p><strong>Dibuat:</strong> {{ $dynamicTable->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Update Terakhir:</strong> {{ $dynamicTable->updated_at->format('d/m/Y H:i') }}</p>
                    
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="{{ route('settings.dynamic-table-columns', $dynamicTable) }}" 
                            class="btn btn-outline-primary">
                            <i class="fas fa-columns"></i> Kelola Kolom
                        </a>
                        @if($dynamicTable->columns->count() > 0)
                            <a href="{{ route('dashboard.table', $dynamicTable->id) }}" 
                                class="btn btn-outline-info" target="_blank">
                                <i class="fas fa-eye"></i> Lihat Data
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection