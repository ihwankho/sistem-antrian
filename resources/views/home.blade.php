@extends('layouts.landing')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
{{-- Tambahkan Font Awesome jika belum ada --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
/* ... (Style modal Anda sudah benar) ... */
.modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
/* .modal.active { display: flex; } <-- Ini sudah ada di home.css */
/* .modal-content { ... } <-- Ini sudah ada di home.css */
/* .modal-header { ... } <-- Ini sudah ada di home.css */
/* .modal-close { ... } <-- Ini sudah ada di home.css */
/* .modal-body { ... } <-- Ini sudah ada di home.css */

/* Style-style modal di bawah ini sudah ada di home.css (di-copy dari blade Anda sebelumnya).
   Anda bisa menghapusnya dari sini jika sudah yakin ada di home.css.
   Saya tetap biarkan di sini untuk berjaga-jaga jika ada perbedaan.
*/
.modal.active { display: flex; }
.modal-content { background-color: #fefefe; margin: auto; padding: 0; border: 1px solid #888; width: 90%; max-width: 500px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19); animation-name: animatetop; animation-duration: 0.4s; border-radius: 8px; overflow: hidden; }
.modal-header { padding: 15px 20px; background-color: #6366f1; color: white; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; }
.modal-header h3 { margin: 0; font-size: 1.25rem; }
.modal-close { color: white; font-size: 28px; font-weight: bold; background: none; border: none; cursor: pointer; line-height: 1; }
.modal-close:hover, .modal-close:focus { color: #eee; text-decoration: none; }
.modal-body { padding: 20px; }
#ticket-list-container a { display: block; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; text-decoration: none; color: #333; transition: background-color 0.2s ease; }
#ticket-list-container a:hover { background-color: #f5f5f5; }
.tiket-nomor { font-weight: bold; font-size: 1.1rem; margin-bottom: 5px; color: #6366f1; }
.tiket-detail { font-size: 0.9rem; color: #555; }
@keyframes animatetop { from {top: -300px; opacity: 0} to {top: 0; opacity: 1} }
.search-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
.stat-time.serving { color: #ffffff; font-weight: bold; }
</style>
@endpush

@section('content')
<main>
    {{-- BAGIAN HERO --}}
    <section id="home" class="hero">
        <div class="container-fluid">
            <div class="hero-content-wrapper">
                <div class="hero-text">
                    <h1>Selamat Datang di E-PTSP Pengadilan Negeri Banyuwangi</h1>
                    <p>Kami akan memberikan pelayanan terbaik untuk Anda dengan sistem digital yang modern, efisien, dan mudah digunakan.</p>
                    <a href="{{ route('antrian.pilih-layanan') }}" class="cta-button">
                        <i class="fas fa-ticket-alt"></i>
                        Ambil Nomor Antrean
                    </a>
                </div>
                <div class="hero-image-container">
                    {{-- INGAT UNTUK MENGKOMPRES GAMBAR INI SECARA MANUAL --}}
                    <img src="{{ asset('images/foto-gedung.png') }}" alt="Gedung Pengadilan Negri Banyuwangi" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    {{-- BAGIAN MONITOR ANTRIAN --}}
    <section id="antrian" class="stats">
        <div class="container">
            <h2 class="section-title">Antrian Saat Ini</h2>
            <p class="section-subtitle" id="avg-time-subtitle">Memuat data estimasi...</p>
            <div class="stats-container" id="stats-container">
                @forelse($lokets as $index => $nama_loket)
                    @php $kodeHuruf = chr(65 + $index); @endphp
                    <div class="stat-card" data-loket-name="{{ $nama_loket }}" data-default-code="{{ $kodeHuruf }}-000">
                        <div class="loket-number">{{ $nama_loket }}</div>
                        <div class="stat-number">{{ $kodeHuruf }}-000</div>
                        <div class="stat-text">Tidak ada antrian</div>
                        <div class="stat-time">Siap melayani</div>
                    </div>
                @empty
                    <p class="text-center" style="grid-column: 1 / -1;">Data loket tidak dapat dimuat saat ini.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- BAGIAN CARI TIKET --}}
    <section id="cari-tiket" class="search-ticket-section">
        <div class="container">
            <h2 class="section-title">Cari Tiket Antrian Anda</h2>
            <p class="section-subtitle">Masukkan Nomor Telepon yang Anda daftarkan untuk melihat kembali tiket Anda hari ini.</p>

            <div id="search-error-container" class="search-error" style="display: none;"></div>

            <form id="search-ticket-form" action="{{ route('antrian.api.cari') }}" method="POST" class="search-form" novalidate>
                @csrf
                <input
                    type="tel"
                    id="no-hp-input"
                    name="no_hp"
                    class="search-input"
                    placeholder="Masukkan Nomor Telepon Anda (Contoh: 0812...)"
                    required
                    minlength="10"
                    maxlength="13"
                    pattern="^[0-9]{10,13}$"
                    title="Nomor telepon harus terdiri dari 10 hingga 13 digit angka.">

                <button type="submit" class="search-button" id="search-button-submit">
                    <i class="fas fa-search"></i>
                    <span>Cari Tiket</span>
                </button>
            </form>
        </div>
    </section>

    {{-- HTML untuk Modal Pop-up Pencarian Tiket --}}
    <div id="ticket-selection-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Beberapa Tiket Ditemukan</h3>
                <button id="modal-close-button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Silakan pilih salah satu tiket untuk melihat detailnya.</p>
                <div id="ticket-list-container"></div>
            </div>
        </div>
    </div>

    {{-- [PERBAIKAN DI SINI] --}}
    {{-- BAGIAN PETUGAS (Dengan Container Baru) --}}
    <section id="petugas" class="leadership-section">
        <div class="container">
            <h2 class="section-title">Petugas Pelayanan Terpadu Satu Pintu</h2>
            {{-- Container Baru Ditambahkan --}}
            <div class="leadership-grid-container">
                {{-- Konten di dalam div ini akan di-generate oleh JavaScript --}}
                <div class="leadership-grid" id="petugas-grid-container">
                    {{-- @forelse($petugas...) Dihapus dari sini --}}
                </div>
            </div> {{-- Akhir leadership-grid-container --}}
        </div>
    </section>

    {{-- BAGIAN LAYANAN --}}
    <section id="layanan" class="about-ptsp-section">
        <div class="container">
            <div class="about-content">
                <div class="image-box">
                    <img src="{{ asset('images/foto-ptsp.jpg') }}" alt="Ruang Pelayanan PTSP">
                </div>
                <div class="text-content">
                    <h2>Layanan Tersedia</h2>
                    <p>Silakan pilih loket untuk melihat detail layanan yang kami sediakan.</p>

                    <ul class="pelayanan-list">
                        @forelse($departemens as $departemen)
                            <li>
                                <a href="#" class="loket-trigger"
                                   data-loket-name="{{ $departemen->nama_departemen }}"
                                   data-layanan='@json($departemen->pelayanans->pluck('nama_layanan'))'>
                                    <i class="fas fa-check-circle"></i>
                                    <span>{{ $departemen->nama_departemen }}</span>
                                </a>
                            </li>
                        @empty
                            <li>Informasi layanan belum tersedia.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL UNTUK POP-UP LAYANAN --}}
    <div id="layananModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalLoketName">Nama Loket</h3>
                <button id="modalCloseBtn" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <ul id="modalLayananList"></ul>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ============================================
        // === LOGIKA BARU UNTUK SECTION PETUGAS ===
        // ============================================
        
        /**
         * [PERBAIKAN DI SINI]
         * Mengambil data petugas ASLI dari controller Laravel
         * yang sudah di-passing sebagai $petugasGrouped
         */
        const dataPetugas = @json($petugasGrouped);

        /**
         * Inisialisasi section petugas
         */
        function initializePetugasSection() {
            const gridContainer = document.getElementById('petugas-grid-container');
            if (!gridContainer) {
                console.warn('Elemen #petugas-grid-container tidak ditemukan.');
                return;
            }

            // Kosongkan kontainer
            gridContainer.innerHTML = '';
            
            // Cek jika dataPetugas kosong
            if (Object.keys(dataPetugas).length === 0) {
                 gridContainer.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: white;">Informasi petugas belum tersedia.</p>';
                 return;
            }

            // Loop data petugas (yang sudah terkelompok) dan buat card
            Object.entries(dataPetugas).forEach(([loketName, officers]) => {
                if (officers.length === 0) return; // Lewati jika tidak ada petugas

                // 1. Buat elemen card
                const card = document.createElement('div');
                card.className = 'leader-card';

                // 2. Buat elemen foto
                const photoDiv = document.createElement('div');
                photoDiv.className = 'leader-photo';
                
                let hasValidPhoto = false;

                officers.forEach((officer, index) => {
                    if (officer.foto) {
                        hasValidPhoto = true;
                        const img = document.createElement('img');
                        img.src = officer.foto;
                        img.alt = officer.nama;
                        // Foto pertama yang valid akan langsung aktif
                        if (index === 0) {
                            img.classList.add('active');
                        }
                        photoDiv.appendChild(img);
                    }
                });
                
                // Jika tidak ada foto sama sekali di grup ini
                if (!hasValidPhoto) {
                    photoDiv.classList.add('no-image');
                    photoDiv.innerHTML = '<span style="font-size: 2.5em; line-height: 1;">?</span>'; // Ganti ? dengan ikon jika mau
                }

                card.appendChild(photoDiv);

                // 3. Buat nama loket
                const nameH3 = document.createElement('h3');
                nameH3.className = 'leader-name';
                nameH3.textContent = loketName;
                card.appendChild(nameH3);
                
                // 4. Buat nama petugas (posisi)
                const positionP = document.createElement('p');
                positionP.className = 'leader-position';
                positionP.textContent = officers[0].nama; // Tampilkan nama petugas pertama
                card.appendChild(positionP);
                
                // 5. Tambahkan card ke grid
                gridContainer.appendChild(card);
                
                // 6. Tambahkan event listener HANYA jika petugas > 1
                if (officers.length > 1) {
                    addPetugasRotationEvents(card, officers);
                }
            });
        }

        /**
         * Menambahkan event listener rotasi foto pada card
         */
        function addPetugasRotationEvents(card, officers) {
            let rotateInterval = null; // Menyimpan ID interval
            let currentIndex = 0;
            
            const images = card.querySelectorAll('.leader-photo img');
            const positionEl = card.querySelector('.leader-position');
            
            // Filter dulu petugas yang punya foto
            const officersWithPhoto = officers.filter(o => o.foto);
            const imagesWithPhoto = Array.from(images); // Convert NodeList to Array
            
            // Jika tidak ada gambar, atau hanya 1 gambar, jangan lakukan rotasi
            if (imagesWithPhoto.length <= 1) return;

            // Saat mouse masuk (hover)
            card.addEventListener('mouseenter', () => {
                // Hentikan interval lama jika ada
                if (rotateInterval) clearInterval(rotateInterval);

                rotateInterval = setInterval(() => {
                    const nextIndex = (currentIndex + 1) % officersWithPhoto.length;
                    
                    // Ganti foto
                    imagesWithPhoto[currentIndex].classList.remove('active');
                    imagesWithPhoto[nextIndex].classList.add('active');
                    
                    // Ganti nama
                    positionEl.textContent = officersWithPhoto[nextIndex].nama;
                    
                    currentIndex = nextIndex;
                }, 1200); // 800ms = 0.8 detik
            });

            // Saat mouse keluar
            card.addEventListener('mouseleave', () => {
                // Hentikan interval rotasi
                clearInterval(rotateInterval);
                rotateInterval = null;
                
                // Reset ke foto/nama pertama (sesuai permintaan)
                if (imagesWithPhoto.length > 0) {
                     imagesWithPhoto[currentIndex].classList.remove('active');
                     imagesWithPhoto[0].classList.add('active');
                     positionEl.textContent = officersWithPhoto[0].nama;
                }
                
                currentIndex = 0; // Reset index
            });
        }

        // Panggil fungsi inisialisasi petugas
        initializePetugasSection();

        
        // ============================================
        // === KODE JAVASCRIPT ANDA YANG SUDAH ADA ===
        // ============================================

        // Queue update logic
        function updateQueueInfo() {
             fetch('/api/antrian_all', { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
             .then(response => response.json())
             .then(data => {
                if (!data.status) return;
                const allCards = document.querySelectorAll('.stat-card');
                const avgTime = data.avg_service_time || 7;
                const subtitle = document.getElementById('avg-time-subtitle');
                if (subtitle) { subtitle.textContent = `Rata-rata waktu pelayanan per antrian hari ini: ~${avgTime} menit`; }
                allCards.forEach(card => { /* Reset card */
                    card.querySelector('.stat-number').textContent = card.dataset.defaultCode;
                    card.querySelector('.stat-text').textContent = 'Tidak ada antrian';
                    const statTime = card.querySelector('.stat-time');
                    statTime.textContent = 'Siap melayani';
                    statTime.classList.remove('serving');
                });
                if (data.data && data.data.length > 0) {
                    data.data.forEach(loketData => { /* Update card based on API */
                        const card = document.querySelector(`.stat-card[data-loket-name="${loketData.loket}"]`);
                        if (!card) return;
                        const antrianAktif = loketData.antrian?.find(a => a.status_antrian == 2);
                        const antrianMenunggu = loketData.antrian?.filter(a => a.status_antrian == 1);
                        const totalMenunggu = antrianMenunggu?.length || 0;
                        const statTime = card.querySelector('.stat-time');
                        if (antrianAktif) { /* Sedang dilayani */
                            card.querySelector('.stat-number').textContent = antrianAktif.kode_antrian;
                            card.querySelector('.stat-text').textContent = 'Sedang Dilayani';
                            statTime.textContent = 'Masuk Loket';
                            statTime.classList.add('serving');
                        } else if (totalMenunggu > 0) { /* Ada yang menunggu */
                            card.querySelector('.stat-number').textContent = antrianMenunggu[0].kode_antrian;
                            card.querySelector('.stat-text').textContent = `${totalMenunggu} antrian menunggu`;
                            const estimasiMenit = totalMenunggu * avgTime;
                            statTime.textContent = `Estimasi: ${estimasiMenit} menit`;
                            statTime.classList.remove('serving');
                        }
                    });
                }
             }).catch(error => console.error('Gagal mengambil data antrian:', error));
        }
        // Layanan modal logic
        const layananModal = document.getElementById('layananModal');
        const layananModalCloseBtn = document.getElementById('modalCloseBtn');
        const loketTriggers = document.querySelectorAll('.loket-trigger');
        const modalLoketName = document.getElementById('modalLoketName');
        const modalLayananList = document.getElementById('modalLayananList');
        if(layananModal && loketTriggers.length > 0) { /* ... event listeners for layanan modal ... */
             loketTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const loketName = this.dataset.loketName;
                    const layananJson = this.dataset.layanan;
                    if (!layananJson) return;
                    try {
                        const layananList = JSON.parse(layananJson);
                        modalLoketName.textContent = `Layanan di ${loketName}`;
                        modalLayananList.innerHTML = '';
                        if (layananList.length > 0) {
                            layananList.forEach(layanan => { const li = document.createElement('li'); li.textContent = layanan; modalLayananList.appendChild(li); });
                        } else { const li = document.createElement('li'); li.textContent = 'Tidak ada detail layanan.'; modalLayananList.appendChild(li); }
                        layananModal.classList.add('active');
                    } catch (error) { console.error("Gagal parsing data layanan:", error); }
                });
            });
            const closeLayananModal = () => layananModal.classList.remove('active');
            if(layananModalCloseBtn) layananModalCloseBtn.addEventListener('click', closeLayananModal);
            layananModal.addEventListener('click', (e) => { if (e.target === layananModal) { closeLayananModal(); } });
        }

        // Search ticket form logic
        const searchForm = document.getElementById('search-ticket-form');
        const noHpInput = document.getElementById('no-hp-input');
        const errorContainer = document.getElementById('search-error-container');
        if (searchForm) { /* ... event listener for search form submit ... */
             noHpInput.addEventListener('input', function(e) { e.target.value = e.target.value.replace(/\D/g, ''); });
             searchForm.addEventListener('submit', function(e) { /* Fetch API logic */
                 e.preventDefault(); errorContainer.style.display = 'none'; const noHp = noHpInput.value;
                 if (!/^[0-9]{10,13}$/.test(noHp)) { errorContainer.textContent = 'No HP 10-13 digit.'; errorContainer.style.display = 'block'; noHpInput.focus(); return; }
                 const formData = new FormData(); formData.append('no_hp', noHp); formData.append('_token', '{{ csrf_token() }}');
                 fetch('{{ route("antrian.api.cari") }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: formData })
                 .then(response => response.json()).then(data => {
                     if (data.success && data.tickets) {
                         if (data.tickets.length === 1) { window.location.href = data.tickets[0].url; return; }
                         ticketListContainer.innerHTML = ''; data.tickets.forEach(ticket => { /* Create ticket links */
                             const link = document.createElement('a'); link.href = ticket.url; link.classList.add('ticket-option');
                             const noDiv = document.createElement('div'); noDiv.className = 'tiket-nomor'; noDiv.textContent = ticket.nomor_lengkap; link.appendChild(noDiv);
                             const layDiv = document.createElement('div'); layDiv.className = 'tiket-detail'; layDiv.innerHTML = `<strong>Layanan:</strong> ${ticket.nama_layanan || 'N/A'}`; link.appendChild(layDiv);
                             const lokDiv = document.createElement('div'); lokDiv.className = 'tiket-detail'; lokDiv.innerHTML = `<strong>Loket:</strong> ${ticket.nama_loket || 'N/A'}`; link.appendChild(lokDiv);
                             ticketListContainer.appendChild(link);
                         });
                         ticketModal.classList.add('active');
                     } else { errorContainer.textContent = data.message || 'Tiket tidak ditemukan.'; errorContainer.style.display = 'block'; }
                 }).catch(error => { console.error('Error:', error); errorContainer.textContent = 'Kesalahan server.'; errorContainer.style.display = 'block'; });
             });
        }

        // Ticket selection modal logic
        const ticketModal = document.getElementById('ticket-selection-modal');
        const ticketModalCloseBtn = document.getElementById('modal-close-button');
        const ticketListContainer = document.getElementById('ticket-list-container');
        if (ticketModal && ticketModalCloseBtn) { /* ... event listeners for ticket modal ... */
             const closeTicketModal = () => ticketModal.classList.remove('active');
             ticketModalCloseBtn.addEventListener('click', closeTicketModal);
             ticketModal.addEventListener('click', (e) => { if (e.target === ticketModal) { closeTicketModal(); } });
        }

        updateQueueInfo();
        setInterval(updateQueueInfo, 5000);
    });
</script>
@endpush