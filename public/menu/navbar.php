<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

        .nav-badge {
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: auto;
        }

        .nav-link.active .nav-badge {
            background: rgba(255, 255, 255, 0.2);
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

        .user-avatar {
            position: relative;
        }

        .user-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }

        .user-status {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }

        .user-info {
            flex: 1;
        }

        .user-info h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.2;
        }

        .user-info p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.2;
        }

        .user-menu {
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .user-menu:hover {
            background: #e2e8f0;
            color: #64748b;
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
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: #5b21b6;
            transform: scale(1.05);
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

        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
            }
            
            .sidebar-toggle {
                width: 44px;
                height: 44px;
                top: 16px;
                left: 16px;
            }
            
            .brand-text h3 {
                font-size: 18px;
            }
            
            .nav-section-title {
                font-size: 10px;
            }
            
            .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }

        /* Animation for nav items */
        .nav-item {
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.3s ease forwards;
        }

        .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .nav-item:nth-child(2) { animation-delay: 0.15s; }
        .nav-item:nth-child(3) { animation-delay: 0.2s; }
        .nav-item:nth-child(4) { animation-delay: 0.25s; }
        .nav-item:nth-child(5) { animation-delay: 0.3s; }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Hover effects */
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: #6366f1;
            border-radius: 0 2px 2px 0;
            transition: height 0.2s ease;
        }

        .nav-link.active::before {
            height: 20px;
        }
        
        /* Badge styles for roles */
        .badge-admin { background-color: #4361ee; }
        .badge-petugas { background-color: #3a0ca3; }
        .badge-user { background-color: #7209b7; }
        
        /* Submenu styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 20px;
        }
        
        .submenu.active {
            max-height: 500px;
        }
        
        .submenu .nav-link {
            padding: 8px 16px 8px 32px;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <!-- Navigasi Utama -->
            <nav class="sidebar-nav">
                <!-- Bagian Menu Utama -->
                <div class="nav-section">
                    <span class="nav-section-title">MENU UTAMA</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard" data-page="dashboard">
                                <div class="nav-icon">
                                    <i class="material-icons">dashboard</i>
                                </div>
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="panggil">
                                <div class="nav-icon">
                                    <i class="material-icons">call</i>
                                </div>
                                <span>Panggilan</span>
                            </a>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#" data-page="master-data">
                                <div class="nav-icon">
                                    <i class="material-icons">business</i>
                                </div>
                                <span>Master Data</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('departemen.index') }}" data-page="departemen">
                                        <div class="nav-icon">
                                            <i class="material-icons">apartment</i>
                                        </div>
                                        <span>Departemen</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="loket">
                                        <div class="nav-icon">
                                            <i class="material-icons">view_module</i>
                                        </div>
                                        <span>Loket</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="layanan">
                                        <div class="nav-icon">
                                            <i class="material-icons">miscellaneous_services</i>
                                        </div>
                                        <span>Layanan</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>

                <!-- Bagian Monitoring / Laporan -->
                <div class="nav-section">
                    <span class="nav-section-title">LAPORAN</span>
                    <ul class="nav-list">
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#" data-page="antrian">
                                <div class="nav-icon">
                                    <i class="material-icons">queue</i>
                                </div>
                                <span>Manajemen Antrian</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="daftar-antrian">
                                        <div class="nav-icon">
                                            <i class="material-icons">list</i>
                                        </div>
                                        <span>Daftar Antrian</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="antrian-aktif">
                                        <div class="nav-icon">
                                            <i class="material-icons">update</i>
                                        </div>
                                        <span>Antrian Aktif</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#" data-page="statistik">
                                <div class="nav-icon">
                                    <i class="material-icons">bar_chart</i>
                                </div>
                                <span>Statistik</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="harian">
                                        <div class="nav-icon">
                                            <i class="material-icons">today</i>
                                        </div>
                                        <span>Harian</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="mingguan">
                                        <div class="nav-icon">
                                            <i class="material-icons">date_range</i>
                                        </div>
                                        <span>Mingguan</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="bulanan">
                                        <div class="nav-icon">
                                            <i class="material-icons">calendar_month</i>
                                        </div>
                                        <span>Bulanan</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="terlewat-lembur">
                                <div class="nav-icon">
                                    <i class="material-icons">schedule</i>
                                </div>
                                <span>Terlewat / Lembur</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Bagian Pengaturan -->
                <div class="nav-section">
                    <span class="nav-section-title">PENGATURAN</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="/pengguna" data-page="pengguna">
                                <div class="nav-icon">
                                    <i class="material-icons">people</i>
                                </div>
                                <span>Pengguna</span>
                            </a>
                        </li>
                        <li class="nav-item has-submenu">
                            <a class="nav-link" href="#" data-page="pengaturan">
                                <div class="nav-icon">
                                    <i class="material-icons">settings</i>
                                </div>
                                <span>Pengaturan Sistem</span>
                            </a>
                            <ul class="submenu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="umum">
                                        <div class="nav-icon">
                                            <i class="material-icons">tune</i>
                                        </div>
                                        <span>Pengaturan Umum</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="tampilan">
                                        <div class="nav-icon">
                                            <i class="material-icons">palette</i>
                                        </div>
                                        <span>Tampilan</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-page="notifikasi">
                                        <div class="nav-icon">
                                            <i class="material-icons">notifications</i>
                                        </div>
                                        <span>Notifikasi</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Profil Pengguna -->
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=Admin+PTSP&background=6366f1&color=fff" alt="Admin">
                        <div class="user-status"></div>
                    </div>
                    <div class="user-info">
                        <h4>Admin PTSP</h4>
                        <p>Administrator</p>
                    </div>
                    <div class="user-menu">
                        <i class="material-icons">more_vert</i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Toggle Sidebar (untuk mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="material-icons">menu</i>
    </button>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navLinks = document.querySelectorAll('.nav-link');
    const submenuItems = document.querySelectorAll('.has-submenu');
    
    // Toggle sidebar on mobile
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Handle submenu toggle
    submenuItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        const submenu = item.querySelector('.submenu');
        
        link.addEventListener('click', function(e) {
            // Jika link ini adalah toggle submenu (href="#")
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                
                // Toggle submenu
                item.classList.toggle('active');
                submenu.classList.toggle('active');
                
                // Close other submenus
                submenuItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.submenu').classList.remove('active');
                    }
                });
            }
        });
    });
    
    // Navigation handling - HANYA untuk visual feedback
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Jika ini adalah link navigasi sebenarnya (punya href yang valid)
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                // Biarkan navigasi normal terjadi
                // Hanya update tampilan active untuk feedback visual
                
                // Remove active dari semua links
                navLinks.forEach(l => l.classList.remove('active'));
                
                // Add active ke link yang diklik
                this.classList.add('active');
                
                // Close sidebar on mobile after navigation
                if (window.innerWidth <= 1024) {
                    setTimeout(closeSidebar, 100);
                }
            }
        });
    });
    
    // Set active menu based on current URL
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        
        navLinks.forEach(link => {
            const linkPath = link.getAttribute('href');
            
            // Reset semua active states
            link.classList.remove('active');
            
            // Cek apakah link ini sesuai dengan path saat ini
            if (linkPath && linkPath !== '#') {
                if (currentPath === linkPath || 
                    (currentPath.startsWith(linkPath) && linkPath !== '/')) {
                    
                    link.classList.add('active');
                    
                    // Jika link ada di submenu, buka submenu parent
                    const submenuParent = link.closest('.has-submenu');
                    if (submenuParent) {
                        submenuParent.classList.add('active');
                        const submenu = submenuParent.querySelector('.submenu');
                        if (submenu) submenu.classList.add('active');
                    }
                }
            }
        });
    }
    
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeSidebar();
        }
    });
    
    // Set active menu on page load
    setActiveMenu();
});
</script>
</body>
</html>