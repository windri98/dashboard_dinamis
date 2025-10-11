@extends('layouts.app')

@section('title', $dynamicTable->name)

@section('content')

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="roles-header">
                <div>
                    <h1 class="h3 mb-2">{{ $dynamicTable->name }}</h1>
                    @if($dynamicTable->description)
                        <p class="text-muted">{{ $dynamicTable->description }}</p>
                    @endif
                </div>
                <div class="btn" style="display: flex; justify-content: flex-end; gap: 10px;">
                    @if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('create', $permissions['dynamic_table'] ?? [])))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    @endif
                    <a href="{{ route('dashboard.index') }}" class="back-button">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search & Filter Section -->
    <form method="GET" action="{{ route('dashboard.table', $dynamicTable->id) }}"                       class="search-form-menu" id="searchForm">
        <input type="text" id="searchInput" name="search" placeholder="Search..." value="{{ request('search') }}">
        <input type="date" name="date_from" id="dateFromInput" value="{{ request('date_from') }}" placeholder="Dari tanggal">
        <span class="align-self-center">s/d</span>
        <input type="date" name="date_to" id="dateToInput" value="{{ request('date_to') }}" placeholder="Sampai tanggal">
        <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
    </form>
    
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <div class="roles-table">
                    <!-- Filter per_page dengan preserve parameters -->
                    <form method="GET" action="{{ route('dashboard.table', $dynamicTable->id) }}">
                        <!-- Preserve search and sort parameters -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                        @if(request('direction'))
                            <input type="hidden" name="direction" value="{{ request('direction') }}">
                        @endif
                        
                        @php
                            $perPageOptions = [10, 15, 20, 50, 100, 200, 500, 1000];
                            $currentPerPage = request('per_page', 15); // default 15
                        @endphp
                        <select name="per_page" onchange="this.form.submit()" class="per-page-select">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $currentPerPage == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <div class="table-responsive">
                        <table class="roles-table">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    @foreach($dynamicTable->activeColumns as $column)
                                        <th>{{ $column->name }}</th>
                                    @endforeach
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($data->count() > 0)
                                    {{-- Data asli --}}
                                    @foreach($data as $index => $row)
                                        <tr>
                                            <td>{{ $data->firstItem() + $index }}</td>
                                            @foreach($dynamicTable->activeColumns as $column)
                                                <td>
                                                    @if($column->type == 'boolean')
                                                        <span class="badge bg-{{ $row->{$column->column_name} ? 'success' : 'danger' }}">
                                                            {{ $row->{$column->column_name} ? 'Ya' : 'Tidak' }}
                                                        </span>
                                                    @elseif($column->type == 'date')
                                                        {{ $row->{$column->column_name} ? \Carbon\Carbon::parse($row->{$column->column_name})->format('d/m/Y') : '-' }}
                                                    @elseif($column->type == 'datetime')
                                                        {{ $row->{$column->column_name} ? \Carbon\Carbon::parse($row->{$column->column_name})->format('d/m/Y H:i') : '-' }}
                                                    @elseif($column->type == 'image')
                                                        @if($row->{$column->column_name} && !str_contains($row->{$column->column_name}, 'tmp'))
                                                            <div class="image-preview-container">
                                                                @php
                                                                    $imagePath = str_starts_with($row->{$column->column_name}, 'uploads/') 
                                                                        ? $row->{$column->column_name} 
                                                                        : 'uploads/' . $dynamicTable->table_name . '/' . $column->column_name . '/original/' . basename($row->{$column->column_name});
                                                                @endphp
                                                                <img src="{{ asset('storage/' . $imagePath) }}" 
                                                                     alt="Image" 
                                                                     class="table-image-preview"
                                                                     data-bs-toggle="modal" 
                                                                     data-bs-target="#imageModal"
                                                                     onclick="showImageModal('{{ asset('storage/' . $imagePath) }}', '{{ $column->name }}')"
                                                                     style="cursor: pointer;">
                                                                <div class="image-overlay">
                                                                    <i class="fas fa-expand"></i>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">
                                                                <i class="fas fa-image"></i> 
                                                                @if(str_contains($row->{$column->column_name} ?? '', 'tmp'))
                                                                    Invalid file
                                                                @else
                                                                    No image
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @elseif($column->type == 'file')
                                                        @if($row->{$column->column_name} && !str_contains($row->{$column->column_name}, 'tmp'))
                                                            <div class="file-preview-container">
                                                                @php
                                                                    $filePath = str_starts_with($row->{$column->column_name}, 'uploads/') 
                                                                        ? $row->{$column->column_name} 
                                                                        : 'uploads/' . $dynamicTable->table_name . '/' . $column->column_name . '/' . basename($row->{$column->column_name});
                                                                    $fileName = basename($row->{$column->column_name});
                                                                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                                                @endphp
                                                                <a href="{{ asset('storage/' . $filePath) }}" 
                                                                   target="_blank" 
                                                                   class="file-link">
                                                                    <i class="fas fa-{{ $extension === 'pdf' ? 'file-pdf' : ($extension === 'doc' || $extension === 'docx' ? 'file-word' : ($extension === 'xls' || $extension === 'xlsx' ? 'file-excel' : 'file-alt')) }}"></i>
                                                                    <span class="file-name">{{ $fileName }}</span>
                                                                </a>
                                                                <small class="file-size text-muted d-block">
                                                                    {{ strtoupper($extension) }}
                                                                </small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">
                                                                <i class="fas fa-file"></i> 
                                                                @if(str_contains($row->{$column->column_name} ?? '', 'tmp'))
                                                                    Invalid file
                                                                @else
                                                                    No file
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @else
                                                        {{ $row->{$column->column_name} ?? '-' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="action-cell">
                                                <div class="action-buttons d-flex gap-2">
                                                    @if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('edit', $permissions['dynamic_table'] ?? [])))
                                                        {{-- <button type="button" class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal" data-bs-target="#editDataModal"
                                                                onclick='loadEditData({{ $row->id }}, @json($row))'>
                                                            <i class="fas fa-edit"></i>
                                                        </button> --}}
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editDataModal"
                                                                onclick="loadEditData({{ $row->id }}, {{ json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                    @if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('delete', $permissions['dynamic_table'] ?? [])))
                                                        <form method="POST" action="{{ route('dashboard.table.destroy', [$dynamicTable->id, $row->id]) }}"
                                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                @else
                                    {{-- Sample data dinamis ketika tidak ada data --}}
                                    <tr class="table-warning bg-opacity-25">
                                        <td>1</td>
                                        @foreach($dynamicTable->activeColumns as $column)
                                            <td>
                                                @if($column->type == 'boolean')
                                                    <span class="badge bg-success">Ya</span>
                                                @elseif($column->type == 'date')
                                                    {{ now()->format('d/m/Y') }}
                                                @elseif($column->type == 'datetime')
                                                    {{ now()->format('d/m/Y H:i') }}
                                                @elseif($column->type == 'integer')
                                                    100
                                                @elseif($column->type == 'decimal')
                                                    150.75
                                                @elseif($column->type == 'image')
                                                    <div class="sample-image-preview">
                                                        <div class="sample-image-placeholder">
                                                            <i class="fas fa-image fa-2x text-muted"></i>
                                                            <small class="text-muted d-block">Sample Image</small>
                                                        </div>
                                                    </div>
                                                @elseif($column->type == 'file')
                                                    <div class="sample-file-preview">
                                                        <i class="fas fa-file-pdf text-danger"></i>
                                                        <span class="file-name text-muted">sample_document.pdf</span>
                                                        <small class="file-size text-muted d-block">PDF</small>
                                                    </div>
                                                @elseif($column->type == 'select' || $column->type == 'radio' || $column->type == 'checkbox')
                                                    @if(isset($column->options['values']) && count($column->options['values']) > 0)
                                                        {{ $column->options['values'][0] }}
                                                    @else
                                                        Sample Option
                                                    @endif
                                                @elseif($column->type == 'text')
                                                    Sample {{ $column->name }} dengan deskripsi panjang...
                                                @else
                                                    Sample {{ $column->name }}
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="action-cell">
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-primary" disabled>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="{{ count($dynamicTable->activeColumns) + 2 }}" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-info-circle fa-2x text-warning mb-2"></i>
                                                <h6 class="text-muted mb-1">Contoh tampilan data untuk tabel "{{ $dynamicTable->name }}"</h6>
                                                <p class="text-muted small mb-2">Data di atas hanya contoh. Klik tombol "Tambah Data" untuk menambahkan data sesungguhnya.</p>
                                                @if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('create', $permissions['dynamic_table'] ?? [])))
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDataModal">
                                                        <i class="fas fa-plus"></i> Tambah Data Pertama
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- paginate --}}
                    @if($data->count() > 0)
                        <!-- Pagination hanya tampil jika ada data asli -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} 
                                dari {{ $data->total() }} data
                            </div>
                            <div>
                                <div class="pagination">
                                    {{-- Tombol Previous --}}
                                    <a id="previousButton" href="{{ $data->currentPage() > 1 ? request()->fullUrlWithQuery(['page' => $data->currentPage() - 1, 'per_page' => request('per_page', $data->perPage())]) : '#' }}">
                                        <button class="see" {{ $data->currentPage() <= 1 ? 'disabled' : '' }}>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15 6L9 12L15 18" stroke="#33363F" stroke-width="2"/>
                                            </svg>
                                            <span>Previous</span>
                                        </button>
                                    </a>

                                    <div class="pagincenter">
                                        @php
                                            $currentPage = $data->currentPage();
                                            $lastPage = $data->lastPage();
                                            $pageLimit = 5;

                                            $start = max(1, $currentPage - 2);
                                            $end = min($lastPage, $currentPage + 2);

                                            if ($start > 1) {
                                                echo '<a href="' . request()->fullUrlWithQuery(['page' => 1, 'per_page' => request('per_page', $data->perPage())]) . '">1</a>';
                                                if ($start > 2) {
                                                    echo '<span>...</span>';
                                                }
                                            }

                                            for ($i = $start; $i <= $end; $i++) {
                                                echo '<a href="' . request()->fullUrlWithQuery(['page' => $i, 'per_page' => request('per_page', $data->perPage())]) . '" class="' . ($currentPage == $i ? 'active' : '') . '">' . $i . '</a>';
                                            }

                                            if ($end < $lastPage) {
                                                if ($end < $lastPage - 1) {
                                                    echo '<span>...</span>';
                                                }
                                                echo '<a href="' . request()->fullUrlWithQuery(['page' => $lastPage, 'per_page' => request('per_page', $data->perPage())]) . '">' . $lastPage . '</a>';
                                            }
                                        @endphp
                                    </div>

                                    {{-- Tombol Next --}}
                                    <a id="nextButton" href="{{ $data->currentPage() < $data->lastPage() ? request()->fullUrlWithQuery(['page' => $data->currentPage() + 1, 'per_page' => request('per_page', $data->perPage())]) : '#' }}">
                                        <button class="see" {{ $data->currentPage() >= $data->lastPage() ? 'disabled' : '' }}>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9 6L15 12L9 18" stroke="#33363F" stroke-width="2"/>
                                            </svg>
                                            <span>Next</span>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted mt-3">
                            <small>Tidak ada pagination karena belum ada data asli</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Data Modal -->
