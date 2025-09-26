@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tiket-detail.css') }}">
@endpush

@section('content')

@php
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

                {{-- Baris NIK dan No. HP disembunyikan menggunakan komentar Blade --}}
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