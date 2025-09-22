@extends('layouts.landing')

@push('styles')
<style>
    .page-container {
        padding: 60px 24px;
        background-color: #f4f7f6;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
    }
    .detail-panel {
        max-width: 600px;
        width: 100%;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    .panel-header {
        padding: 24px;
        border-bottom: 1px solid #e9ecef;
        text-align: center;
    }
    .panel-header h2 {
        font-family: var(--font-heading);
        font-size: 1.8rem;
        margin: 0;
        color: var(--primary-green);
    }
    .panel-header p {
        margin: 4px 0 0 0;
        color: #6c757d;
    }
    .panel-body {
        padding: 30px;
        text-align: center;
    }
    .profile-photo {
        width: 150px;
        height: 150px;
        /* [DIPERBAIKI] Mengubah foto menjadi kotak dengan sudut tumpul */
        border-radius: 16px; 
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        margin-bottom: 24px;
    }
    .queue-number-box {
        background-color: #e8f0ed;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 30px;
    }
    .queue-number-box p {
        margin: 0;
        font-size: 1rem;
        color: var(--primary-green);
    }
    .queue-number-box span {
        font-family: var(--font-heading);
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-green);
    }
    .info-grid {
        display: grid;
        grid-template-columns: max-content 1fr;
        gap: 12px 20px;
        text-align: left;
    }
    .info-label {
        font-weight: 600;
        color: #343a40;
    }
    .info-label::after {
        content: ":";
    }
    .info-value {
        color: #555;
        word-break: break-word;
    }
    .error-container {
        text-align: center;
    }
</style>
@endpush

@section('content')

@php
    // Logika ini tetap sama, tidak perlu diubah
    if (isset($tiket) && !empty($tiket['pelayanan'])) {
        $idLoket = $tiket['pelayanan']['departemen']['loket']['id'];
        $allLokets = \App\Models\Loket::orderBy('id', 'ASC')->pluck('id')->toArray();
        $loketIndex = array_search($idLoket, $allLokets);
        $kodeHuruf = ($loketIndex !== false) ? chr(65 + $loketIndex) : '?';
        $nomorUrut = $tiket['nomor_antrian'];
        $nomorAntrianLengkap = $kodeHuruf . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    } else {
        $nomorAntrianLengkap = $tiket['nomor_antrian'] ?? 'N/A';
    }
@endphp

<div class="page-container">
    @if(isset($tiket) && !empty($tiket['pengunjung']))
    <div class="detail-panel">
        <div class="panel-header">
            <h2>Detail Pengunjung</h2>
            <p>Pengadilan Negeri Banyuwangi</p>
        </div>
        <div class="panel-body">
            <img src="{{ isset($tiket['pengunjung']['foto_wajah']) ? asset('storage/' . $tiket['pengunjung']['foto_wajah']) : asset('images/default-avatar.png') }}" alt="Foto Pengunjung" class="profile-photo">
            
            <div class="queue-number-box">
                <p>Nomor Antrian</p>
                <span>{{ $nomorAntrianLengkap }}</span>
            </div>
            
            <div class="info-grid">
                <div class="info-label">Nama</div>
                <div class="info-value">{{ $tiket['pengunjung']['nama_pengunjung'] ?? 'N/A' }}</div>
                
                {{-- [DIPERBAIKI] Baris NIK dan No. HP disembunyikan menggunakan komentar Blade --}}
                {{--
                <div class="info-label">NIK</div>
                <div class="info-value">{{ $tiket['pengunjung']['nik'] ?? 'N/A' }}</div>

                <div class="info-label">No. HP</div>
                <div class="info-value">{{ $tiket['pengunjung']['no_hp'] ?? 'N/A' }}</div>
                --}}

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
                <div class="info-value">{{ date('d M Y, H:i', strtotime($tiket['created_at'] ?? now())) }}</div>
            </div>
        </div>
    </div>
    @else
    <div class="detail-panel error-container">
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