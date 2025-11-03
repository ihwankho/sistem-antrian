<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' : '' }}Antrian Digital PTSP</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    
    {{-- Ini untuk CSS spesifik per halaman (misal: departemen.css) --}}
    @stack('styles')
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="icon-logo ">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo" class="icon-logo">
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
                        @if(in_array(Auth::user()->role, [1, 2]))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') ?? '/dashboard' }}">
                                <div class="nav-icon"><i class="material-icons">dashboard</i></div>
                                <span>Beranda</span>
                            </a>
                        </li>
                        @endif
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('panggilan.admin') ?? '/panggilan/admin' }}">
                                <div class="nav-icon"><i class="material-icons">call</i></div>
                                <span>Panggilan</span>
                            </a>
                        </li>
                        
                        @if(Auth::user()->role === 1)
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
                        @endif
                    </ul>
                </div>
                
                @if(in_array(Auth::user()->role, [1, 2]))
                <div class="nav-section">
                    <span class="nav-section-title">LAPORAN</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="/reports/activity">
                                <div class="nav-icon"><i class="material-icons">summarize</i></div>
                                <span>Laporan Aktivitas</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
                
                @if(Auth::user()->role === 1)
                <div class="nav-section">
                    <span class="nav-section-title">PENGATURAN</span>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a class="nav-link" href="/pengguna">
                                <div class="nav-icon"><i class="material-icons">people</i></div>
                                <span>Pengguna</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('display-settings.index') }}">
                                <div class="nav-icon"><i class="material-icons">settings_brightness</i></div>
                                <span>Setting Tampilan</span>
                            </a>
                        </li>
                        </ul>
                </div>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile" id="userProfileDropdown">
                    <div class="user-avatar">
                        @if(Auth::user()->foto)
                            <img src="{{ asset(Auth::user()->foto) }}" alt="{{ Auth::user()->name }}" class="user-avatar">
                        @else
                            <div class="user-avatar-placeholder">
                                <i class="material-icons" style="font-size: 20px;">person</i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="user-info">
                        <p>
                            @if(Auth::user()->role === 1)
                                Admin PTSP
                            @elseif(Auth::user()->role === 2)
                                Petugas PTSP
                            @else
                                Pengguna
                            @endif
                        </p>
                    </div>
                    <div class="user-menu">
                        <i class="material-icons">more_vert</i>
                    </div>
                </div>

                <div class="user-dropdown-menu" id="userDropdownMenu">
                    
                    {{-- Tombol Profil HANYA untuk Petugas (Role 2) --}}
                    @if(Auth::user()->role == 2)
                        <a href="{{ route('profil.edit') }}" class="dropdown-item">
                            <i class="material-icons">account_circle</i> Profil Saya
                        </a>
                    @endif

                    {{-- Tombol Logout untuk semua role --}}
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="material-icons">exit_to_app</i> Logout
                        </button>
                    </form>
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

    {{-- SEMUA SCRIPT TETAP DI SINI SESUAI PERMINTAAN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    // ... (Semua JavaScript Anda biarkan sama persis) ...
    // ===============================
    // GLOBAL JAVASCRIPT CONFIGURATION
    // ===============================
    
    if (typeof $ === 'undefined') {
        console.error('❌ jQuery tidak tersedia! Sistem mungkin tidak berfungsi dengan baik.');
    } else {
        console.log('✅ jQuery berhasil dimuat, versi:', $.fn.jquery);
    }

    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        console.log('✅ CSRF token berhasil dikonfigurasi untuk AJAX');
    }

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
        const userProfileDropdown = document.getElementById('userProfileDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
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

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
        
        submenuItems.forEach(item => {
            const link = item.querySelector('.nav-link');
            const submenu = item.querySelector('.submenu');

            if (link && submenu) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    submenuItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherSubmenu = otherItem.querySelector('.submenu');
                            if (otherSubmenu) {
                                otherSubmenu.classList.remove('active');
                            }
                        }
                    });
                    item.classList.toggle('active');
                    submenu.classList.toggle('active');
                });
            }
        });

        if (userProfileDropdown && userDropdownMenu) {
            userProfileDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (userDropdownMenu.classList.contains('active') && 
                    !userProfileDropdown.contains(e.target) && 
                    !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('active');
                }
            });
        }

        function setActiveMenu() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                link.classList.remove('active');
                const parentSubmenu = link.closest('.has-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.remove('active');
                    const submenu = parentSubmenu.querySelector('.submenu');
                    if (submenu) {
                        submenu.classList.remove('active');
                    }
                }

                if (linkPath && linkPath !== '#' && currentPath.startsWith(linkPath)) {
                    link.classList.add('active');
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
            
            if (currentPath === '/dashboard' || currentPath === '/') {
                const dashboardLink = document.querySelector('a[href*="/dashboard"]');
                if (dashboardLink) {
                    dashboardLink.classList.add('active');
                }
            }
        }
        
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        setActiveMenu();
        console.log('✅ Sidebar berhasil diinisialisasi');
    });

    // ===============================
    // GLOBAL HELPER FUNCTIONS
    // ===============================
    
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
            alert((title ? title + ': ' : '') + message);
        }
    };

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

    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    };

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

    console.log('✅ Layout berhasil dimuat dengan konfigurasi lengkap');
    console.log('🔧 Helper functions tersedia: showNotification, setButtonLoading, handleAjaxError, formatCurrency, formatDate');
    </script>

    @stack('scripts')
    
    <script>
        // ===============================
        // GLOBAL JAVASCRIPT CONFIGURATION
        // ===============================
        
        function handleApiError(error) {
            if (error.status === 401 || error.status === 403) {
                window.showNotification('error', 'Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                window.showNotification('error', 'Terjadi kesalahan pada server: ' + error.statusText);
            }
        }
        
        if (typeof $ !== 'undefined') {
            $(document).ajaxComplete(function(event, xhr, settings) {
                if (xhr.status === 401 || xhr.status === 403) {
                    handleApiError(xhr);
                }
            });
        }
        
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args)
                .then(response => {
                    if (response.status === 401 || response.status === 403) {
                        handleApiError({
                            status: response.status,
                            statusText: response.statusText
                        });
                    }
                    return response;
                });
        };
        </script>
</body>
</html>