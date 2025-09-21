@extends('layouts.app')

@section('content')

    <h1 class="page-title">Dasbord</h1>
    <!-- Section: Kartu Ringkasan -->
    <section class="section-summary">
        <div class="section-header">
            <h2 class="section-title">Ringkasan</h2>
        </div>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Pengguna</div>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="card-value">1,258</div>
                <div class="card-info">
                    <i class="fas fa-arrow-up"></i> 12% dari bulan lalu
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pendapatan</div>
                    <div class="card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="card-value">Rp 45,6 Jt</div>
                <div class="card-info">
                    <i class="fas fa-arrow-up"></i> 8% dari bulan lalu
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Proyek Aktif</div>
                    <div class="card-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                </div>
                <div class="card-value">24</div>
                <div class="card-info">
                    <i class="fas fa-arrow-down"></i> 3% dari bulan lalu
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Tingkat Konversi</div>
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="card-value">64.7%</div>
                <div class="card-info">
                    <i class="fas fa-arrow-up"></i> 5% dari bulan lalu
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Grafik/Analitik -->
    <section class="section-analytics">
        <div class="section-header">
            <h2 class="section-title">Analitik & Visualisasi</h2>
        </div>
        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-header">
                    <h3>Aktifitas Bulanan</h3>
                    <div class="chart-actions">
                        <button class="chart-btn active">Bulan</button>
                        <button class="chart-btn">Kuartal</button>
                        <button class="chart-btn">Tahun</button>
                    </div>
                </div>
                <div class="chart-canvas">
                    <!-- Canvas untuk grafik akan dirender oleh JS -->
                    <canvas id="monthlyActivityChart"></canvas>
                    <div style="color: #666;" class="chart-placeholder">Grafik aktivitas akan muncul di sini</div>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="chart-header">
                    <h3>Distribusi Pengguna</h3>
                </div>
                <div class="chart-canvas">
                    <!-- Canvas untuk grafik pie akan dirender oleh JS -->
                    <canvas id="userDistributionChart"></canvas>
                    <div style="color: #666;" class="chart-placeholder">Grafik distribusi akan muncul di sini</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Proyek -->
    <section class="section-projects">
        <div class="section-header">
            <h2 class="section-title">Manajemen Proyek</h2>
        </div>
        <div class="table-wrapper">
            <div class="chart-header">
                <h3>Proyek Terbaru</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Proyek</th>
                        <th>Manajer</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Redesain Website</td>
                        <td>Andi Pratama</td>
                        <td>20 Apr 2025</td>
                        <td><span class="status status-active">Aktif</span></td>
                        <td>75%</td>
                    </tr>
                    <tr>
                        <td>Aplikasi Mobile</td>
                        <td>Budi Santoso</td>
                        <td>05 Mei 2025</td>
                        <td><span class="status status-pending">Pending</span></td>
                        <td>40%</td>
                    </tr>
                    <tr>
                        <td>Integrasi CRM</td>
                        <td>Citra Dewi</td>
                        <td>15 Mar 2025</td>
                        <td><span class="status status-completed">Selesai</span></td>
                        <td>100%</td>
                    </tr>
                    <tr>
                        <td>Kampanye Digital</td>
                        <td>Doni Wijaya</td>
                        <td>28 Apr 2025</td>
                        <td><span class="status status-active">Aktif</span></td>
                        <td>60%</td>
                    </tr>
                    <tr>
                        <td>Analisis Data</td>
                        <td>Ella Permata</td>
                        <td>10 Mei 2025</td>
                        <td><span class="status status-pending">Pending</span></td>
                        <td>25%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
