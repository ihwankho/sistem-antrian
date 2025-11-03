@extends('layouts.app')

{{-- Menggunakan file CSS yang SAMA dengan dashboard admin --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endpush

@section('content')
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