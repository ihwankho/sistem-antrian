@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pilih-layanan.css') }}" data-turbo-track="reload">
@endpush

@section('content')
<div class="page-container">
    <div class="container">
        <div class="section-heading">
            <h2>Silakan Pilih Departemen</h2>
        </div>

        <div class="service-container">
            @if (!empty($pelayananGrouped) && count($pelayananGrouped) > 0)
                <div class="department-grid">
                    @foreach ($pelayananGrouped as $departemen => $pelayanans)
                        <div class="department-card">
                            {{-- BAGIAN UTAMA KARTU (LINK UNTUK MELANJUTKAN) --}}
                            <a href="{{ route('antrian.isi-data', ['id_departemen' => $pelayanans[0]['departemen']['id']]) }}" class="card-main-link">
                                <div class="card-content">
                                    <div class="department-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h3 class="department-name">{{ $departemen }}</h3>
                                    <p class="department-description">
                                        Klik untuk melanjutkan ke loket {{ strtolower($departemen) }}
                                    </p>
                                </div>
                            </a>

                            {{-- TOMBOL PEMICU MODAL --}}
                            <button type="button" class="service-modal-trigger" 
                                    data-departemen-name="{{ $departemen }}"
                                    data-layanan="{{ json_encode(collect($pelayanans)->pluck('nama_layanan')) }}">
                                {{ count($pelayanans) }} Layanan Tersedia
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- KONTEN JIKA TIDAK ADA LAYANAN (TETAP SAMA) --}}
                <div class="no-services-container">
                    <div class="no-services-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3 class="department-name">Tidak Ada Layanan</h3>
                    <p class="department-description">
                        Saat ini belum ada layanan yang tersedia. Silakan coba lagi nanti.
                    </p>
                    <a href="{{ route('landing.page') }}" class="back-button">
                        <i class="fas fa-arrow-left"></i><span>Kembali</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- [BARU] STRUKTUR MODAL (DITEMPATKAN DI LUAR CONTAINER UTAMA) --}}
<div id="layananModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Layanan Departemen</h3>
            <button id="modalCloseBtn" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <ul id="modalLayananList">
                {{-- Daftar layanan akan dimasukkan di sini oleh JavaScript --}}
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- [BARU] JAVASCRIPT UNTUK MENGONTROL MODAL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Ambil elemen-elemen modal
    const modalOverlay = document.getElementById('layananModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalLayananList = document.getElementById('modalLayananList');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    
    // 2. Ambil semua tombol pemicu modal
    const triggers = document.querySelectorAll('.service-modal-trigger');

    // Fungsi untuk membuka modal
    function openModal(departemen, layananList) {
        // Isi judul modal
        modalTitle.textContent = `Layanan di ${departemen}`;
        
        // Kosongkan daftar layanan sebelumnya
        modalLayananList.innerHTML = '';
        
        // Isi daftar layanan baru
        if (layananList && layananList.length > 0) {
            layananList.forEach(layanan => {
                const li = document.createElement('li');
                li.textContent = layanan;
                modalLayananList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = 'Tidak ada detail layanan untuk departemen ini.';
            modalLayananList.appendChild(li);
        }
        
        // Tampilkan modal
        modalOverlay.classList.add('active');
    }

    // Fungsi untuk menutup modal
    function closeModal() {
        modalOverlay.classList.remove('active');
    }

    // 3. Tambahkan event listener ke setiap tombol pemicu
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function () {
            const departemenName = this.dataset.departemenName;
            // Ambil data layanan (dalam format JSON string) dan ubah menjadi array
            const layananJson = JSON.parse(this.dataset.layanan);
            
            openModal(departemenName, layananJson);
        });
    });

    // 4. Tambahkan event listener untuk menutup modal
    modalCloseBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function (event) {
        // Tutup modal hanya jika yang diklik adalah area overlay (latar belakang)
        if (event.target === modalOverlay) {
            closeModal();
        }
    });
});
</script>
@endpush