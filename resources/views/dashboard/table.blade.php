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
    

    {{-- <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.table', $dynamicTable->id) }}" class="d-flex flex-wrap align-items-end gap-3">
                        <div class="flex-grow-1">
                            <label for="search" class="form-label">Pencarian</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Cari data...">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-auto">
                            <label for="sort" class="form-label">Urutkan</label>
                            <select name="sort" id="sort" class="form-select">
                                <option value="">-- Pilih Kolom --</option>
                                @foreach($dynamicTable->activeColumns->where('is_sortable', true) as $column)
                                    <option value="{{ $column->column_name }}"
                                        {{ request('sort') == $column->column_name ? 'selected' : '' }}>
                                        {{ $column->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <label for="direction" class="form-label">Arah</label>
                            <select name="direction" id="direction" class="form-select">
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>A-Z</option>
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Z-A</option>
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <label class="form-label">Filter Tanggal</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="Dari tanggal">
                                <span class="align-self-center">s/d</span>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai tanggal">
                            </div>
                        </div>
                        
                        <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
    
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
                                                @elseif($column->type == 'enum')
                                                    @if(isset($column->options['values']) && count($column->options['values']) > 0)
                                                        {{ $column->options['values'][0] }}
                                                    @else
                                                        Sample Enum
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
            <form method="POST" action="{{ route('dashboard.table.store', $dynamicTable->id) }}">
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
            <form method="POST" id="editForm" action="{{ route('dashboard.table.update', [$dynamicTable->id, 'PLACEHOLDER']) }}">
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

<style>
/* Modern Alert Styles */
    .modern-alert {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        margin-bottom: 12px;
        min-width: 320px;
        overflow: hidden;
        position: relative;
        transform: translateX(400px);
        opacity: 0;
        animation: slideInAlert 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        border-left: 4px solid;
    }

    .modern-alert.closing {
        animation: slideOutAlert 0.3s cubic-bezier(0.55, 0.06, 0.68, 0.19) forwards;
    }

    /* Alert Content Layout */
    .alert-content {
        display: flex;
        align-items: flex-start;
        padding: 16px;
        gap: 12px;
    }

    .alert-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .alert-text {
        flex: 1;
        min-width: 0;
    }

    .alert-text strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .alert-text p {
        margin: 0;
        font-size: 13px;
        opacity: 0.9;
        line-height: 1.4;
    }

    .alert-close {
        background: none;
        border: none;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.2s ease;
        font-size: 12px;
        flex-shrink: 0;
    }

    .alert-close:hover {
        opacity: 1;
    }

    /* Progress Bar */
    .alert-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.3);
        animation: progressBar 4s linear forwards;
    }

    /* Alert Type Colors */
    .alert-success {
        border-left-color: #10b981;
    }
    .alert-success .alert-icon {
        color: #10b981;
    }
    .alert-success .alert-progress {
        background: #10b981;
    }

    .alert-error {
        border-left-color: #ef4444;
    }
    .alert-error .alert-icon {
        color: #ef4444;
    }
    .alert-error .alert-progress {
        background: #ef4444;
    }

    .alert-warning {
        border-left-color: #f59e0b;
    }
    .alert-warning .alert-icon {
        color: #f59e0b;
    }
    .alert-warning .alert-progress {
        background: #f59e0b;
    }

    .alert-info {
        border-left-color: #3b82f6;
    }
    .alert-info .alert-icon {
        color: #3b82f6;
    }
    .alert-info .alert-progress {
        background: #3b82f6;
    }

    /* Animations */
    @keyframes slideInAlert {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutAlert {
        from {
            transform: translateX(0) scale(1);
            opacity: 1;
            max-height: 200px;
        }
        to {
            transform: translateX(400px) scale(0.8);
            opacity: 0;
            max-height: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
    }

    @keyframes progressBar {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }

    /* Responsive */
    @media (max-width: 480px) {
        #alertContainer {
            right: 12px;
            left: 12px;
            top: 12px;
            max-width: none;
        }
        
        .modern-alert {
            min-width: auto;
            transform: translateY(-100px);
        }
        
        @keyframes slideInAlert {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutAlert {
            from {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            to {
                transform: translateY(-100px) scale(0.95);
                opacity: 0;
            }
        }
    }
</style>

{{-- digunakan untuk "menyuntikkan" (push) potongan kode ke stack tertentu yang nanti akan ditampilkan di bagian @stack('scripts') --}}
{{-- @push('scripts') 
@endpush --}}

{{-- <script>
// ============= DEFINE loadEditData FUNCTION FIRST =============
function loadEditData(id, data) {
    console.log('=== LOADING EDIT DATA ===');
    console.log('ID:', id);
    console.log('Data:', data);
    
    const form = document.getElementById('editForm');
    if (!form) {
        console.error('Edit form not found!');
        alert('Form edit tidak ditemukan!');
        return;
    }
    
    // Update form action dengan ID yang benar
    const currentAction = form.getAttribute('action');
    console.log('Current action:', currentAction);
    
    const newAction = currentAction.replace('RECORD_ID_PLACEHOLDER', id);
    form.setAttribute('action', newAction);
    console.log('Form action updated to:', newAction);
    
    // Clear form terlebih dahulu
    form.reset();
    clearValidationErrors();
    
    // Fill form fields dengan data yang diterima
    let fieldsProcessed = 0;
    let fieldsFound = 0;
    
    Object.keys(data).forEach(key => {
        // Skip system columns
        if (['id', 'created_at', 'updated_at'].includes(key)) {
            return;
        }
        
        fieldsProcessed++;
        const field = document.getElementById('edit_' + key);
        
        if (field) {
            fieldsFound++;
            console.log(`Processing field: ${key}, type: ${field.type || field.tagName}, value: ${data[key]}`);
            
            try {
                if (field.type === 'checkbox') {
                    field.checked = Boolean(data[key]);
                    console.log(`Checkbox ${key}: ${field.checked}`);
                } else if (field.tagName.toLowerCase() === 'select') {
                    // Handle select fields (boolean, enum)
                    const value = data[key];
                    field.value = value !== null && value !== undefined ? String(value) : '';
                    console.log(`Select ${key}: '${field.value}' (from '${value}')`);
                } else if (field.type === 'date') {
                    if (data[key] && data[key] !== '') {
                        try {
                            // Handle date format
                            const dateStr = data[key].toString();
                            if (dateStr.includes(' ')) {
                                field.value = dateStr.split(' ')[0]; // Extract date part
                            } else {
                                field.value = dateStr;
                            }
                            console.log(`Date ${key}: '${field.value}'`);
                        } catch (e) {
                            console.error(`Date error for ${key}:`, e);
                            field.value = '';
                        }
                    } else {
                        field.value = '';
                    }
                } else if (field.type === 'datetime-local') {
                    if (data[key] && data[key] !== '') {
                        try {
                            const date = new Date(data[key]);
                            if (!isNaN(date.getTime())) {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                const hours = String(date.getHours()).padStart(2, '0');
                                const minutes = String(date.getMinutes()).padStart(2, '0');
                                field.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                                console.log(`Datetime ${key}: '${field.value}'`);
                            } else {
                                field.value = '';
                                console.log(`Invalid datetime for ${key}`);
                            }
                        } catch (e) {
                            console.error(`Datetime error for ${key}:`, e);
                            field.value = '';
                        }
                    } else {
                        field.value = '';
                    }
                } else if (field.tagName.toLowerCase() === 'textarea') {
                    // Handle textarea
                    field.value = data[key] !== null && data[key] !== undefined ? String(data[key]) : '';
                    console.log(`Textarea ${key}: '${field.value}'`);
                } else {
                    // Handle text, number, dan input lainnya
                    field.value = data[key] !== null && data[key] !== undefined ? String(data[key]) : '';
                    console.log(`Input ${key}: '${field.value}'`);
                }
            } catch (e) {
                console.error(`Error setting field ${key}:`, e);
                field.value = '';
            }
        } else {
            console.warn(`Field not found: edit_${key}`);
        }
    });
    
    console.log(`=== SUMMARY: ${fieldsFound}/${fieldsProcessed} fields found and processed ===`);
    
    // Debug: show final form state
    const formData = new FormData(form);
    console.log('=== FINAL FORM DATA ===');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: '${value}'`);
    }
    
    console.log('=== EDIT DATA LOADED SUCCESSFULLY ===');
}

// Make function available globally IMMEDIATELY
window.loadEditData = loadEditData;

// ============= OTHER FUNCTIONS =============
function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
}

function showAlert(type, title, message) {
    const container = document.getElementById('alertContainer');
    if (!container) {
        console.error('Alert container not found');
        return;
    }
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const alertHTML = `
        <div class="modern-alert alert-${type}" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="${icons[type] || icons.info}"></i>
                </div>
                <div class="alert-text">
                    <strong>${title}</strong>
                    <p>${message}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', alertHTML);
    const newAlert = container.lastElementChild;
    
    // Auto close after 4 seconds
    setTimeout(() => {
        if (newAlert && newAlert.parentNode) {
            newAlert.classList.add('closing');
            setTimeout(() => {
                if (newAlert && newAlert.parentNode) {
                    newAlert.parentNode.removeChild(newAlert);
                }
            }, 300);
        }
    }, 4000);
    
    // Handle close button
    const closeBtn = newAlert.querySelector('[data-alert-close="true"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const alert = e.target.closest('.modern-alert');
            if (alert) {
                alert.classList.add('closing');
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 300);
            }
        });
    }
}

// ============= MODAL AND FORM HANDLERS =============
function initializeAlerts() {
    const alerts = document.querySelectorAll('.modern-alert');
    alerts.forEach((alert, index) => {
        if (!alert.hasAttribute('data-initialized')) {
            const closeBtn = alert.querySelector('[data-alert-close="true"]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const alert = e.target.closest('.modern-alert');
                    if (alert) {
                        alert.classList.add('closing');
                        setTimeout(() => {
                            if (alert && alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 300);
                    }
                });
            }
            alert.setAttribute('data-initialized', 'true');
            
            // Auto close
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.classList.add('closing');
                    setTimeout(() => {
                        if (alert && alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 300);
                }
            }, 4000);
        }
    });
}

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

// ============= INITIALIZE EVERYTHING =============
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing...');
    
    initializeAlerts();
    forceCleanupModals();
    initializeModalHandlers();
    initializeFormHandlers();
    handleServerValidationErrors();
    
    // Debug: Log available edit fields
    console.log('Available edit fields:');
    document.querySelectorAll('[id^="edit_"]').forEach(field => {
        console.log(`- ${field.id}: ${field.tagName} (${field.type || 'no-type'})`);
    });
    
    console.log('All handlers initialized successfully');
});

// ============= MAKE FUNCTIONS GLOBAL =============
window.showAlert = showAlert;
window.clearValidationErrors = clearValidationErrors;

// ============= DEBUG HELPER =============
window.debugEditForm = function(id) {
    console.log('=== DEBUG EDIT FORM ===');
    const form = document.getElementById('editForm');
    console.log('Form found:', !!form);
    if (form) {
        console.log('Action:', form.action);
        console.log('Method:', form.method);
        console.log('_method input:', form.querySelector('input[name="_method"]')?.value);
    }
    
    console.log('Available edit fields:');
    document.querySelectorAll('[id^="edit_"]').forEach(field => {
        console.log(`- ${field.id}: ${field.tagName} (${field.type || 'no-type'})`);
    });
};

// ============= MANUAL TEST FUNCTION =============
window.testEditData = function() {
    console.log('=== MANUAL TEST ===');
    const testData = {
        nama_lengkap: 'Test User',
        email: 'test@example.com',
        nomor_telepon: '081234567890',
        tanggal_lahir: '1990-01-01',
        status: 'Aktif'
    };
    
    loadEditData(1, testData);
};

console.log('=== SCRIPT LOADED SUCCESSFULLY ===');
console.log('loadEditData function available:', typeof window.loadEditData);
console.log('Run testEditData() to test manually');
</script> --}}


