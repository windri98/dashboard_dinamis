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
<section class="roles-section" id="user">
    <div class="roles-header">
        <h1>User</h1>
        <a href="{{ route('settings.users.create') }}" class="add-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            Add New Role
        </a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>username</th>
                        <th>Roles</th>
                        <th>Action</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->nama }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->roles->role}}</td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <form action="#">
                                            <button type="submit" class="btn btn-sm btn-view">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                                                </svg>
                                                {{-- View --}}
                                            </button>
                                        </form>
                                        <form action="{{ route('settings.users.edit', $user->id) }} ">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                {{-- Edit --}}
                                            </button>
                                        </form>
                                        <form action="{{ route('settings.users.destroy', $user->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" style="border: none;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                {{-- Delete --}}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data role</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </table>
        </div>
    </div>
</section>

@endsection