@extends('layouts.landing')

@push('styles')
<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="bg-light py-5">
    <div class="container">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Perhatian!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (!empty($pelayananGrouped))
            @foreach ($pelayananGrouped as $departemen => $pelayanans)
                <div class="mb-5">
                    <h2 class="h5 border-bottom pb-2 mb-3 text-dark fw-semibold">
                        {{ $departemen }}
                    </h2>

                    <div class="row g-3">
                        @foreach ($pelayanans as $layanan)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <form action="{{ route('antrian.isi-data') }}" method="GET">
                                    <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
                                    <button type="submit" class="btn w-100 text-start border border-light-subtle shadow-sm p-3 rounded-3 bg-white hover-shadow transition">
                                        <h5 class="text-primary fw-semibold mb-1">
                                            {{ $layanan['nama_layanan'] }}
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Klik untuk mengambil nomor antrian
                                        </p>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5">
                <div class="card border shadow-sm mx-auto" style="max-width: 400px;">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark">Layanan Tidak Tersedia</h5>
                        <p class="card-text text-muted small">
                            Saat ini belum ada layanan yang tersedia.
                        </p>
                        <a href="{{ route('landing.page') }}" class="btn btn-primary mt-2">
                            Kembali ke Halaman Utama
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
