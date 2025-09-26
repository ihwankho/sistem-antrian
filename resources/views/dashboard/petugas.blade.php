@extends('layouts.app')

@section('content')
<style>
    /* Menggunakan CSS yang sama persis dengan dashboard Admin untuk konsistensi */
    :root {
        --primary-color: #4361ee; --secondary-color: #3f37c9; --success-color: #4cc9f0;
        --warning-color: #f72585; --danger-color: #e63946; --light-color: #f8f9fa;
        --dark-color: #212529; --gray-color: #6c757d; --white-color: #ffffff;
        --border-radius: 12px; --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --spacing-lg: 24px; --spacing-xl: 32px;
    }
    .page-header { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: var(--spacing-xl); border-radius: var(--border-radius); margin-bottom: var(--spacing-xl); box-shadow: var(--shadow); color: var(--white-color); }
    .page-header-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .page-title { font-weight: 700; font-size: clamp(1.5rem, 4vw, 2.2rem); margin: 0; }
    .date-display { background-color: rgba(255, 255, 255, 0.2); padding: 12px 24px; border-radius: 50px; font-weight: 500; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl); }
    .stat-card { background: var(--white-color); border-radius: var(--border-radius); padding: var(--spacing-xl); box-shadow: var(--shadow); border: none; }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
    .stat-title { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--gray-color); margin: 0; }
    .stat-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .stat-card.served .stat-icon { background-color: rgba(76, 201, 240, 0.1); color: var(--success-color); }
    .stat-card.missed .stat-icon { background-color: rgba(230, 57, 70, 0.1); color: var(--danger-color); }
    .stat-card.waiting .stat-icon { background-color: rgba(247, 37, 133, 0.1); color: var(--warning-color); }
    .stat-value { font-size: clamp(2rem, 5vw, 2.8rem); font-weight: 700; color: var(--dark-color); margin: 0; line-height: 1; }
    .chart-card { background: var(--white-color); border-radius: var(--border-radius); box-shadow: var(--shadow); overflow: hidden; }
    .chart-header { padding: var(--spacing-lg); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .chart-title { font-weight: 700; font-size: 1.1rem; color: var(--dark-color); margin: 0; }
    .chart-body { padding: var(--spacing-lg); height: 350px; }
    .info-box {
        background-color: var(--white-color);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <h1 class="page-title">Dashboard Petugas</h1>
                <p class="mb-0">Ringkasan aktivitas di <strong>{{ $petugasInfo->nama_loket ?? 'Loket Tidak Terdaftar' }}</strong></p>
            </div>
            <div class="date-display">
                <i class="material-icons">calendar_today</i>
                <span id="current-date">Memuat...</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card served"><div class="stat-header"><h5 class="stat-title">Antrian Terlayani</h5><div class="stat-icon"><i class="material-icons">check_circle</i></div></div><h2 class="stat-value">{{ $stats['served_by_you'] }}</h2></div>
        <div class="stat-card missed"><div class="stat-header"><h5 class="stat-title">Antrian Terlewat</h5><div class="stat-icon"><i class="material-icons">schedule</i></div></div><h2 class="stat-value">{{ $stats['missed_by_you'] }}</h2></div>
        <div class="stat-card waiting"><div class="stat-header"><h5 class="stat-title">Menunggu di Loket</h5><div class="stat-icon"><i class="material-icons">hourglass_empty</i></div></div><h2 class="stat-value">{{ $stats['waiting_for_you'] }}</h2></div>
    </div>

    <div class="row">
        {{-- Kolom Kiri untuk Diagram Batang --}}
        <div class="col-lg-7 mb-4">
            <div class="chart-card h-100">
                <div class="chart-header">
                    <h5 class="chart-title">Grafik Pengunjung Loket Anda (7 Hari Terakhir)</h5>
                </div>
                <div class="chart-body">
                    <canvas id="weeklyVisitorChart"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Kolom Kanan untuk Diagram Donat --}}
        <div class="col-lg-5 mb-4">
            <div class="chart-card h-100">
                <div class="chart-header">
                    <h5 class="chart-title">Status Antrian Loket Anda (Hari Ini)</h5>
                </div>
                <div class="chart-body">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="info-box">
        <h4><i class="material-icons text-primary align-middle me-2">info</i>Informasi Layanan</h4>
        <p class="text-secondary">Anda ditugaskan untuk melayani antrian pada <strong>Departemen {{ $petugasInfo->departemen->nama_departemen ?? 'N/A' }}</strong>. Selalu berikan pelayanan yang efisien dan ramah.</p>
        <a href="{{ route('panggilan.admin') }}" class="btn btn-primary">
            <i class="material-icons align-middle me-1">call</i>
            Mulai Panggil Antrian
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menampilkan tanggal
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    
    // Inisialisasi Diagram Batang Mingguan
    const weeklyCtx = document.getElementById('weeklyVisitorChart');
    if (weeklyCtx) {
        const weeklyData = {!! json_encode($weeklyVisitorData) !!};
        new Chart(weeklyCtx.getContext('2d'), {
            type: 'bar',
            data: { labels: weeklyData.labels, datasets: [{ label: 'Jumlah Pengunjung', data: weeklyData.data, backgroundColor: '#4e73df', hoverBackgroundColor: '#2e59d9', borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
        });
    }

    // Inisialisasi Diagram Donat
    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        const donutData = {!! json_encode($donutChartData) !!};
        if (donutData.served === 0 && donutData.missed === 0) {
            donutCtx.parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted">Belum ada data.</div>';
        } else {
            new Chart(donutCtx.getContext('2d'), {
                type: 'doughnut',
                data: { labels: ['Terlayani', 'Terlewat'], datasets: [{ data: [donutData.served, donutData.missed], backgroundColor: ['#1cc88a', '#e74a3b'], hoverBackgroundColor: ['#17a673', '#c73e31'] }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { position: 'bottom' } } }
            });
        }
    }
});
</script>
@endsection