@if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('create', $permissions['dynamic_table'] ?? [])))
<div class="modal fade" id="addDataModal" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.table.store', $dynamicTable->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data - {{ $dynamicTable->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach($dynamicTable->activeColumns as $column)
                            {{-- Skip system columns --}}
                            @if(!in_array($column->column_name, ['id', 'created_at', 'updated_at']))
                            <div class="col-md-{{ $column->type == 'text' ? '12' : '6' }} mb-3">
                                <label for="add_{{ $column->column_name }}" class="form-label">
                                    {{ $column->name }}
                                    @if($column->is_required)
                                    <span class="text-danger">*</span>
                                    @endif
                                </label>
                                    
                                    @if($column->type == 'boolean')
                                        <select name="{{ $column->column_name }}" 
                                                id="add_{{ $column->column_name }}" 
                                                class="form-control  {{ $column->is_required ? 'required' : '' }}"
                                                {{ $column->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih --</option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                        
                                    @elseif($column->type == 'enum')
                                    <select name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control  {{ $column->is_required ? 'required' : '' }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                        <option value="">-- Pilih {{ $column->name }} --</option>
                                        @if(isset($column->options['values']))
                                            @foreach($column->options['values'] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    
                                    @elseif($column->type == 'text')
                                        <textarea name="{{ $column->column_name }}" 
                                                id="add_{{ $column->column_name }}" 
                                                class="form-control {{ $column->is_required ? 'required' : '' }}" 
                                                rows="4" 
                                                placeholder="Masukkan {{ strtolower($column->name) }}..."
                                                {{ $column->is_required ? 'required' : '' }}>{{ old($column->column_name) }}</textarea>
                                                
                                    @elseif($column->type == 'integer')
                                        <input type="number" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            placeholder="Masukkan {{ strtolower($column->name) }}..."
                                            value="{{ old($column->column_name) }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                            
                                    @elseif($column->type == 'decimal')
                                        <input type="number" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            step="0.01"
                                            placeholder="Masukkan {{ strtolower($column->name) }}..."
                                            value="{{ old($column->column_name) }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                            
                                    @elseif($column->type == 'date')
                                        <input type="date" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            value="{{ old($column->column_name) }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                            
                                    @elseif($column->type == 'time')
                                        <input type="time" 
                                            name="{{ $column->column_name }}" 
                                            id="edit_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                            
                                    @elseif($column->type == 'datetime')
                                        <input type="datetime-local" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            value="{{ old($column->column_name) }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                    
                                    @elseif($column->type == 'file')
                                        <input type="file" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            accept=".pdf,.doc,.docx,.txt,.xlsx,.xls"
                                            {{ $column->is_required ? 'required' : '' }}>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Supported formats: PDF, DOC, DOCX, TXT, Excel (Max: 2MB)
                                        </small>
                                    
                                    @elseif($column->type == 'image')
                                        <input type="file" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            accept="image/*"
                                            {{ $column->is_required ? 'required' : '' }}>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Supported formats: JPG, PNG, GIF (Max: 2MB). Thumbnail will be auto-generated.
                                        </small>
                                            
                                    @else
                                        <input type="text" 
                                            name="{{ $column->column_name }}" 
                                            id="add_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            placeholder="Masukkan {{ strtolower($column->name) }}..."
                                            value="{{ old($column->column_name) }}"
                                            @if(isset($column->options['max_length']))
                                                maxlength="{{ $column->options['max_length'] }}"
                                            @endif
                                            {{ $column->is_required ? 'required' : '' }}>
                                    @endif
                                    
                                    {{-- Display validation errors --}}
                                    @error($column->column_name)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    
                                    @if(isset($column->options['help_text']))
                                        <div class="form-text">{{ $column->options['help_text'] }}</div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Data Modal -->
@if($isSuperAdmin || (isset($permissions['dynamic_table']) && in_array('edit', $permissions['dynamic_table'] ?? [])))
<div class="modal fade" id="editDataModal" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editForm" action="{{ route('dashboard.table.update', [$dynamicTable->id, 'PLACEHOLDER']) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data - {{ $dynamicTable->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach($dynamicTable->activeColumns as $column)
                            {{-- Skip system columns --}}
                            @if(!in_array($column->column_name, ['id', 'created_at', 'updated_at']))
                            <div class="col-md-{{ $column->type == 'text' ? '12' : '6' }} mb-3">
                                <label for="edit_{{ $column->column_name }}" class="form-label">
                                    {{ $column->name }}
                                    @if($column->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                
                                @if($column->type == 'boolean')
                                    <select name="{{ $column->column_name }}" 
                                            id="edit_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                        <option value="">-- Pilih --</option>
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                    
                                @elseif($column->type == 'enum')
                                    <select name="{{ $column->column_name }}" 
                                            id="edit_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}"
                                            {{ $column->is_required ? 'required' : '' }}>
                                        <option value="">-- Pilih {{ $column->name }} --</option>
                                        @if(isset($column->options['values']))
                                            @foreach($column->options['values'] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    
                                @elseif($column->type == 'text')
                                    <textarea name="{{ $column->column_name }}" 
                                            id="edit_{{ $column->column_name }}" 
                                            class="form-control {{ $column->is_required ? 'required' : '' }}" 
                                            rows="4" 
                                            placeholder="Masukkan {{ strtolower($column->name) }}..."
                                            {{ $column->is_required ? 'required' : '' }}></textarea>
                                            
                                @elseif($column->type == 'integer')
                                    <input type="number" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        placeholder="Masukkan {{ strtolower($column->name) }}..."
                                        {{ $column->is_required ? 'required' : '' }}>
                                        
                                @elseif($column->type == 'decimal')
                                    <input type="number" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        step="0.01"
                                        placeholder="Masukkan {{ strtolower($column->name) }}..."
                                        {{ $column->is_required ? 'required' : '' }}>
                                        
                                @elseif($column->type == 'date')
                                    <input type="date" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        {{ $column->is_required ? 'required' : '' }}>
                                        
                                @elseif($column->type == 'time')
                                    <input type="time" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        {{ $column->is_required ? 'required' : '' }}>

                                @elseif($column->type == 'datetime')
                                    <input type="datetime-local" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        {{ $column->is_required ? 'required' : '' }}>
                                
                                @elseif($column->type == 'file')
                                    <div class="current-file mb-2" id="current_file_{{ $column->column_name }}" style="display: none;">
                                        <label class="form-label text-muted">Current file:</label>
                                        <div class="file-current-preview">
                                            <a href="#" target="_blank" class="current-file-link">
                                                <i class="fas fa-file"></i>
                                                <span class="current-file-name"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <input type="file" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control"
                                        accept=".pdf,.doc,.docx,.txt,.xlsx,.xls">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Leave empty to keep current file. Supported: PDF, DOC, DOCX, TXT, Excel (Max: 2MB)
                                    </small>
                                
                                @elseif($column->type == 'image')
                                    <div class="current-image mb-2" id="current_image_{{ $column->column_name }}" style="display: none;">
                                        <label class="form-label text-muted">Current image:</label>
                                        <div class="current-image-preview">
                                            <img src="#" alt="Current image" class="current-image-thumb" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e9ecef;">
                                        </div>
                                    </div>
                                    <input type="file" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control"
                                        accept="image/*">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Leave empty to keep current image. Supported: JPG, PNG, GIF (Max: 2MB)
                                    </small>
                                        
                                @else
                                    <input type="text" 
                                        name="{{ $column->column_name }}" 
                                        id="edit_{{ $column->column_name }}" 
                                        class="form-control {{ $column->is_required ? 'required' : '' }}"
                                        placeholder="Masukkan {{ strtolower($column->name) }}..."
                                        @if(isset($column->options['max_length']))
                                            maxlength="{{ $column->options['max_length'] }}"
                                        @endif
                                        {{ $column->is_required ? 'required' : '' }}>
                                @endif
                                
                                {{-- Display validation errors --}}
                                @error($column->column_name)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                
                                @if(isset($column->options['help_text']))
                                    <div class="form-text">{{ $column->options['help_text'] }}</div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modern Alert Container -->
<div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
    @if(session('success'))
        <div class="modern-alert alert-success" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Berhasil!</strong>
                    <p>{{ session('success') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('error'))
        <div class="modern-alert alert-error" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Gagal!</strong>
                    <p>{{ session('error') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('warning'))
        <div class="modern-alert alert-warning" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-text">
                    <strong>Peringatan!</strong>
                    <p>{{ session('warning') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif

    @if(session('info'))
        <div class="modern-alert alert-info" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-text">
                    <strong>Info!</strong>
                    <p>{{ session('info') }}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    @endif
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    initializeAlerts();
    forceCleanupModals();
    initializeModalHandlers();
    initializeFormHandlers();
    handleServerValidationErrors();
});


// ============= MODAL FUNCTIONS =============
function forceCleanupModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show', 'fade');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
    });
    
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    document.body.style.marginRight = '';
}

function initializeModalHandlers() {
    // Add Modal Handler
    const addModal = document.getElementById('addDataModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function(e) {
            clearValidationErrors();
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Data';
                }
            }
        });
        
        addModal.addEventListener('hidden.bs.modal', function(e) {
            clearValidationErrors();
            forceCleanupModals();
        });
    }
    
    // Edit Modal Handler  
    const editModal = document.getElementById('editDataModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(e) {
            clearValidationErrors();
        });
        
        editModal.addEventListener('hidden.bs.modal', function(e) {
            clearValidationErrors();
            const form = this.querySelector('form');
            if (form) {
                form.reset();
            }
            forceCleanupModals();
        });
    }
}

function initializeFormHandlers() {
    // Handle Add Form Submission
    const addForm = document.querySelector('#addDataModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                sessionStorage.setItem('lastModalType', 'add');
            }
        });
    }
    
    // Handle Edit Form Submission
    const editForm = document.querySelector('#editDataModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
                sessionStorage.setItem('lastModalType', 'edit');
            }
        });
    }
}

