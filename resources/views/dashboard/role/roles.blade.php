@extends('layouts.app')

@section('content')

<section class="roles-section" id="role">
    <div class="roles-header">
        <h1>Roles</h1>
        <a href="{{ route('add.role') }}" class="add-button">Add New Role</a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Akses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $index => $role)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $role->role }}</td>
                            <td>
                                <!-- Button untuk toggle permissions -->
                                <button type="button" class="btn btn-sm" onclick="togglePermissionsSlide(this)">
                                    <span class="toggle-text">Lihat Akses</span>
                                    <svg class="toggle-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
                                    </svg>
                                </button>
                                
                                <!-- Container permissions yang bisa di-toggle -->
                                <div class="permissions-container">
                                    @php
                                        $permissions = is_string($role->akses) ? json_decode($role->akses, true) : $role->akses;
                                        $permissions = is_array($permissions) ? $permissions : [];
                                        $actionLabels = [
                                            'read' => '<span class="badge bg-info">Read</span>',
                                            'create' => '<span class="badge bg-success">Create</span>',
                                            'edit' => '<span class="badge bg-warning">Edit</span>',
                                            'delete' => '<span class="badge bg-danger">Delete</span>',
                                        ];
                                    @endphp
                                    
                                    <div class="permissions-content">
                                        @if(empty($permissions))
                                            <span class="badge bg-secondary">All Akses</span>
                                        @else
                                            @foreach($permissions as $moduleSlug => $actions)
                                                @php
                                                    $moduleName = isset($modules) ? ($modules->firstWhere('slug', $moduleSlug)->nama ?? $moduleSlug) : $moduleSlug;
                                                @endphp
                                                <div class="permission-item">
                                                    <strong>{{ $moduleName }}:</strong>
                                                    @if($actions === true)
                                                        <span class="badge bg-primary">Akses Penuh</span>
                                                    @elseif(is_array($actions))
                                                        @foreach($actions as $action)
                                                            {!! $actionLabels[$action] ?? "<span class='badge bg-secondary'>{$action}</span>" !!}
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="action-cell">
                                <div class="action-buttons">
                                    <form action="{{ route('edit.role', $role->id) }}">
                                        <button type="submit" class="btn btn-sm btn-primary" style="border: none">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            {{-- Edit --}}
                                        </button>
                                    </form>
                                    
                                    {{-- <a href="{{ route('edit.role', $role->id) }}" class="edit-button">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                        Edit
                                    </a> --}}
                                    <form action="{{ route('delete.role', $role->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this role?');">
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
        </div>
    </div>
</section>

<script>
// Alert handling
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 - Tampilkan Notifikasi Jika Data Berhasil atau Gagal
    let successMessage = @json(session('success'));
    let errorMessage = @json(session('error'));

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: successMessage,
            confirmButtonColor: '#1f6e8c',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'custom-success-popup',
                title: 'custom-title',
                content: 'custom-text',
                confirmButton: 'custom-confirm-button'
            }
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: errorMessage,
            confirmButtonColor: '#ff0000',
            confirmButtonText: 'Coba Lagi',
            customClass: {
                popup: 'custom-error-popup',
                title: 'custom-title',
                content: 'custom-text',
                confirmButton: 'custom-confirm-button'
            }
        });
    }
});

// Function untuk toggle permissions
function togglePermissionsSlide(button) {
    const container = button.parentElement.querySelector('.permissions-container');
    const toggleText = button.querySelector('.toggle-text');
    
    // Toggle class show pada container
    container.classList.toggle('show');
    
    // Toggle class active pada button untuk rotasi icon
    button.classList.toggle('active');
    
    // Update text button
    if (container.classList.contains('show')) {
        toggleText.textContent = 'Sembunyikan Akses';
    } else {
        toggleText.textContent = 'Lihat Akses';
    }
}
</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection