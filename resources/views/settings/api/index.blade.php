@extends('layouts.app')

@section('title', 'Kelola API Endpoints')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        Kelola API Endpoints
                    </h3>
                    <div>
                        <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="fas fa-magic"></i> Generate APIs
                        </button>
                        <a href="{{ route('settings.api.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah API
                        </a>
                    </div>
                </div>
                <div class="table-container">
                    @if($apiEndpoints->count() > 0)
                        <div class="roles-table">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Name</th>
                                        <th width="20%">Endpoint</th>
                                        <th width="8%">Method</th>
                                        <th width="12%">Table</th>
                                        <th width="10%">Permission</th>
                                        <th width="8%">Status</th>
                                        <th width="10%">Security</th>
                                        <th width="12%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiEndpoints as $index => $api)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $api->name }}</strong>
                                            @if($api->description)
                                                <br><small class="text-muted">{{ Str::limit($api->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $api->endpoint }}</code>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($api->method == 'GET') bg-info
                                                @elseif($api->method == 'POST') bg-success
                                                @elseif($api->method == 'PUT') bg-warning
                                                @elseif($api->method == 'DELETE') bg-danger
                                                @endif">
                                                {{ $api->method }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($api->table_name)
                                                <code>{{ $api->table_name }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($api->permission)
                                                <small>{{ $api->permission->menu->name ?? 'N/A' }}.{{ $api->permission->action->slug ?? 'N/A' }}</small>
                                            @else
                                                <span class="text-muted">No permission</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('settings.api.toggle', $api) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $api->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                                        onclick="return confirm('Toggle status API ini?')">
                                                    @if($api->is_active)
                                                        <i class="fas fa-check"></i>
                                                    @else
                                                        <i class="fas fa-times"></i>
                                                    @endif
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if($api->use_ip_restriction)
                                                    <span class="badge bg-warning text-dark" title="IP Restriction">
                                                        <i class="fas fa-shield-alt"></i> IP
                                                    </span>
                                                @endif
                                                @if($api->use_rate_limit)
                                                    <span class="badge bg-info" title="Rate Limit: {{ $api->rate_limit_max }}/{{ $api->rate_limit_period }}s">
                                                        <i class="fas fa-tachometer-alt"></i> Rate
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('settings.api.show', $api) }}" class="btn btn-sm btn-view" title="View Details">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                                                </svg>
                                                </a>
                                                <a href="{{ route('settings.api.edit', $api) }}" class="btn btn-sm btn-primary" title="Edit">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                </a>
                                                <form action="{{ route('settings.api.destroy', $api) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Hapus API endpoint ini?')" title="Delete">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
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
                        <div class="text-center py-5">
                            <i class="fas fa-code fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada API endpoints</h5>
                            <p class="text-muted">Buat API endpoint pertama atau generate dari tabel dinamis</p>
                            <a href="{{ route('settings.api.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah API Endpoint
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate APIs Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.api.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="generateModalLabel">Generate API Endpoints</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="table_id" class="form-label">Pilih Tabel <span class="text-danger">*</span></label>
                        <select name="table_id" id="table_id" class="form-select" required>
                            <option value="">-- Pilih Tabel --</option>
                            @foreach(\App\Models\DynamicTable::where('is_active', true)->get() as $table)
                                <option value="{{ $table->id }}">{{ $table->name }} ({{ $table->table_name }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Generate 5 endpoint CRUD (GET, POST, PUT, DELETE) untuk tabel ini</small>
                    </div>

                    <div class="mb-3">
                        <label for="gen_permission_id" class="form-label">Permission (Opsional)</label>
                        <select name="permission_id" id="gen_permission_id" class="form-select">
                            <option value="">-- Tanpa Permission --</option>
                            @foreach(\App\Models\Permission::with(['menu', 'action'])->get() as $permission)
                                <option value="{{ $permission->id }}">
                                    {{ $permission->menu->name ?? 'N/A' }}.{{ $permission->action->slug ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_ip_restriction" value="1" id="gen_use_ip_restriction">
                                <label class="form-check-label" for="gen_use_ip_restriction">
                                    IP Restriction
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_rate_limit" value="1" id="gen_use_rate_limit">
                                <label class="form-check-label" for="gen_use_rate_limit">
                                    Rate Limiting
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="gen_ip_section" style="display: none;">
                        <label for="gen_ip_whitelist" class="form-label">IP Whitelist</label>
                        <input type="text" name="ip_whitelist" id="gen_ip_whitelist" class="form-control" 
                               placeholder="192.168.1.1, 10.0.0.0/8">
                        <small class="text-muted">Pisahkan dengan koma untuk multiple IP</small>
                    </div>

                    <div class="row" id="gen_rate_section" style="display: none;">
                        <div class="col-md-6">
                            <label for="gen_rate_limit_max" class="form-label">Max Requests</label>
                            <input type="number" name="rate_limit_max" id="gen_rate_limit_max" class="form-control" value="60" min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="gen_rate_limit_period" class="form-label">Period (seconds)</label>
                            <input type="number" name="rate_limit_period" id="gen_rate_limit_period" class="form-control" value="60" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Generate APIs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle IP section
    document.getElementById('gen_use_ip_restriction').addEventListener('change', function() {
        document.getElementById('gen_ip_section').style.display = this.checked ? 'block' : 'none';
    });

    // Toggle Rate section
    document.getElementById('gen_use_rate_limit').addEventListener('change', function() {
        document.getElementById('gen_rate_section').style.display = this.checked ? 'block' : 'none';
    });
});
</script>
@endsection