<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAlerts();
    forceCleanupModals();
    initializeModalHandlers();
    initializeFormHandlers();
    handleServerValidationErrors();
});

// ============= ALERT FUNCTIONS (FIXED VERSION) =============
function initializeAlerts() {
    const alerts = document.querySelectorAll('.modern-alert');
    console.log('Found alerts:', alerts.length);
    
    alerts.forEach((alert, index) => {
        console.log(`Initializing alert ${index}`);
        
        // Pastikan belum ada event listener
        if (!alert.hasAttribute('data-initialized')) {
            setupAlertHandlers(alert);
            alert.setAttribute('data-initialized', 'true');
            
            // Auto close timer dengan ID unik
            const timerId = setTimeout(() => {
                console.log(`Auto closing alert ${index}`);
                closeAlert(alert);
            }, 4000);
            
            // Simpan timer ID untuk bisa dibatalkan
            alert.setAttribute('data-timer-id', timerId);
        }
    });
}

function setupAlertHandlers(alert) {
    const closeBtn = alert.querySelector('[data-alert-close="true"]');
    if (closeBtn) {
        // Gunakan handleAlertClose sebagai named function
        closeBtn.addEventListener('click', handleAlertClose);
        
        // Simpan reference untuk cleanup nanti
        alert._closeHandler = handleAlertClose;
    }
}

