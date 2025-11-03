@extends('layouts.app')

{{-- Menambahkan CSS khusus halaman ini ke layout utama --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Dashboard Antrian</h1>
            
            {{-- FILTER LOKET --}}
            <form id="loketFilterForm" method="GET" action="{{ route('dashboard') }}" class="ms-auto">
                {{-- Menghapus style inline dan menggantinya dengan class --}}
                <select name="loket_id" class="form-select page-header-filter" onchange="this.form.submit()" aria-label="Filter Loket">
                    <option value="all" {{ $selectedLoket == 'all' ? 'selected' : '' }}>Semua Loket</option>
                    @foreach($lokets as $loket)
                        {{-- Menghapus style inline dari option --}}
                        <option value="{{ $loket->id }}" {{ $selectedLoket == $loket->id ? 'selected' : '' }}>
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