// ============= EDIT DATA FUNCTION (FIXED) =============
function loadEditData(id, data) {
    console.log('=== EDIT DATA DEBUG ===');
    console.log('Editing ID:', id);
    console.log('Data received:', data);

    // Fix form action URL - replace PLACEHOLDER with actual ID
    // const form = document.getElementById('editForm');
    // const currentAction = form.getAttribute('action');
    // const newAction = currentAction.replace('PLACEHOLDER', id);
    // form.setAttribute('action', newAction);

    const form = document.getElementById('editForm');
    const baseAction = "{{ route('dashboard.table.update', [$dynamicTable->id, 'RECORD_ID_PLACEHOLDER']) }}";
    const newAction = baseAction.replace('RECORD_ID_PLACEHOLDER', id);
    form.action = newAction;
    
    console.log('Form action updated to:', newAction);
    console.log('Form method:', form.method);
    console.log('Form has method input:', form.querySelector('input[name="_method"]')?.value);

    console.log('Form action updated to:', form.action);

    // Clear form first
    form.reset();

    // Fill form fields
    Object.keys(data).forEach(key => {
        // Skip system columns
        if (['id', 'created_at', 'updated_at'].includes(key)) {
            return;
        }
        
        const field = document.getElementById('edit_' + key);
        if (field) {
            console.log(`Setting field ${key} (${field.type || field.tagName}) =`, data[key]);

            if (field.type === 'checkbox') {
                field.checked = data[key] ? true : false;
            } else if (field.tagName === 'SELECT') {
                // Handle select fields (boolean, enum)
                const value = data[key];
                field.value = value ?? '';
                console.log(`Select field ${key} set to:`, field.value, 'Options:', Array.from(field.options).map(o => o.value));
            } else if (field.type === 'datetime-local') {
                if (data[key]) {
                    try {
                        const d = new Date(data[key]);
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        const h = String(d.getHours()).padStart(2, '0');
                        const min = String(d.getMinutes()).padStart(2, '0');
                        field.value = `${y}-${m}-${day}T${h}:${min}`;
                    } catch (e) {
                        console.error('Error parsing datetime:', e);
                        field.value = '';
                    }
                } else {
                    field.value = '';
                }
            } else if (field.type === 'date') {
                if (data[key]) {
                    field.value = data[key].split(' ')[0];
                } else {
                    field.value = '';
                }
            } else if (field.type === 'file') {
                // Handle file fields - show current file if exists
                const currentFileDiv = document.getElementById('current_file_' + key);
                if (currentFileDiv && data[key]) {
                    currentFileDiv.style.display = 'block';
                    const fileLink = currentFileDiv.querySelector('.current-file-link');
                    const fileName = currentFileDiv.querySelector('.current-file-name');
                    
                    if (fileLink && fileName) {
                        fileLink.href = '{{ asset("storage/uploads/files/") }}/' + data[key];
                        fileName.textContent = data[key].split('/').pop();
                        
                        // Update icon based on file extension
                        const icon = fileLink.querySelector('i');
                        const ext = data[key].split('.').pop().toLowerCase();
                        icon.className = ext === 'pdf' ? 'fas fa-file-pdf' : 'fas fa-file-alt';
                    }
                } else if (currentFileDiv) {
                    currentFileDiv.style.display = 'none';
                }
                // Don't set value for file inputs - they can't be pre-filled
            } else if (field.accept && field.accept.includes('image')) {
                // Handle image fields - show current image if exists
                const currentImageDiv = document.getElementById('current_image_' + key);
                if (currentImageDiv && data[key]) {
                    currentImageDiv.style.display = 'block';
                    const imageThumb = currentImageDiv.querySelector('.current-image-thumb');
                    
                    if (imageThumb) {
                        imageThumb.src = '{{ asset("storage/uploads/images/") }}/' + data[key];
                        imageThumb.alt = 'Current ' + key;
                    }
                } else if (currentImageDiv) {
                    currentImageDiv.style.display = 'none';
                }
                // Don't set value for file inputs - they can't be pre-filled
            } else {
                field.value = data[key] ?? '';
            }
            
            console.log(`Field ${key} final value:`, field.value);
        } else {
            console.warn('Field not found in modal:', 'edit_' + key);
        }
    });
    
    // Debug final form state
    console.log('=== FINAL FORM STATE ===');
    const formData = new FormData(form);
    for (let [key, value] of formData.entries()) {
        console.log(`Form data ${key}:`, value);
    }
}

