@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Panggilan Antrian - Admin</h1>
            
            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Panggil Antrian</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label for="loketSelect" class="form-label">Pilih Loket</label>
                                <select class="form-select" id="loketSelect">
                                    @foreach($lokets as $loket)
                                    <option value="{{ $loket->id }}" {{ $selectedLoketId == $loket->id ? 'selected' : '' }}>{{ $loket->nama_loket }}</option>
                                    @endforeach
                                </select>
                            </div>

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
                                    <button class="btn btn-success w-100 btn-next" data-loket-id="{{ $selectedLoketId }}">
                                        <i class="material-icons align-middle">skip_next</i> Next
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-info w-100 btn-recall-main" data-loket-id="{{ $selectedLoketId }}" {{ !$currentCalling ? 'disabled' : '' }}>
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
                                            <th>Loket</th>
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
                                            <td>{{ $antrian->nama_loket }}</td>
                                            <td>{{ $antrian->created_at->format('H:i') }}</td>
                                            <td>
                                                @if($antrian->status_antrian == 2)
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary btn-recall-table" data-loket-id="{{ $antrian->pelayanan->departemen->loket->id }}" title="Recall">
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
                                            <td colspan="7" class="text-center">Tidak ada antrian aktif</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center">
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
<script>
$(document).ready(function() {
    // Fungsi untuk mengucapkan teks menggunakan Web Speech API
    function speakText(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            utterance.onerror = function(event) {
                console.error('SpeechSynthesis Error:', event);
            };
            window.speechSynthesis.speak(utterance);
        } else {
            console.error('Web Speech API tidak didukung di browser ini.');
        }
    }

    // Handle perubahan dropdown loket
    $('#loketSelect').change(function() {
        const loketId = $(this).val();
        window.location.href = "{{ route('panggilan.admin') }}?loket=" + loketId;
    });

    // Fungsi AJAX generik untuk tombol aksi
    // Ditambahkan parameter 'playSound' untuk mengontrol suara
    function handleActionButton(button, url, data, successMessage, playSound, callback) {
        button.prop('disabled', true).html('<i class="material-icons align-middle">hourglass_top</i> Memproses...');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: { ...data, _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.status) {
                    toastr.success(successMessage + (response.data.kode_antrian ? ': ' + response.data.kode_antrian : ''));
                    
                    // Suara hanya akan diputar jika playSound adalah true
                    if (playSound && response.data && response.data.voice_text) {
                        setTimeout(() => speakText(response.data.voice_text), 500);
                    }
                    
                    if (callback) {
                        callback(response);
                    } else {
                        // Diberi jeda sedikit lebih lama jika ada suara, jika tidak bisa lebih cepat
                        const reloadDelay = playSound ? 2000 : 1000;
                        setTimeout(() => location.reload(), reloadDelay);
                    }
                } else {
                    toastr.error('Gagal: ' + response.message);
                    button.prop('disabled', false).html(button.data('original-html'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html(button.data('original-html'));
            }
        });
    }

    // Simpan HTML asli tombol
    $('.btn-next, .btn-recall-main, .btn-recall-table, .btn-finish, .btn-skip').each(function() {
        $(this).data('original-html', $(this).html());
    });
    
    // Event listener untuk tombol-tombol
    $('.btn-next').click(function() {
        const loketId = $(this).data('loket-id');
        // Panggil handleActionButton dengan playSound = false
        handleActionButton($(this), "{{ route('panggilan.admin.next') }}", { id_loket: loketId }, 'Antrian berhasil dipanggil', false, () => {
             setTimeout(() => location.reload(), 1000); // Reload lebih cepat karena tidak menunggu suara
        });
    });

    $('.btn-recall-main').click(function() {
        const loketId = $(this).data('loket-id');
        // Panggil handleActionButton dengan playSound = false
        handleActionButton($(this), "{{ route('panggilan.admin.recall') }}", { id_loket: loketId }, 'Recall berhasil', false, (response) => {
            // Setelah selesai, kembalikan tombol ke state semula
            $(this).prop('disabled', false).html($(this).data('original-html'));
        });
    });

    $('.btn-recall-table').click(function() {
        const loketId = $(this).data('loket-id');
        // Panggil handleActionButton dengan playSound = false
        handleActionButton($(this), "{{ route('panggilan.admin.recall') }}", { id_loket: loketId }, 'Recall berhasil', false, (response) => {
            $(this).prop('disabled', false).html($(this).data('original-html'));
        });
    });

    $('.btn-finish').click(function() {
        const antrianId = $(this).data('antrian-id');
        // Panggil handleActionButton dengan playSound = false (atau true jika ingin tetap ada suara)
        handleActionButton($(this), "{{ route('panggilan.admin.finish') }}", { id_antrian: antrianId }, 'Antrian berhasil diselesaikan', false);
    });

    $('.btn-skip').click(function() {
        const antrianId = $(this).data('antrian-id');
        // Panggil handleActionButton dengan playSound = false (atau true jika ingin tetap ada suara)
        handleActionButton($(this), "{{ route('panggilan.admin.skip') }}", { id_antrian: antrianId }, 'Antrian berhasil dilewati', false);
    });
});
</script>
@endpush