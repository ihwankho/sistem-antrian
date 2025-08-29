@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Panggilan Antrian - Admin</h1>
            
            <div class="row">
                <!-- Panel Kiri: Panggil Antrian -->
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Panggil Antrian</h5>
                        </div>
                        <div class="card-body">

                            <!-- Dropdown Loket -->
                            <div class="mb-3">
                                <label for="loketSelect" class="form-label">Pilih Loket</label>
                                <select class="form-select" id="loketSelect">
                                    @foreach($lokets as $loket)
                                    <option value="{{ $loket->id }}" {{ $selectedLoketId == $loket->id ? 'selected' : '' }}>{{ $loket->nama_loket }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Nomor Antrian yang Sedang Dipanggil -->
                            <div class="mb-4 p-3 bg-light rounded text-center" id="currentAntrianDisplay">
                                @if($currentCalling)
                                    <h2 class="display-4 fw-bold">{{ $currentCalling->kode_antrian }}</h2>
                                    <p class="mb-0">Sedang Dipanggil</p>
                                @else
                                    <p class="mb-0">Tidak ada antrian yang sedang dipanggil</p>
                                @endif
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="row mb-3">
                                <!-- Tombol Panggil Berikutnya -->
                                <div class="col-6">
                                    <button class="btn btn-success btn-lg w-100 btn-next" data-loket-id="{{ $selectedLoketId }}">
                                        <i class="material-icons align-middle">skip_next</i> Next
                                    </button>
                                </div>
                                <!-- Tombol Recall -->
                                <div class="col-6">
                                    <button class="btn btn-info btn-lg w-100 btn-recall-main" data-loket-id="{{ $selectedLoketId }}" {{ !$currentCalling ? 'disabled' : '' }}>
                                        <i class="material-icons align-middle">replay</i> Recall
                                    </button>
                                </div>
                            </div>

                            @if($currentCalling)
                            <div class="row mb-3">
                                <!-- Tombol Selesai -->
                                <div class="col-6">
                                    <button class="btn btn-primary w-100 btn-finish" data-antrian-id="{{ $currentCalling->id }}">
                                        <i class="material-icons align-middle">check</i> Selesai
                                    </button>
                                </div>
                                <!-- Tombol Skip -->
                                <div class="col-6">
                                    <button class="btn btn-warning w-100 btn-skip" data-antrian-id="{{ $currentCalling->id }}">
                                        <i class="material-icons align-middle">skip_next</i> Skip
                                    </button>
                                </div>
                            </div>
                            @endif

                            <!-- Jumlah Antrian Tersisa -->
                            <div class="alert alert-secondary mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Antrian Tersisa:</span>
                                    <span class="badge bg-primary rounded-pill" id="antrianTersisa">{{ $antrianTersisa }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Kanan: Daftar Antrian Aktif -->
                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Daftar Antrian Aktif</h5>
                        </div>
                        <div class="card-body">
                            <!-- Fitur Pencarian dan Paginasi -->
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

                            <!-- Tabel Antrian -->
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

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $antrians->links() }}
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
            // Hentikan ucapan yang sedang berlangsung (jika ada)
            window.speechSynthesis.cancel();
            
            // Buat objek SpeechSynthesisUtterance
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID'; // Atur bahasa Indonesia
            
            // Atur kecepatan dan pitch (opsional)
            utterance.rate = 1.0; // Kecepatan, 1.0 normal
            utterance.pitch = 1.0; // Pitch, 1.0 normal
            
            // Tambahkan event handler untuk error
            utterance.onerror = function(event) {
                console.error('SpeechSynthesis Error:', event);
            };
            
            // Mulai berbicara
            window.speechSynthesis.speak(utterance);
        } else {
            console.error('Web Speech API tidak didukung di browser ini.');
        }
    }

    // Handle perubahan dropdown loket: reload halaman dengan loket yang dipilih
    $('#loketSelect').change(function() {
        const loketId = $(this).val();
        window.location.href = "{{ route('panggilan.admin') }}?loket=" + loketId;
    });

    // Handle tombol next
    $('.btn-next').click(function() {
        const button = $(this);
        const loketId = button.data('loket-id');
        
        button.prop('disabled', true).html('<i class="material-icons align-middle">hourglass_top</i> Memproses...');
        
        $.ajax({
            url: "{{ route('panggilan.admin.next') }}",
            method: 'POST',
            data: {
                id_loket: loketId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status) {
                    // Update tampilan antrian yang sedang dipanggil
                    $('#currentAntrianDisplay').html(
                        '<h2 class="display-4 fw-bold">' + response.data.kode_antrian + '</h2>' +
                        '<p class="mb-0">Sedang Dipanggil</p>'
                    );
                    
                    // Enable tombol recall
                    $('.btn-recall-main').prop('disabled', false);
                    
                    toastr.success('Antrian berhasil dipanggil: ' + response.data.kode_antrian);
                    
                    // Ucapkan teks dari response
                    if (response.data.voice_text) {
                        // Beri sedikit jeda sebelum berbicara
                        setTimeout(function() {
                            speakText(response.data.voice_text);
                        }, 500);
                    }
                    
                    // Reload setelah 2 detik untuk update tabel
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error('Gagal: ' + response.message);
                }
                button.prop('disabled', false).html('<i class="material-icons align-middle">skip_next</i> Next');
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html('<i class="material-icons align-middle">skip_next</i> Next');
            }
        });
    });

    // Handle tombol recall utama
    $('.btn-recall-main').click(function() {
        const button = $(this);
        const loketId = button.data('loket-id');
        
        button.prop('disabled', true).html('<i class="material-icons align-middle">hourglass_top</i> Memproses...');
        
        $.ajax({
            url: "{{ route('panggilan.admin.recall') }}",
            method: 'POST',
            data: {
                id_loket: loketId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status) {
                    // Ucapkan teks dari response
                    if (response.data.voice_text) {
                        // Beri sedikit jeda sebelum berbicara
                        setTimeout(function() {
                            speakText(response.data.voice_text);
                        }, 500);
                    }
                    
                    toastr.success('Recall berhasil: ' + response.data.kode_antrian);
                } else {
                    toastr.error('Gagal: ' + response.message);
                }
                button.prop('disabled', false).html('<i class="material-icons align-middle">replay</i> Recall');
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html('<i class="material-icons align-middle">replay</i> Recall');
            }
        });
    });

    // Handle tombol recall di tabel
    $('.btn-recall-table').click(function() {
        const button = $(this);
        const loketId = button.data('loket-id');
        
        button.prop('disabled', true).html('<i class="material-icons">hourglass_top</i>');
        
        $.ajax({
            url: "{{ route('panggilan.admin.recall') }}",
            method: 'POST',
            data: {
                id_loket: loketId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status) {
                    // Ucapkan teks dari response
                    if (response.data.voice_text) {
                        // Beri sedikit jeda sebelum berbicara
                        setTimeout(function() {
                            speakText(response.data.voice_text);
                        }, 500);
                    }
                    
                    toastr.success('Recall berhasil: ' + response.data.kode_antrian);
                } else {
                    toastr.error('Gagal: ' + response.message);
                }
                button.prop('disabled', false).html('<i class="material-icons">replay</i>');
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html('<i class="material-icons">replay</i>');
            }
        });
    });

    // Handle tombol finish
    $('.btn-finish').click(function() {
        const button = $(this);
        const antrianId = button.data('antrian-id');
        
        button.prop('disabled', true).html('<i class="material-icons">hourglass_top</i>');
        
        $.ajax({
            url: "{{ route('panggilan.admin.finish') }}",
            method: 'POST',
            data: {
                id_antrian: antrianId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status) {
                    toastr.success('Antrian berhasil diselesaikan');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error('Gagal: ' + response.message);
                    button.prop('disabled', false).html('<i class="material-icons">check</i>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html('<i class="material-icons">check</i>');
            }
        });
    });

    // Handle tombol skip
    $('.btn-skip').click(function() {
        const button = $(this);
        const antrianId = button.data('antrian-id');
        
        button.prop('disabled', true).html('<i class="material-icons">hourglass_top</i>');
        
        $.ajax({
            url: "{{ route('panggilan.admin.skip') }}",
            method: 'POST',
            data: {
                id_antrian: antrianId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status) {
                    toastr.success('Antrian berhasil dilewati');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error('Gagal: ' + response.message);
                    button.prop('disabled', false).html('<i class="material-icons">skip_next</i>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
                button.prop('disabled', false).html('<i class="material-icons">skip_next</i>');
            }
        });
    });
});
</script>
@endpush