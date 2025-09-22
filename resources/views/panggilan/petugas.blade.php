@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Panggilan Antrian - {{ $loket->nama_loket }}</h1>
            
            <div class="alert alert-info mb-4 d-flex align-items-center">
                <i class="material-icons align-middle me-2">info</i>
                <span>Anda mengelola antrian untuk <strong>{{ $loket->nama_loket }}</strong></span>
            </div>
            
            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Panggil Antrian</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-4 p-3 bg-light rounded text-center" id="currentAntrianDisplay">
                                @if($currentCalling)
                                    <h2 class="display-4 fw-bold">{{ $currentCalling->kode_antrian }}</h2>
                                    <p class="mb-0">Sedang Dipanggil</p>
                                @else
                                    <p class="mb-0">Tidak ada antrian yang sedang dipanggil</p>
                                @endif
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    {{-- DIUBAH: Menghapus class 'btn-lg' agar ukurannya normal --}}
                                    <button class="btn btn-success w-100 btn-next">
                                        <i class="material-icons align-middle">skip_next</i> Next
                                    </button>
                                </div>
                                <div class="col-6">
                                    {{-- DIUBAH: Menghapus class 'btn-lg' agar ukurannya normal --}}
                                    <button class="btn btn-info w-100 btn-recall-main" {{ !$currentCalling ? 'disabled' : '' }}>
                                        <i class="material-icons align-middle">replay</i> Recall
                                    </button>
                                </div>
                            </div>

                            @if($currentCalling)
                            <div class="row mb-3">
                                <div class="col-6">
                                    <button class="btn btn-primary w-100 btn-finish" data-antrian-id="{{ $currentCalling->id }}">
                                        <i class="material-icons align-middle">check</i> Selesai
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-warning w-100 btn-skip" data-antrian-id="{{ $currentCalling->id }}">
                                        <i class="material-icons align-middle">skip_next</i> Skip
                                    </button>
                                </div>
                            </div>
                            @endif

                            <div class="alert alert-secondary mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Antrian Tersisa:</span>
                                    <span class="badge bg-primary rounded-pill" id="antrianTersisa">{{ $antrianTersisa }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Daftar Antrian Aktif</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <form method="GET" action="" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Cari..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                                </form>
                                <div>
                                    <span class="me-2">Per halaman:</span>
                                    <select class="form-select d-inline-block w-auto" onchange="window.location.href = this.value">
                                        @foreach([10, 25, 50] as $perPage)
                                        <option value="{{ request()->fullUrlWithQuery(['perPage' => $perPage]) }}" {{ $antrians->perPage() == $perPage ? 'selected' : '' }}>
                                            {{ $perPage }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Antrian</th>
                                            <th>Nama Pengunjung</th>
                                            <th>Status</th>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($antrians as $index => $antrian)
                                        <tr class="{{ $antrian->status_antrian == 2 ? 'table-info' : '' }}">
                                            <td>{{ $index + $antrians->firstItem() }}</td>
                                            <td><strong>{{ $antrian->kode_antrian }}</strong></td>
                                            <td>{{ $antrian->pengunjung->nama_pengunjung ?? 'N/A' }}</td>
                                            <td>
                                                @if($antrian->status_antrian == 1)
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                                @elseif($antrian->status_antrian == 2)
                                                <span class="badge bg-info">Dipanggil</span>
                                                @elseif($antrian->status_antrian == 4)
                                                <span class="badge bg-secondary">Dilewati</span>
                                                @endif
                                            </td>
                                            <td>{{ $antrian->created_at->format('H:i') }}</td>
                                            <td>
                                                @if($antrian->status_antrian == 2)
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary btn-recall-table" title="Recall">
                                                        <i class="material-icons">replay</i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success btn-finish" data-antrian-id="{{ $antrian->id }}" title="Selesai">
                                                        <i class="material-icons">check</i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning btn-skip" data-antrian-id="{{ $antrian->id }}" title="Skip">
                                                        <i class="material-icons">skip_next</i>
                                                    </button>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada antrian aktif</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center">
                                {{-- DIUBAH: Menambahkan view 'pagination::bootstrap-5' untuk style yang benar --}}
                                {{ $antrians->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- DIUBAH: Kode JavaScript disederhanakan untuk mengurangi duplikasi --}}
<script>
$(document).ready(function() {
    // Fungsi untuk mengucapkan teks
    function speakText(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.onerror = (e) => console.error('SpeechSynthesis Error:', e);
            window.speechSynthesis.speak(utterance);
        }
    }

    // Fungsi AJAX generik untuk menangani semua aksi tombol
    function handleAction(button, url, data, successMessage, callback) {
        button.prop('disabled', true).html('<i class="material-icons align-middle">hourglass_top</i>');

        $.ajax({
            url: url,
            method: 'POST',
            data: { ...data, _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.status) {
                    toastr.success(successMessage + (response.data.kode_antrian ? ': ' + response.data.kode_antrian : ''));
                    if (response.data && response.data.voice_text) {
                        setTimeout(() => speakText(response.data.voice_text), 500);
                    }
                    if (callback) {
                        callback(response);
                    } else {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    toastr.error('Gagal: ' + (response.message || 'Terjadi kesalahan'));
                    button.prop('disabled', false).html(button.data('original-html'));
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan pada server';
                toastr.error(msg);
                button.prop('disabled', false).html(button.data('original-html'));
            }
        });
    }

    // Simpan HTML asli dari setiap tombol untuk di-restore jika gagal
    $('.btn-next, .btn-recall-main, .btn-recall-table, .btn-finish, .btn-skip').each(function() {
        $(this).data('original-html', $(this).html());
    });
    
    // Event listeners untuk setiap tombol aksi
    $('.btn-next').click(function() {
        handleAction($(this), "{{ route('panggilan.petugas.next') }}", {}, 'Antrian berhasil dipanggil', () => {
             setTimeout(() => location.reload(), 2000);
        });
    });

    $('.btn-recall-main, .btn-recall-table').click(function() {
        handleAction($(this), "{{ route('panggilan.petugas.recall') }}", {}, 'Recall berhasil', (response) => {
            // Setelah selesai, kembalikan tombol ke state semula tanpa reload halaman
            $(this).prop('disabled', false).html($(this).data('original-html'));
        });
    });

    // Menggunakan event delegation untuk tombol finish & skip yang bisa muncul/hilang
    $(document).on('click', '.btn-finish', function() {
        const antrianId = $(this).data('antrian-id');
        handleAction($(this), "{{ route('panggilan.petugas.finish') }}", { id_antrian: antrianId }, 'Antrian berhasil diselesaikan');
    });

    $(document).on('click', '.btn-skip', function() {
        const antrianId = $(this).data('antrian-id');
        handleAction($(this), "{{ route('panggilan.petugas.skip') }}", { id_antrian: antrianId }, 'Antrian berhasil dilewati');
    });
});
</script>
@endpush