@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --danger-color: #e63946;
        --warning-color: #f72585;
        --white-color: #ffffff;
        --gray-color: #6c757d;
        --border-radius: 12px;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --spacing-lg: 24px;
        --spacing-xl: 32px;
    }
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: var(--spacing-xl);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow);
        color: var(--white-color);
    }
    .page-title {
        font-weight: 700;
    }
    .date-display {
        background-color: rgba(255, 255, 255, 0.2);
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 500;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }
    .stat-card {
        background: var(--white-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-xl);
        box-shadow: var(--shadow);
        border: none;
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--gray-color);
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-card.served .stat-icon { background-color: rgba(76, 201, 240, 0.1); color: var(--success-color); }
    .stat-card.missed .stat-icon { background-color: rgba(230, 57, 70, 0.1); color: var(--danger-color); }
    .stat-card.waiting .stat-icon { background-color: rgba(247, 37, 133, 0.1); color: var(--warning-color); }
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
    }
    .info-box {
        background-color: var(--white-color);
        padding: var(--spacing-xl);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
</style>

<div class="container-fluid">
    {{-- Header Halaman --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="page-title mb-1">Selamat Datang, {{ Auth::user()->nama }}!</h1>
                <p class="mb-0">Berikut adalah ringkasan aktivitas Anda hari ini di <strong>{{ $petugasInfo->nama_loket ?? 'Loket Tidak Terdaftar' }}</strong>.</p>
            </div>
            <div class="date-display d-none d-md-flex align-items-center mt-2 mt-md-0">
                <i class="material-icons me-2">calendar_today</i>
                <span id="current-date">Memuat...</span>
            </div>
        </div>
    </div>

    {{-- Kartu Statistik Khusus Petugas --}}
    <div class="stats-grid mb-4">
        <div class="stat-card served">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="stat-title">Antrian Anda Terlayani (Hari Ini)</h5>
                <div class="stat-icon"><i class="material-icons">check_circle</i></div>
            </div>
            <h2 class="stat-value">{{ $stats['served_by_you'] }}</h2>
        </div>
        <div class="stat-card missed">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="stat-title">Antrian Anda Terlewat (Hari Ini)</h5>
                <div class="stat-icon"><i class="material-icons">schedule</i></div>
            </div>
            <h2 class="stat-value">{{ $stats['missed_by_you'] }}</h2>
        </div>
        <div class="stat-card waiting">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="stat-title">Menunggu di Loket Anda</h5>
                <div class="stat-icon"><i class="material-icons">hourglass_empty</i></div>
            </div>
            <h2 class="stat-value">{{ $stats['waiting_for_you'] }}</h2>
        </div>
    </div>

    {{-- Informasi Layanan --}}
    <div class="info-box">
        <h4><i class="material-icons text-primary align-middle me-2">info</i>Informasi Layanan</h4>
        <p class="text-secondary">Anda ditugaskan untuk melayani antrian pada <strong>Departemen {{ $petugasInfo->departemen->nama_departemen ?? 'N/A' }}</strong>. Selalu berikan pelayanan yang efisien dan ramah.</p>
        <a href="{{ route('panggilan.admin') }}" class="btn btn-primary">
            <i class="material-icons align-middle me-1">call</i>
            Mulai Panggil Antrian
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }
});
</script>
@endpush