@extends('layouts.app')

@section('content')
<style>
    /* ===== CSS VARIABLES ===== */
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
        --danger-color: #e63946;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --gray-color: #6c757d;
        --white-color: #ffffff;
        
        --border-radius-sm: 8px;
        --border-radius: 12px;
        --border-radius-lg: 16px;
        
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
        
        --spacing-xs: 8px;
        --spacing-sm: 12px;
        --spacing: 16px;
        --spacing-lg: 24px;
        --spacing-xl: 32px;
    }

    /* ===== MAIN LAYOUT ===== */
    .main-content {
        padding: var(--spacing-lg);
        min-height: calc(100vh - 120px);
        background-color: #f5f7fa;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: var(--spacing-xl);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow);
        color: var(--white-color);
        position: relative;
        overflow: hidden;
    }

    .page-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--spacing);
    }

    .page-title {
        font-weight: 700;
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        margin: 0;
    }

    .date-display {
        background-color: rgba(255, 255, 255, 0.2);
        padding: var(--spacing-sm) var(--spacing-lg);
        border-radius: 50px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: 0.9rem;
    }

    /* ===== STAT CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .stat-card {
        background: var(--white-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-xl);
        box-shadow: var(--shadow);
        border: none;
        position: relative;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--spacing);
    }

    .stat-title {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--gray-color);
        margin: 0;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .stat-card.queue .stat-icon { background-color: rgba(67, 97, 238, 0.1); color: var(--primary-color); }
    .stat-card.missed .stat-icon { background-color: rgba(247, 37, 133, 0.1); color: var(--warning-color); }
    .stat-card.served .stat-icon { background-color: rgba(76, 201, 240, 0.1); color: var(--success-color); }
    .stat-card.active .stat-icon { background-color: rgba(230, 57, 70, 0.1); color: var(--danger-color); }

    .stat-value {
        font-size: clamp(2rem, 5vw, 2.8rem);
        font-weight: 700;
        color: var(--dark-color);
        margin: 0;
        line-height: 1;
    }

    /* ===== CONTENT GRID ===== */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: var(--spacing-xl);
    }

    /* ===== CHART CARD & NOTIFICATION CARD ===== */
    .chart-card,
    .notification-card {
        background: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .chart-header,
    .notification-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .chart-title,
    .notification-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--dark-color);
        margin: 0;
    }

    .chart-body {
        padding: var(--spacing-lg);
        height: 350px;
    }

    .notification-card {
        height: fit-content;
    }
    
    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: var(--spacing);
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        display: flex;
        gap: var(--spacing);
        align-items: flex-start;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .notification-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .notification-text h6 {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    .notification-text p {
        font-size: 0.85rem;
        margin-bottom: 4px;
        color: var(--gray-color);
        line-height: 1.4;
    }

    .notification-time {
        font-size: 0.75rem;
        color: #999;
    }


    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Dashboard Antrian</h1>
            
            {{-- FILTER LOKET --}}
            <form id="loketFilterForm" method="GET" action="{{ route('dashboard') }}" class="ms-auto">
                <select name="loket_id" class="form-select" onchange="this.form.submit()" aria-label="Filter Loket" style="background-color: rgb(255, 255, 255); border-color: rgba(255,255,255,0.3); color: rgb(0, 0, 0);">
                    <option value="all" {{ $selectedLoket == 'all' ? 'selected' : '' }}>Semua Loket</option>
                    @foreach($lokets as $loket)
                        <option value="{{ $loket->id }}" {{ $selectedLoket == $loket->id ? 'selected' : '' }} style="color: black; background-color: white;">
                            {{ $loket->nama_loket }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="date-display">
                <i class="material-icons">calendar_today</i>
                <span id="current-date">Memuat...</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card queue"><div class="stat-header"><h5 class="stat-title">Antrian Hari Ini</h5><div class="stat-icon"><i class="material-icons">queue</i></div></div><h2 class="stat-value">{{ $stats['today_queue'] }}</h2></div>
        <div class="stat-card missed"><div class="stat-header"><h5 class="stat-title">Terlewat</h5><div class="stat-icon"><i class="material-icons">schedule</i></div></div><h2 class="stat-value">{{ $stats['missed'] }}</h2></div>
        <div class="stat-card served"><div class="stat-header"><h5 class="stat-title">Terlayani</h5><div class="stat-icon"><i class="material-icons">check_circle</i></div></div><h2 class="stat-value">{{ $stats['served'] }}</h2></div>
        <div class="stat-card active"><div class="stat-header"><h5 class="stat-title">Antrian Aktif</h5><div class="stat-icon"><i class="material-icons">hourglass_empty</i></div></div><h2 class="stat-value">{{ $stats['active'] }}</h2></div>
    </div>

    <div class="content-grid">
        {{-- Kolom Kiri untuk Semua Diagram --}}
        <div>
            {{-- DIAGRAM BATANG MINGGUAN --}}
            <div class="chart-card mb-4">
                <div class="chart-header">
                    <h5 class="chart-title">Grafik Pengunjung 7 Hari Terakhir</h5>
                </div>
                <div class="chart-body">
                    <canvas id="weeklyVisitorChart"></canvas>
                </div>
            </div>
            
            {{-- DIAGRAM DONAT --}}
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">Status Antrian Selesai (Hari Ini)</h5>
                </div>
                <div class="chart-body">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Kolom Kanan untuk Notifikasi --}}
        <div class="notification-card">
            <div class="notification-header">
                <h5 class="notification-title">Notifikasi</h5>
                <span class="badge rounded-pill bg-primary">{{ count($notifications) }} Baru</span>
            </div>
            <div class="list-group list-group-flush notification-list">
                @forelse($notifications as $notification)
                <a href="#" class="list-group-item list-group-item-action notification-item {{ $notification['type'] ?? '' }}">
                    <div class="notification-icon"><i class="material-icons">{{ $notification['icon'] }}</i></div>
                    <div class="notification-text">
                        <h6>{{ $notification['title'] }}</h6>
                        <p>{{ $notification['message'] }}</p>
                        <div class="notification-time">{{ $notification['time'] }}</div>
                    </div>
                </a>
                @empty
                <div class="list-group-item notification-item">
                    <div class="notification-icon"><i class="material-icons">notifications_none</i></div>
                    <div class="notification-text">
                        <h6>Tidak ada notifikasi</h6>
                        <p>Tidak ada aktivitas terbaru.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menampilkan tanggal hari ini
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