// ============= UTILITY FUNCTIONS =============
function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
}

function showFieldError(fieldName, message) {
    const field = document.getElementById(fieldName) || 
                document.getElementById('add_' + fieldName) || 
                document.getElementById('edit_' + fieldName);
    if (field) {
        field.classList.add('is-invalid');
        
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
        
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorMsg = this.parentNode.querySelector('.invalid-feedback');
            if (errorMsg) {
                errorMsg.remove();
            }
        }, { once: true });
    }
}

// ============= SERVER VALIDATION HANDLER =============
function handleServerValidationErrors() {
    @if($errors->any())
        console.log('Server validation errors detected');
        @foreach($errors->all() as $error)
            console.log('Error: {{ $error }}');
        @endforeach
        
        const modalType = sessionStorage.getItem('lastModalType');
        if (modalType === 'add') {
            const addModal = new bootstrap.Modal(document.getElementById('addDataModal'));
            addModal.show();
        } else if (modalType === 'edit') {
            const editModal = new bootstrap.Modal(document.getElementById('editDataModal'));
            editModal.show();
        }
        sessionStorage.removeItem('lastModalType');
    @endif
}

// ============= GLOBAL FUNCTIONS =============
window.loadEditData = loadEditData;
window.showAlert = showAlert;


// <!-- End Search & Filter Section -->
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const dateFromInput = document.getElementById('dateFromInput');
        const dateToInput = document.getElementById('dateToInput');
        const searchForm = document.getElementById('searchForm');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500); // 500ms debounce
        });

        dateFromInput.addEventListener('change', function() {
            searchForm.submit();
        });
        dateToInput.addEventListener('change', function() {
            searchForm.submit();
        });
    });
        });
    });

