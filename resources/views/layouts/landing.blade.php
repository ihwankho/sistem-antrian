<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-PTSP Pengadilan Negeri Banyuwangi</title>

    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <link rel="stylesheet" href="{{ asset('css/layouts/layout.css') }}" data-turbo-track="reload">

    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="{{ route('landing.page') }}" class="logo">
                <div class="icon-logo">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo" class="icon-logo">
                </div>
                <span>E-PTSP Pengadilan Negeri Banyuwangi</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('landing.page') }}#home">Beranda</a></li>
                <li><a href="{{ route('landing.page') }}#antrian">Monitor</a></li>
                <li><a href="{{ route('landing.page') }}#cari-tiket">Cari Tiket</a></li>
                <li><a href="{{ route('landing.page') }}#petugas">Tim Kami</a></li>
                <li><a href="{{ route('landing.page') }}#layanan">Layanan</a></li>
                <li><a href="{{ route('antrian.pilih-layanan') }}">Ambil Tiket</a></li>
            </ul>

            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="mobile-menu" id="mobile-menu">
        <button class="mobile-menu-close" id="mobile-menu-close">
            <i class="fas fa-times"></i>
        </button>
        <ul class="nav-links">
            <li><a href="{{ route('landing.page') }}#home" class="mobile-menu-link">Beranda</a></li>
            <li><a href="{{ route('landing.page') }}#antrian" class="mobile-menu-link">Monitor</a></li>
            <li><a href="{{ route('landing.page') }}#cari-tiket" class="mobile-menu-link">Cari Tiket</a></li>
            <li><a href="{{ route('landing.page') }}#petugas" class="mobile-menu-link">Tim Kami</a></li>
            <li><a href="{{ route('landing.page') }}#layanan" class="mobile-menu-link">Layanan</a></li>
            <li><a href="{{ route('antrian.pilih-layanan') }}" class="mobile-menu-link">Ambil Tiket</a></li>
        </ul>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-content footer-content-gap">
                <div class="footer-column footer-info">
                    <h3>Pengadilan Negeri Banyuwangi</h3>
                    <p>Pengadilan Negeri Banyuwangi Kelas IA</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Adi Sucipto No.26, Taman Baru, Kec. Banyuwangi, Kabupaten Banyuwangi, Jawa Timur 68416</p>
                    <p><i class="fas fa-phone"></i> (0333) 421376</p>
                    <p><i class="fas fa-envelope"></i> pnbanyuwangi@gmail.com</p>
                </div>
                <div class="footer-column footer-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.196377033282!2d114.35422267597199!3d-8.182431881776953!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd15b0205d55555%3A0x2333fde188f45648!2sPengadilan%20Negeri%20Banyuwangi!5e0!3m2!1sen!2sid!4v1694070659104!5m2!1sen!2sid" width="100%" height="250" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">© {{ date('Y') }} Pengadilan Negeri Banyuwangi Kelas IA. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        function setupMobileMenu() {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('mobile-menu-close');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileLinks = document.querySelectorAll('.mobile-menu-link');

            if (!menuBtn || !closeBtn || !mobileMenu) return;

            const openMenu = () => {
                mobileMenu.classList.add('active');
                document.body.style.overflow = 'hidden';
            };
            const closeMenu = () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            };

            menuBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenu);
            mobileLinks.forEach(link => {
                link.addEventListener('click', closeMenu);
            });
        }
        document.addEventListener('turbo:load', setupMobileMenu);
        document.addEventListener('DOMContentLoaded', setupMobileMenu);
    </script>

    @stack('scripts')
</body>
</html>