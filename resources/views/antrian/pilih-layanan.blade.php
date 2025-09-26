@extends('layouts.landing')

@push('styles')
{{-- Memuat file CSS yang sudah dipisah. Atribut turbo-track penting untuk navigasi --}}
<link rel="stylesheet" href="{{ asset('css/pilih-layanan.css') }}" data-turbo-track="reload">
@endpush

@section('content')

<div class="page-container">
    <div class="container">

        <div class="section-heading">
            <h2>Pilihan Layanan</h2>
            <p>Silakan pilih jenis layanan yang Anda perlukan di bawah ini.</p>
        </div>

        <div class="service-container">
            @if (!empty($pelayananGrouped))
                @foreach ($pelayananGrouped as $departemen => $pelayanans)
                    <section class="department-section">
                        <h3 class="department-title">{{ $departemen }}</h3>
                        <div class="service-grid">
                            @foreach ($pelayanans as $layanan)
                                <form action="{{ route('antrian.isi-data') }}" method="GET">
                                    <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
                                    <button type="submit" class="service-card">
                                        <div class="card-text">
                                            <p class="title">{{ $layanan['nama_layanan'] }}</p>
                                            <p class="description">Klik untuk melanjutkan</p>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                    <h3 class="department-title">Tidak Ada Layanan</h3>
                    <p class="text-muted">Saat ini belum ada layanan yang tersedia. Silakan coba lagi nanti.</p>
                    <a href="{{ route('landing.page') }}" class="btn mt-3" style="background: var(--primary-green); color: white; border-radius: 50px; padding: 10px 20px;">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection