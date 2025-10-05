@extends('layouts.app')

@section('content')

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

    <section class="#" id="#">
        <div class="roles-header">
            <h1>Tabel Menu</h1>
            <a href="{{ route('settings.dynamic-menus.create') }}" class="add-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Tambah Menu
            </a> 
        </div>

        {{-- Table --}}
        <div class="table-container">
            @if($menus->count() > 0)
                <div class="table-responsive">
                    <table class="roles-table">
                        <thead>
                            <tr>
                                <th>Nama Menu</th>
                                <th>Icon</th>
                                <th>Kategori</th>
                                <th>Permission</th>
                                <th>Sub Menu</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>  
                            @foreach($menus as $menu)
                                <tr>
                                    <td><strong>{{ $menu->name }}</strong></td>
                                    <td><i class="{{ $menu->icon }}"></i> {{ $menu->icon }}</td>
                                    <td>
                                        <span class="badge badge-{{ $menu->category === 'main' ? 'primary' : 'secondary' }}">
                                            {{ $menu->category === 'main' ? 'Menu Utama' : 'Pengaturan' }}
                                        </span>
                                    </td>
                                    <td>{{ $menu->permission_key ?: '-' }}</td>
                                    <td>
                                        {{ $menu->items->count() }} item
                                        <a href="{{ route('settings.dynamic-menu-items', $menu) }}" class="btn btn-sm btn-outline-primary ms-1">
                                            <i class="fas fa-list"></i> Kelola
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $menu->is_active ? 'success' : 'danger' }}">
                                            {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('settings.dynamic-menus.edit', $menu) }}" class="btn btn-primary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>

                                            <form 
                                                action="{{ route('settings.dynamic-menus.destroy', $menu) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this table?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="border: none;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-bars fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada menu dinamis</h5>
                    <p class="text-muted">Mulai dengan membuat menu pertama Anda</p>
                    <a href="{{ route('settings.dynamic-menus.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Menu Pertama
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
