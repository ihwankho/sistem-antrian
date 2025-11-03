@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/antrian/tiket-detail.css') }}">
@endpush

@section('content')
<div class="page-container">
    @if(isset($tiket) && !empty($tiket['pengunjung']))
    <div class="detail-panel">
        <div class="panel-header">
            <h2>Detail Pengunjung</h2>
            <p>Pengadilan Negeri Banyuwangi</p>
        </div>
        <div class="panel-body">

            {{-- 
              [PERBAIKAN FINAL] 
              Kita gunakan basename() untuk mengambil NAMA FILE SAJA.
              Ini akan mengubah "wajah/foto.jpg" (dari DB) menjadi "foto.jpg".
              Lalu kita gabungkan dengan "images/"
            --}}
            <img src="{{ isset($tiket['pengunjung']['foto_wajah']) ? asset('images/' . basename($tiket['pengunjung']['foto_wajah'])) : asset('images/default-avatar.png') }}" alt="Foto Pengunjung" class="profile-photo">


            <div class="queue-number-box">
                <p>Nomor Antrian</p>
                <span>{{ $tiket['nomor_antrian_lengkap'] }}</span>
            </div>

            <div class="info-grid">
                {{-- (Sisa kode Anda sama persis, tidak perlu diubah) --}}
                <div class="info-label">Nama</div>
                <div class="info-value">{{ $tiket['pengunjung']['nama_pengunjung'] ?? 'N/A' }}</div>

                <div class="info-label">Jenis Kelamin</div>
                <div class="info-value">{{ $tiket['pengunjung']['jenis_kelamin'] ?? 'N/A' }}</div>

                <div class="info-label">Alamat</div>
                <div class="info-value">{{ $tiket['pengunjung']['alamat'] ?? 'N/A' }}</div>

                <hr style="grid-column: 1 / -1; margin: 10px 0; border-style: dashed;">

                <div class="info-label">Loket Tujuan</div>
                <div class="info-value">{{ $tiket['pelayanan']['departemen']['loket']['nama_loket'] ?? 'N/A' }}</div>

                <div class="info-label">Departemen</div>
                <div class="info-value">{{ $tiket['pelayanan']['departemen']['nama_departemen'] ?? 'N/A' }}</div>

                <div class="info-label">Waktu Daftar</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($tiket['created_at'] ?? now())->translatedFormat('d M Y, H:i') }}</div>
            </div>
        </div>
    </div>
    @else
    <div class="detail-panel error-container">
        {{-- (Sisa kode Anda sama persis, tidak perlu diubah) --}}
        <div class="panel-header">
            <h2>Data Tidak Ditemukan</h2>
        </div>
        <div class="panel-body">
            <p>Data tiket dengan ID tersebut tidak ditemukan dalam sistem.</p>
            <a href="{{ route('landing.page') }}" class="btn mt-3" style="background: var(--primary-green); color: white; border-radius: 50px; padding: 10px 24px; text-decoration: none;">
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
    @endif
</div>
@endsection