@extends('layouts.app')

@section('title', 'Detail API Endpoint')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- API Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Detail API Endpoint
                    </h3>
                    <div>
                        <a href="{{ route('settings.api.edit', $apiEndpoint) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('settings.api.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Nama:</th>
                                    <td>{{ $apiEndpoint->name }}</td>
                                </tr>
                                <tr>
                                    <th>Endpoint:</th>
                                    <td><code>{{ $apiEndpoint->endpoint }}</code></td>
                                </tr>
                                <tr>
                                    <th>Method:</th>
                                    <td>
                                        <span class="badge 
                                            @if($apiEndpoint->method == 'GET') bg-info
                                            @elseif($apiEndpoint->method == 'POST') bg-success
                                            @elseif($apiEndpoint->method == 'PUT') bg-warning
                                            @elseif($apiEndpoint->method == 'DELETE') bg-danger
                                            @endif">
                                            {{ $apiEndpoint->method }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Target Table:</th>
                                    <td>
                                        @if($apiEndpoint->table_name)
                                            <code>{{ $apiEndpoint->table_name }}</code>
                                        @else
                                            <span class="text-muted">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($apiEndpoint->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Permission:</th>
                                    <td>
                                        @if($apiEndpoint->permission)
                                            {{ $apiEndpoint->permission->menu->name ?? 'N/A' }}.{{ $apiEndpoint->permission->action->slug ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">Tidak ada permission khusus</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP Restriction:</th>
                                    <td>
                                        @if($apiEndpoint->use_ip_restriction)
                                            <span class="badge bg-warning text-dark">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Rate Limiting:</th>
                                    <td>
                                        @if($apiEndpoint->use_rate_limit)
                                            <span class="badge bg-info">{{ $apiEndpoint->rate_limit_max }}/{{ $apiEndpoint->rate_limit_period }}s</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Dibuat:</th>
                                    <td>{{ $apiEndpoint->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Diupdate:</th>
                                    <td>{{ $apiEndpoint->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($apiEndpoint->description)
                        <div class="mt-3">
                            <h6>Deskripsi:</h6>
                            <p>{{ $apiEndpoint->description }}</p>
                        </div>
                    @endif

                    @if($apiEndpoint->use_ip_restriction)
                        <div class="mt-3">
                            <h6>IP Configuration:</h6>
                            <div class="row">
                                @if($apiEndpoint->ip_whitelist)
                                    <div class="col-md-6">
                                        <strong>Whitelist IPs:</strong>
                                        <ul class="list-unstyled ms-3">
                                            @foreach($apiEndpoint->ip_whitelist as $ip)
                                                <li><code>{{ $ip }}</code></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($apiEndpoint->ip_blacklist)
                                    <div class="col-md-6">
                                        <strong>Blacklist IPs:</strong>
                                        <ul class="list-unstyled ms-3">
                                            @foreach($apiEndpoint->ip_blacklist as $ip)
                                                <li><code>{{ $ip }}</code></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- API Usage Example Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        Contoh Penggunaan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>cURL Example:</h6>
                            <pre class="bg-dark text-light p-3 rounded"><code>curl -X {{ $apiEndpoint->method }} \
  "{{ url($apiEndpoint->endpoint) }}" \
  -H "Content-Type: application/json"
@if($apiEndpoint->method == 'POST')
  -d '{
    "field1": "value1",
    "field2": "value2"
  }'
@endif</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>JavaScript Example:</h6>
                            <pre class="bg-dark text-light p-3 rounded"><code>fetch('{{ url($apiEndpoint->endpoint) }}', {
  method: '{{ $apiEndpoint->method }}',
  headers: {
    'Content-Type': 'application/json',
  }
@if($apiEndpoint->method == 'POST')
  body: JSON.stringify({
    field1: 'value1',
    field2: 'value2'
  })
@endif
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Logs Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Log Akses Terbaru (100 terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    @if($apiEndpoint->accessLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>IP Address</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Response</th>
                                        <th>Execution Time</th>
                                        <th>Block Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiEndpoint->accessLogs as $log)
                                    <tr>
                                        <td>
                                            @if($log->created_at instanceof \Carbon\Carbon)
                                                {{ $log->created_at->format('d/m H:i:s') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m H:i:s') }}
                                            @endif
                                        </td>
                                        <td><code>{{ $log->ip_address }}</code></td>
                                        <td>
                                            <span class="badge 
                                                @if($log->request_method == 'GET') bg-info
                                                @elseif($log->request_method == 'POST') bg-success
                                                @elseif($log->request_method == 'PUT') bg-warning
                                                @elseif($log->request_method == 'DELETE') bg-danger
                                                @endif">
                                                {{ $log->request_method }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->access_granted)
                                                <span class="badge bg-success">✓</span>
                                            @else
                                                <span class="badge bg-danger">✗</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->response_status)
                                                <span class="badge 
                                                    @if($log->response_status >= 200 && $log->response_status < 300) bg-success
                                                    @elseif($log->response_status >= 400 && $log->response_status < 500) bg-warning
                                                    @elseif($log->response_status >= 500) bg-danger
                                                    @else bg-info
                                                    @endif">
                                                    {{ $log->response_status }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->execution_time)
                                                {{ number_format($log->execution_time * 1000, 2) }}ms
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->block_reason)
                                                <small class="text-danger">{{ $log->block_reason }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">Belum ada log akses</h6>
                            <p class="text-muted">Log akan muncul ketika API endpoint ini diakses</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection