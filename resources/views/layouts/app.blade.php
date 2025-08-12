<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' : '' }}Antrian Digital PTSP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <style>
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .brand-text h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* Sidebar Content */
        .sidebar-content {
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: calc(100vh - 100px);
        }

        .sidebar-nav {
            flex: 1;
            padding: 0 20px;
        }

        .nav-section {
            margin-bottom: 32px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: block;
            padding-left: 16px;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: #64748b;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            position: relative;
            gap: 12px;
        }

        .nav-link:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .nav-link.active {
            background-color: #6366f1;
            color: white;
        }

        .nav-link.active .nav-icon {
            color: white;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: color 0.2s ease;
        }

        .nav-icon i {
            font-size: 20px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .user-profile:hover {
            background: #f1f5f9;
        }

        .user-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .user-info h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-info p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        .user-menu {
            color: #94a3b8;
        }

        /* Mobile Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #6366f1;
            border: none;
            border-radius: 12px;
            width: 48px;
            height: 48px;
            color: white;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content Adjustment */
        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            background: #f8fafc;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Submenu styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
            padding-left: 20px;
        }
        
        .submenu.active {
            max-height: 500px;
        }
        
        .submenu .nav-link {
            padding-left: 32px;
            font-size: 13px;
        }
        
        .has-submenu > .nav-link::after {
            content: 'expand_more';
            font-family: 'Material Icons';
            margin-left: auto;
            transition: transform 0.3s ease;
        }
        
        .has-submenu.active > .nav-link::after {
            transform: rotate(180deg);
        }

        /* Content Styles */
        .content-wrapper {
            padding: 30px;
        }

        /* Loading Spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Custom button loading state */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <!-- Custom Styles dari halaman -->
    @stack('styles')
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="material-icons">account_balance</i>
                </div>
                <div class="brand-text">
                    <h3>PTSP</h3>
                    <span>Antrian Digital</span>
                </div>
            </div>
        </div>

        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">MENU UTAMA</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') ?? '/dashboard' }}">
                                <div class="nav-icon"><i class="material-icons">dashboard</i></div>
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('panggilan.admin') ?? '/panggilan/admin' }}">
                                <div class="nav-icon"><i class="material-icons">call</i></div>
                                <span>Panggilan</span>
                            </a>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#">
                                <div class="nav-icon"><i class="material-icons">business</i></div>
                                <span>Master Data</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item"><a class="nav-link" href="/departemen"><span>Departemen</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="/loket"><span>Loket</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="/pelayanan"><span>Layanan</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">LAPORAN</span>
                    <ul class="nav-list">
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#">
                                <div class="nav-icon"><i class="material-icons">queue</i></div>
                                <span>Manajemen Antrian</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item"><a class="nav-link" href="/antrian/daftar"><span>Daftar Antrian</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="/antrian/aktif"><span>Antrian Aktif</span></a></li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#">
                                <div class="nav-icon"><i class="material-icons">bar_chart</i></div>
                                <span>Statistik</span>
                            </a>
                             <ul class="submenu">
                                <li class="nav-item"><a class="nav-link" href="/statistik/harian"><span>Harian</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="/statistik/mingguan"><span>Mingguan</span></a></li>
                                <li class="nav-item"><a class="nav-link" href="/statistik/bulanan"><span>Bulanan</span></a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/laporan/terlewat">
                                <div class="nav-icon"><i class="material-icons">schedule</i></div>
                                <span>Terlewat / Lembur</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">PENGATURAN</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="/pengguna">
                                <div class="nav-icon"><i class="material-icons">people</i></div>
                                <span>Pengguna</span>
                            </a>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#">
                                <div class="nav-icon"><i class="material-icons">settings</i></div>
                                <span>Pengaturan Sistem</span>
                            </a>
                            <ul class="submenu">
                               <li class="nav-item"><a class="nav-link" href="/pengaturan/umum"><span>Pengaturan Umum</span></a></li>
                               <li class="nav-item"><a class="nav-link" href="/pengaturan/tampilan"><span>Tampilan</span></a></li>
                               <li class="nav-item"><a class="nav-link" href="/pengaturan/notifikasi"><span>Notifikasi</span></a></li>
                           </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=Admin+PTSP&background=6366f1&color=fff" alt="Admin">
                    </div>
                    <div class="user-info">
                        <h4>{{ Auth::user()->name ?? 'Admin PTSP' }}</h4>
                        <p>{{ Auth::user()->role ?? 'Administrator' }}</p>
                    </div>
                    <div class="user-menu">
                        <i class="material-icons">more_vert</i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="material-icons">menu</i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <!-- jQuery - WAJIB DI-LOAD PERTAMA -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    // ===============================
    // GLOBAL JAVASCRIPT CONFIGURATION
    // ===============================
    
    // Pastikan jQuery tersedia
    if (typeof $ === 'undefined') {
        console.error('❌ jQuery tidak tersedia! Sistem mungkin tidak berfungsi dengan baik.');
    } else {
        console.log('✅ jQuery berhasil dimuat, versi:', $.fn.jquery);
    }

    // Setup CSRF token untuk semua AJAX request
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        console.log('✅ CSRF token berhasil dikonfigurasi untuk AJAX');
    }

    // Setup Toastr options
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        console.log('✅ Toastr berhasil dikonfigurasi');
    } else {
        console.warn('⚠️ Toastr tidak tersedia, notifikasi akan menggunakan alert standar');
    }

    // ===============================
    // SIDEBAR FUNCTIONALITY
    // ===============================
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const submenuItems = document.querySelectorAll('.has-submenu');
        
        // Fungsi untuk membuka/menutup sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners untuk sidebar
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
        
        // Menangani submenu
        submenuItems.forEach(item => {
            const link = item.querySelector('.nav-link');
            const submenu = item.querySelector('.submenu');

            if (link && submenu) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Tutup submenu lain
                    submenuItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherSubmenu = otherItem.querySelector('.submenu');
                            if (otherSubmenu) {
                                otherSubmenu.classList.remove('active');
                            }
                        }
                    });
                    
                    // Toggle submenu saat ini
                    item.classList.toggle('active');
                    submenu.classList.toggle('active');
                });
            }
        });

        // Set active menu berdasarkan URL
        function setActiveMenu() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');

                // Reset semua status aktif
                link.classList.remove('active');
                const parentSubmenu = link.closest('.has-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.remove('active');
                    const submenu = parentSubmenu.querySelector('.submenu');
                    if (submenu) {
                        submenu.classList.remove('active');
                    }
                }

                // Set aktif jika path cocok
                if (linkPath && linkPath !== '#' && currentPath.startsWith(linkPath)) {
                    link.classList.add('active');
                    
                    // Buka parent submenu jika ada
                    const closestParent = link.closest('.has-submenu');
                    if (closestParent) {
                        closestParent.classList.add('active');
                        const submenu = closestParent.querySelector('.submenu');
                        if (submenu) {
                            submenu.classList.add('active');
                        }
                    }
                }
            });
            
            // Kasus khusus untuk dashboard
            if (currentPath === '/dashboard' || currentPath === '/') {
                const dashboardLink = document.querySelector('a[href*="/dashboard"]');
                if (dashboardLink) {
                    dashboardLink.classList.add('active');
                }
            }
        }
        
        // Tutup sidebar dengan ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Inisialisasi menu aktif
        setActiveMenu();
        console.log('✅ Sidebar berhasil diinisialisasi');
    });

    // ===============================
    // GLOBAL HELPER FUNCTIONS
    // ===============================
    
    // Helper function untuk notifikasi yang kompatibel
    window.showNotification = function(type, message, title = '') {
        if (typeof toastr !== 'undefined') {
            switch(type.toLowerCase()) {
                case 'success':
                    toastr.success(message, title);
                    break;
                case 'error':
                case 'danger':
                    toastr.error(message, title);
                    break;
                case 'warning':
                    toastr.warning(message, title);
                    break;
                case 'info':
                    toastr.info(message, title);
                    break;
                default:
                    toastr.info(message, title);
            }
        } else {
            // Fallback ke alert biasa
            alert((title ? title + ': ' : '') + message);
        }
    };

    // Helper function untuk button loading state
    window.setButtonLoading = function(button, isLoading, originalText = null) {
        const $btn = $(button);
        
        if (isLoading) {
            if (!originalText) {
                originalText = $btn.html();
                $btn.data('original-text', originalText);
            }
            $btn.prop('disabled', true)
                .addClass('btn-loading')
                .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
        } else {
            const savedText = originalText || $btn.data('original-text') || 'Submit';
            $btn.prop('disabled', false)
                .removeClass('btn-loading')
                .html(savedText);
        }
    };

    // Helper function untuk AJAX error handling
    window.handleAjaxError = function(xhr, defaultMessage = 'Terjadi kesalahan pada server') {
        let errorMessage = defaultMessage;
        
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join(', ');
            }
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // Tetap gunakan default message
            }
        }
        
        return errorMessage;
    };

    // Helper function untuk format currency
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    };

    // Helper function untuk format date
    window.formatDate = function(date, format = 'DD/MM/YYYY') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        
        switch(format) {
            case 'DD/MM/YYYY':
                return `${day}/${month}/${year}`;
            case 'YYYY-MM-DD':
                return `${year}-${month}-${day}`;
            case 'DD/MM/YYYY HH:mm':
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            default:
                return d.toLocaleDateString('id-ID');
        }
    };

    // Debug info
    console.log('✅ Layout berhasil dimuat dengan konfigurasi lengkap');
    console.log('🔧 Helper functions tersedia: showNotification, setButtonLoading, handleAjaxError, formatCurrency, formatDate');
    </script>

    <!-- Custom Scripts dari halaman -->
    @stack('scripts')
</body>
</html>