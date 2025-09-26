{{-- resources/views/home.blade.php --}}

@extends('layouts.landing')

{{-- Menghubungkan ke file CSS eksternal --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
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
                    <img src="{{ asset('images/foto-gedung.png') }}" alt="Gedung Pengadilan Negri Banyuwangi" loading="lazy">
                </div>
            </div>
        </div>
    </section>


    {{-- BAGIAN MONITOR ANTRIAN --}}
    <section id="antrian" class="stats">
        <div class="container">
            <h2 class="section-title">Antrian Saat Ini</h2>
            {{-- Teks statis diubah menjadi dinamis, akan diisi oleh JavaScript --}}
            <p class="section-subtitle" id="avg-time-subtitle">Memuat data estimasi...</p>
            <div class="stats-container" id="stats-container">
                @forelse($lokets as $index => $nama_loket)
                    @php $kodeHuruf = chr(65 + $index); @endphp
                    <div class="stat-card" data-loket-name="{{ $nama_loket }}" data-default-code="{{ $kodeHuruf }}-000">
                        <div class="loket-number">{{ $nama_loket }}</div>
                        <div class="stat-number">{{ $kodeHuruf }}-000</div>
                        <div class="stat-text">Tidak ada antrian</div>
                        <div class="stat-time">Siap melayani</div> {{-- Elemen ini akan kita ubah --}}
                    </div>
                @empty
                    <p class="text-center" style="grid-column: 1 / -1;">Data loket tidak dapat dimuat saat ini.</p>
                @endforelse
            </div>
        </div>
    </section>
    
    {{-- BAGIAN CARI TIKET (DENGAN LOGIKA MODAL BARU) --}}
    <section id="cari-tiket" class="search-ticket-section">
        <div class="container">
            <h2 class="section-title">Cari Tiket Antrian Anda</h2>
            <p class="section-subtitle">Masukkan NIK yang Anda daftarkan untuk melihat kembali tiket Anda hari ini.</p>
        
            {{-- Tempat untuk menampilkan pesan error dari JavaScript --}}
            <div id="search-error-container" class="search-error" style="display: none;"></div>
        
            {{-- Form diberi ID untuk JavaScript --}}
            <form id="search-ticket-form" action="{{ route('antrian.cari') }}" method="POST" class="search-form">
                @csrf
                <input 
                    type="text" 
                    id="nik-input"
                    name="nik" 
                    class="search-input" 
                    placeholder="Masukkan 16 digit NIK Anda" 
                    required 
                    minlength="16" 
                    maxlength="16" 
                    pattern="\d{16}"
                    title="NIK harus terdiri dari 16 digit angka.">
    
                <button type="submit" class="search-button" id="search-button-submit">
                    <i class="fas fa-search"></i>
                    <span>Cari Tiket</span>
                </button>
            </form>
        </div>
    </section>

    {{-- HTML untuk Modal Pop-up Pencarian Tiket, awalnya tersembunyi --}}
    <div id="ticket-selection-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Beberapa Tiket Ditemukan</h3>
                <button id="modal-close-button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Silakan pilih salah satu tiket untuk melihat detailnya.</p>
                <div id="ticket-list-container">
                    {{-- Daftar tiket akan dimasukkan di sini oleh JavaScript --}}
                </div>
            </div>
        </div>
    </div>


    {{-- BAGIAN PETUGAS --}}
    <section id="petugas" class="leadership-section">
        <div class="container">
            <h2 class="section-title">Petugas Pelayanan Terpadu Satu Pintu</h2>
            <div class="leadership-grid">
                @forelse($petugas as $staff)
                    <div class="leader-card">
                        <div class="leader-photo {{ $staff['foto'] ? '' : 'no-image' }}">
                            @if($staff['foto'])
                                <img src="{{ $staff['foto'] }}" alt="{{ $staff['nama'] }}">
                            @else
                                👤
                            @endif
                        </div>
                        <h3 class="leader-name">{{ $staff['nama'] }}</h3>
                        <p class="leader-position">{{ $staff['nama_loket'] }}</p>
                    </div>
                @empty
                     <p class="text-center" style="grid-column: 1 / -1; color: white;">Informasi petugas belum tersedia.</p>
                @endforelse
            </div>
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
                                   data-layanan="{{ json_encode($departemen->pelayanans->pluck('nama_layanan')) }}">
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
        
        // =======================================================
        // BAGIAN 1: FUNGSI ASLI (MONITOR ANTRIAN & MODAL LAYANAN)
        // =======================================================
        
        function updateQueueInfo() {
            fetch('/api/antrian_all', {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.status) return;

                const allCards = document.querySelectorAll('.stat-card');
                const avgTime = data.avg_service_time || 7; // Ambil rata-rata waktu, default 7 menit
                
                // Update subtitle dengan rata-rata waktu
                const subtitle = document.getElementById('avg-time-subtitle');
                if (subtitle) {
                    subtitle.textContent = `Rata-rata waktu pelayanan per antrian hari ini: ~${avgTime} menit`;
                }

                // Reset semua kartu ke status default
                allCards.forEach(card => {
                    card.querySelector('.stat-number').textContent = card.dataset.defaultCode;
                    card.querySelector('.stat-text').textContent = 'Tidak ada antrian';
                    const statTime = card.querySelector('.stat-time');
                    statTime.textContent = 'Siap melayani';
                    statTime.classList.remove('serving');
                });
                
                // Jika ada data antrian, proses dan update kartu
                if (data.data && data.data.length > 0) {
                    data.data.forEach(loketData => {
                        const card = document.querySelector(`.stat-card[data-loket-name="${loketData.loket}"]`);
                        if (!card) return;
                        
                        const antrianAktif = loketData.antrian?.find(a => a.status_antrian == 2);
                        const antrianMenunggu = loketData.antrian?.filter(a => a.status_antrian == 1);
                        const totalMenunggu = antrianMenunggu?.length || 0;
                        const statTime = card.querySelector('.stat-time');

                        if (antrianAktif) {
                            card.querySelector('.stat-number').textContent = antrianAktif.kode_antrian;
                            card.querySelector('.stat-text').textContent = 'Sedang Dilayani';
                            statTime.textContent = 'Masuk Loket';
                            statTime.classList.add('serving');
                        } else if (totalMenunggu > 0) {
                            card.querySelector('.stat-number').textContent = antrianMenunggu[0].kode_antrian;
                            card.querySelector('.stat-text').textContent = `${totalMenunggu} antrian menunggu`;
                            // Hitung estimasi waktu hanya untuk yang menunggu
                            const estimasiMenit = totalMenunggu * avgTime;
                            statTime.textContent = `Estimasi: ${estimasiMenit} menit`;
                            statTime.classList.remove('serving');
                        }
                    });
                }
            }).catch(error => console.error('Gagal mengambil data antrian:', error));
        }

        const layananModal = document.getElementById('layananModal');
        const layananModalCloseBtn = document.getElementById('modalCloseBtn');
        const loketTriggers = document.querySelectorAll('.loket-trigger');
        const modalLoketName = document.getElementById('modalLoketName');
        const modalLayananList = document.getElementById('modalLayananList');

        if(layananModal && loketTriggers.length > 0) {
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
                            layananList.forEach(layanan => {
                                const li = document.createElement('li');
                                li.textContent = layanan;
                                modalLayananList.appendChild(li);
                            });
                        } else {
                            const li = document.createElement('li');
                            li.textContent = 'Tidak ada detail layanan untuk loket ini.';
                            modalLayananList.appendChild(li);
                        }
                        layananModal.classList.add('active');
                    } catch (error) {
                        console.error("Gagal mem-parsing data layanan:", error);
                    }
                });
            });

            const closeLayananModal = () => layananModal.classList.remove('active');
            layananModalCloseBtn.addEventListener('click', closeLayananModal);
            layananModal.addEventListener('click', (e) => {
                if (e.target === layananModal) {
                    closeLayananModal();
                }
            });
        }
        
        // Jalankan fungsi update monitor antrian
        updateQueueInfo();
        setInterval(updateQueueInfo, 5000); // Refresh setiap 5 detik

        
        // =================================================================
        // BAGIAN 2: FUNGSI BARU (PENCARIAN TIKET VIA NIK DENGAN MODAL)
        // =================================================================

        const searchForm = document.getElementById('search-ticket-form');
        const nikInput = document.getElementById('nik-input');
        const ticketModal = document.getElementById('ticket-selection-modal');
        const ticketModalCloseBtn = document.getElementById('modal-close-button');
        const ticketListContainer = document.getElementById('ticket-list-container');
        const errorContainer = document.getElementById('search-error-container');

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault(); 

                const nik = nikInput.value;
                const formData = new FormData();
                formData.append('nik', nik);
                formData.append('_token', '{{ csrf_token() }}');

                errorContainer.style.display = 'none';

                fetch('{{ route("antrian.api.cari") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.tickets) {
                        if (data.tickets.length === 1) {
                            window.location.href = data.tickets[0].url;
                            return;
                        }
                        
                        ticketListContainer.innerHTML = '';
                        data.tickets.forEach(ticket => {
                            const link = document.createElement('a');
                            link.href = ticket.url;
                            link.innerHTML = `
                                <div class="tiket-nomor">${ticket.nomor_lengkap}</div>
                                <div class="tiket-detail"><strong>Layanan:</strong> ${ticket.nama_layanan}</div>
                                <div class="tiket-detail"><strong>Loket:</strong> ${ticket.nama_loket}</div>
                            `;
                            ticketListContainer.appendChild(link);
                        });
                        ticketModal.classList.add('active');
                    } else {
                        errorContainer.textContent = data.message || 'Tiket tidak ditemukan.';
                        errorContainer.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorContainer.textContent = 'Terjadi kesalahan saat menghubungi server.';
                    errorContainer.style.display = 'block';
                });
            });
        }

        if (ticketModal && ticketModalCloseBtn) {
            const closeTicketModal = () => ticketModal.classList.remove('active');
            ticketModalCloseBtn.addEventListener('click', closeTicketModal);
            ticketModal.addEventListener('click', (e) => {
                if (e.target === ticketModal) {
                    closeTicketModal();
                }
            });
        }
    });
</script>
@endpush