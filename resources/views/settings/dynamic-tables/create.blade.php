@extends('layouts.app')

@section('title', 'Tambah Tabel Dinamis')

@section('content')

<div class="container-fluid">
    <div class="roles-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tambah Tabel Dinamis</h1>
            <p class="mb-0 text-muted">Buat tabel baru untuk menyimpan data</p>
        </div>
        <a href="{{ route('settings.dynamic-tables.index') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tabel Baru</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.dynamic-tables.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name" class="form-label">
                                Nama Tabel <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required
                                    placeholder="Contoh: Data Pengguna">
                            <small class="text-muted">Nama tabel akan otomatis diubah menjadi format database</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3" 
                                    placeholder="Deskripsi singkat tentang kegunaan tabel ini">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Tabel
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
                    <h6 class="m-0 font-weight-bold text-info">Preview Nama Tabel</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Database Table:</strong></p>
                    <code id="table-preview">dyn_nama_tabel</code>
                    <hr>
                    <small class="text-muted">
                        Nama tabel di database akan menggunakan prefix "dyn_" 
                        dan format snake_case
                    </small>
                </div>
            </div>
            
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Langkah Selanjutnya</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Buat tabel ini</li>
                        <li class="mb-2">Tambahkan kolom-kolom yang dibutuhkan</li>
                        <li class="mb-2">Hubungkan dengan menu dinamis</li>
                        <li>Mulai input data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const tablePreview = document.getElementById('table-preview');
    
    nameInput.addEventListener('input', function() {
        const name = this.value || 'nama_tabel';
        const tableName = 'dyn_' + name.toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        tablePreview.textContent = tableName;
    });
});
</script>

@endsection