</script>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Preview" class="img-fluid max-height-400">
            </div>
        </div>
    </div>
</div>

<style>
/* File and Image Preview Styles */
.image-preview-container {
    position: relative;
    display: inline-block;
}

.table-image-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.table-image-preview:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 8px;
}

.image-preview-container:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    color: white;
    font-size: 16px;
}

.file-preview-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}

.file-link {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #007bff;
    padding: 6px 12px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    max-width: 200px;
}

.file-link:hover {
    background: #e9ecef;
    border-color: #007bff;
    text-decoration: none;
    color: #0056b3;
}

.file-name {
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}

.file-size {
    font-size: 0.75rem;
    margin-left: 20px;
}

/* Sample Preview Styles */
.sample-image-preview {
    display: flex;
    align-items: center;
    justify-content: center;
}

.sample-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}

.sample-file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    opacity: 0.7;
}

.max-height-400 {
    max-height: 400px;
    width: auto;
}

/* File type specific colors */
.file-link .fa-file-pdf {
    color: #dc3545;
}

.file-link .fa-file-word {
    color: #2b579a;
}

.file-link .fa-file-excel {
    color: #217346;
}

.file-link .fa-file-alt {
    color: #6c757d;
}
</style>

<script>
function showImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModalLabel').textContent = title || 'Image Preview';
}

@endsection