function handleAlertClose(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const alert = e.target.closest('.modern-alert');
    if (alert) {
        console.log('Manual close triggered');
        closeAlert(alert);
    }
}

function closeAlert(alertElement) {
    // Validasi element
    if (!alertElement || !alertElement.classList) {
        console.warn('Invalid alert element');
        return;
    }
    
    // Cek apakah sudah dalam proses closing
    if (alertElement.classList.contains('closing')) {
        console.log('Alert already closing');
        return;
    }
    
    console.log('Closing alert');
    
    // Cancel timer jika ada
    const timerId = alertElement.getAttribute('data-timer-id');
    if (timerId) {
        clearTimeout(parseInt(timerId));
        alertElement.removeAttribute('data-timer-id');
    }
    
    // Remove event listener untuk mencegah memory leak
    const closeBtn = alertElement.querySelector('[data-alert-close="true"]');
    if (closeBtn && alertElement._closeHandler) {
        closeBtn.removeEventListener('click', alertElement._closeHandler);
        delete alertElement._closeHandler;
    }
    
    // Start closing animation
    alertElement.classList.add('closing');
    
    // Remove from DOM after animation
    setTimeout(() => {
        if (alertElement && alertElement.parentNode) {
            console.log('Removing alert from DOM');
            alertElement.parentNode.removeChild(alertElement);
        }
    }, 300); // Sesuai dengan durasi CSS animation
}

function showAlert(type, title, message) {
    const container = document.getElementById('alertContainer');
    if (!container) {
        console.error('Alert container not found');
        return;
    }
    
    console.log(`Creating ${type} alert:`, title, message);
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const alertHTML = `
        <div class="modern-alert alert-${type}" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="${icons[type] || icons.info}"></i>
                </div>
                <div class="alert-text">
                    <strong>${title}</strong>
                    <p>${message}</p>
                </div>
                <button class="alert-close" data-alert-close="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', alertHTML);
    const newAlert = container.lastElementChild;
    
    // Setup handlers untuk alert baru
    setupAlertHandlers(newAlert);
    newAlert.setAttribute('data-initialized', 'true');
    
    // Auto close timer
    const timerId = setTimeout(() => {
        console.log('Auto closing new alert');
        closeAlert(newAlert);
    }, 4000);
    
    newAlert.setAttribute('data-timer-id', timerId);
}

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
</script>

<!-- End Search & Filter Section -->
<script>
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
</script>

@endsection