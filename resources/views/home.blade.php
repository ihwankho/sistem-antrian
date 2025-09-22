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
            <p class="section-subtitle">Estimasi waktu per antrian: 10 menit</p>
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
                <ul id="modalLayananList">
                    {{-- Layanan akan diisi oleh JavaScript --}}
                </ul>
            </div>
        </div>
    </div>
</main>
@endsection


@push('scripts')
{{-- KODE JAVASCRIPT LENGKAP DAN FUNGSIONAL --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        function updateQueueInfo() {
            fetch('/api/antrian_all', {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (!data.status || !data.data) return;

                const allCards = document.querySelectorAll('.stat-card');
                
                allCards.forEach(card => {
                    card.querySelector('.stat-number').textContent = card.dataset.defaultCode;
                    card.querySelector('.stat-text').textContent = 'Tidak ada antrian';
                    const statTime = card.querySelector('.stat-time');
                    statTime.textContent = 'Siap melayani';
                    statTime.style.backgroundColor = '';
                    statTime.style.color = '';
                });

                data.data.forEach(loketData => {
                    const card = document.querySelector(`.stat-card[data-loket-name="${loketData.loket}"]`);
                    if (!card) return;
                    
                    const antrianAktif = loketData.antrian?.find(a => a.status_antrian == 2);
                    const antrianMenunggu = loketData.antrian?.filter(a => a.status_antrian == 1);
                    const statTime = card.querySelector('.stat-time');

                    if (antrianAktif) {
                        card.querySelector('.stat-number').textContent = antrianAktif.kode_antrian;
                        card.querySelector('.stat-text').textContent = 'Sedang Dilayani';
                        statTime.textContent = 'Masuk Loket';
                        statTime.style.backgroundColor = 'var(--primary-green)';
                        statTime.style.color = 'white';
                    } else if (antrianMenunggu?.length > 0) {
                        card.querySelector('.stat-number').textContent = antrianMenunggu[0].kode_antrian;
                        card.querySelector('.stat-text').textContent = `${antrianMenunggu.length} antrian menunggu`;
                        statTime.textContent = 'Menunggu';
                    }
                });
            }).catch(error => console.error('Gagal mengambil data antrian:', error));
        }

        const modal = document.getElementById('layananModal');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const loketTriggers = document.querySelectorAll('.loket-trigger');
        const modalLoketName = document.getElementById('modalLoketName');
        const modalLayananList = document.getElementById('modalLayananList');

        if(modal && loketTriggers.length > 0) {
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
                        
                        modal.classList.add('active');
                    } catch (error) {
                        console.error("Gagal mem-parsing data layanan:", error);
                    }
                });
            });

            const closeModal = () => modal.classList.remove('active');
            modalCloseBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        updateQueueInfo();
        setInterval(updateQueueInfo, 5000);
    });
</script>
@endpush