@extends('layouts.app')

@section('content')
    <section class="roles-section" id="role">
        <div class="roles-header">
            <h1>Kelola Tabel</h1>
            <a href="{{ route('settings.dynamic-tables.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"/>
                </svg>
                Tambah Tabel
            </a>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        {{-- Table --}}
        <div class="table-container">
            @if($tables->count())
                <div class="table-responsive">
                    <table class="roles-table">
                        <thead>
                            <tr>
                                <th>Nama Tabel</th>
                                <th>Table Name</th>
                                <th>Kolom</th>
                                <th>Digunakan di Menu</th>
                                <th>Diskripsi</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tables as $table)
                                <tr>
                                    <td><strong>{{ $table->name }}</strong></td>
                                    <td><code>{{ $table->table_name }}</code></td>
                                    <td>
                                        {{ $table->columns->count() }} kolom
                                        <a href="{{ route('settings.dynamic-table-columns', $table) }}"
                                            class="btn btn-sm btn-outline-primary ms-1">
                                            <i class="fas fa-columns"></i> Kelola
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $usedInMenus = \App\Models\DynamicMenuItem::where('link_type', 'table')
                                                ->where('link_value', $table->id)
                                                ->with('menu')
                                                ->get();
                                        @endphp
                                        @if($usedInMenus->count())
                                            @foreach($usedInMenus as $menuItem)
                                                <span class="badge badge-info">
                                                    {{ $menuItem->menu->name }} > {{ $menuItem->name }}
                                                </span><br>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Belum digunakan</span>
                                        @endif
                                    </td>
                                    <td>{{ $table->description ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $table->is_active ? 'success' : 'danger' }}">
                                            {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('settings.dynamic-tables.edit', $table->id) }}" class="btn btn-sm btn-primary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>

                                            {{-- Delete --}}
                                            <form action="{{ route('settings.dynamic-tables.destroy', $table->id) }}"method="POST" class="delete-form"onsubmit="return confirm('Are you sure you want to delete this table?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"stroke="currentColor" stroke-width="2"stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection
