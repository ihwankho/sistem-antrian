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
        height: 140px;
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

    /* ===== CHART CARD ===== */
    .chart-card {
        background: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .chart-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .chart-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--dark-color);
        margin: 0;
    }

    .chart-body {
        padding: var(--spacing-lg);
        height: 350px;
    }

    /* ===== NOTIFICATION CARD ===== */
    .notification-card {
        background: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        height: fit-content;
    }

    .notification-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--dark-color);
        margin: 0;
    }

    .notification-badge {
        background: var(--primary-color);
        color: var(--white-color);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
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

    .notification-item.new .notification-icon { background-color: rgba(67, 97, 238, 0.15); color: var(--primary-color); }
    .notification-item.warning .notification-icon { background-color: rgba(247, 37, 133, 0.15); color: var(--warning-color); }
    .notification-item.success .notification-icon { background-color: rgba(76, 201, 240, 0.15); color: var(--success-color); }

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

    /* ===== FOOTER ===== */
    .footer {
        text-align: center;
        color: var(--gray-color);
        font-size: 0.9rem;
        margin-top: var(--spacing-xl);
        padding: var(--spacing) 0;
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1200px) {
        .content-grid { grid-template-columns: 1fr 350px; }
    }
    @media (max-width: 992px) {
        .content-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Dashboard Antrian</h1>
            <div class="date-display">
                <i class="material-icons">calendar_today</i>
                <span id="current-date">Memuat...</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card queue">
            <div class="stat-header">
                <h5 class="stat-title">Antrian Hari Ini</h5>
                <div class="stat-icon"><i class="material-icons">queue</i></div>
            </div>
            <h2 class="stat-value" id="queue-today">{{ $stats['today_queue'] }}</h2>
        </div>
        <div class="stat-card missed">
            <div class="stat-header">
                <h5 class="stat-title">Terlewat</h5>
                <div class="stat-icon"><i class="material-icons">schedule</i></div>
            </div>
            <h2 class="stat-value" id="missed-count">{{ $stats['missed'] }}</h2>
        </div>
        <div class="stat-card served">
            <div class="stat-header">
                <h5 class="stat-title">Terlayani</h5>
                <div class="stat-icon"><i class="material-icons">check_circle</i></div>
            </div>
            <h2 class="stat-value" id="served-count">{{ $stats['served'] }}</h2>
        </div>
        <div class="stat-card active">
            <div class="stat-header">
                <h5 class="stat-title">Antrian Aktif</h5>
                <div class="stat-icon"><i class="material-icons">hourglass_empty</i></div>
            </div>
            <h2 class="stat-value" id="active-count">{{ $stats['active'] }}</h2>
        </div>
    </div>

    <div class="content-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Perbandingan Antrian: Hari Ini vs Kemarin</h5>
            </div>
            <div class="chart-body">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
        
        <div class="notification-card">
            <div class="notification-header">
                <h5 class="notification-title">Notifikasi</h5>
                <span class="notification-badge">{{ count($notifications) }} Baru</span>
            </div>
            <div class="notification-list">
                @forelse($notifications as $notification)
                <div class="notification-item {{ $notification['type'] }}">
                    <div class="notification-icon"><i class="material-icons">{{ $notification['icon'] }}</i></div>
                    <div class="notification-text">
                        <h6>{{ $notification['title'] }}</h6>
                        <p>{{ $notification['message'] }}</p>
                        <div class="notification-time">{{ $notification['time'] }}</div>
                    </div>
                </div>
                @empty
                <div class="notification-item">
                    <div class="notification-icon"><i class="material-icons">notifications_none</i></div>
                    <div class="notification-text">
                        <h6>Tidak ada notifikasi</h6>
                        <p>Tidak ada aktivitas terbaru</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2025 Sistem Antrian QueueMaster. Hak Cipta Dilindungi.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== DATE DISPLAY =====
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = new Date().toLocaleDateString('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        });
    }
    
    // ===== CHART INITIALIZATION =====
    const ctx = document.getElementById('comparisonChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'Hari Ini',
                        data: {!! json_encode($chartData['today']) !!},
                        backgroundColor: 'rgba(67, 97, 238, 0.8)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Kemarin',
                        data: {!! json_encode($chartData['yesterday']) !!},
                        backgroundColor: 'rgba(76, 201, 240, 0.8)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }
});
</script>
@endsection