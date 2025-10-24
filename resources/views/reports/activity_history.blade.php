@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- HEADER HALAMAN --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Laporan Aktivitas Antrian</h1>
            <a href="#" class="btn btn-success" id="exportButton" style="display: none;">
                <i class="material-icons align-middle fs-6">download</i> Export Excel
            </a>
        </div>
    </div>

    {{-- KARTU FILTER --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" onsubmit="return false;">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label for="startDate">Dari Tanggal</label>
                        <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="endDate">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="endDate" name="end_date">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="1">Menunggu</option>
                            <option value="2">Dipanggil</option>
                            <option value="3">Selesai</option>
                            <option value="4">Dilewati</option>
                        </select>
                    </div>
                    @if(Auth::user()->role === 1)
                    <div class="col-md-3 mb-3">
                        <label for="department">Departemen</label>
                        <select class="form-control" id="department" name="department_id">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-primary w-100" id="filterButton">
                            <i class="material-icons align-middle fs-6">search</i> Tampilkan
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="button" class="btn btn-secondary w-100" onclick="resetFilter()">
                            <i class="material-icons align-middle fs-6">refresh</i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KARTU STATISTIK RINGKASAN --}}
    <div class="row" id="summarySection" style="display: none;">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Antrian</div>
                            <div id="totalAntrian" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto"><i class="material-icons text-gray-300" style="font-size: 32px;">receipt_long</i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selesai</div>
                            <div id="totalSelesai" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto"><i class="material-icons text-gray-300" style="font-size: 32px;">check_circle</i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Dilewati</div>
                            <div id="totalDilewati" class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto"><i class="material-icons text-gray-300" style="font-size: 32px;">skip_next</i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Estimasi Waktu Tunggu</div>
                            <div id="estimasiWaktu" class="h5 mb-0 font-weight-bold text-gray-800">0 Menit</div>
                        </div>
                        <div class="col-auto"><i class="material-icons text-gray-300" style="font-size: 32px;">timer</i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU TABEL HASIL LAPORAN --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Hasil Laporan</h6>
            <span id="resultCount" class="badge bg-primary rounded-pill"></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="reportTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu Daftar</th>
                            <th>Nomor Antrian</th>
                            <th>Nama Pengunjung</th>
                            <th>Layanan</th>
                            <th>Departemen</th>
                            <th>Loket</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody id="reportData">
                        <tr>
                            <td colspan="9" class="text-center">Silakan gunakan filter di atas untuk menampilkan data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL UNTUK DETAIL PENGUNJUNG --}}
<div class="modal fade" id="visitorDetailModal" tabindex="-1" aria-labelledby="visitorDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="visitorDetailModalLabel">Detail Pengunjung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- Data Diri --}}
        <dl class="row">
            <dt class="col-sm-4">Nama</dt>
            <dd class="col-sm-8" id="detailNama"></dd>
            
            <dt class="col-sm-4">No. HP</dt>
            <dd class="col-sm-8" id="detailHp"></dd>
            <dt class="col-sm-4">Jenis Kelamin</dt>
            <dd class="col-sm-8" id="detailJk"></dd>
            <dt class="col-sm-4">Alamat</dt>
            <dd class="col-sm-8" id="detailAlamat"></dd>
        </dl>
        <hr>
        {{-- Bagian untuk menampilkan foto --}}
        <div class="row mt-3">
            <div class="col-md-12 text-center mb-3">
                <strong>Foto Wajah</strong>
                <div id="containerFotoWajah" class="mt-2">
                    {{-- Konten diisi oleh JavaScript --}}
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const today = new Date().toISOString().split('T')[0];
        $('#startDate').val(today);
        $('#endDate').val(today);
        
        fetchReportData();
        
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchReportData();
        });
    });

    function fetchReportData() {
        // Membersihkan department_id dari karakter ':'
        let formData = new URLSearchParams($('#filterForm').serialize());
        let deptId = formData.get('department_id');
        if (deptId) {
            formData.set('department_id', deptId.replace(/[^0-9]/g, ''));
        }
        
        const formDataString = formData.toString();
        const apiUrl = `/api/reports/activity-history?${formDataString}`;
        const exportUrl = `{{ route('reports.activity.export') }}?${formDataString}`;
        $('#exportButton').attr('href', exportUrl);

        $.ajax({
            url: apiUrl,
            method: 'GET',
            headers: { 
                'Authorization': 'Bearer ' + '{{ session("token") }}',
                'Accept': 'application/json'
            },
            beforeSend: function() {
                $('#filterButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mencari...');
                $('#reportData').html('<tr><td colspan="9" class="text-center">Memuat data...</td></tr>');
                $('#summarySection').slideUp();
            },
            success: function(response) {
                const summary = response.summary;
                const data = response.data;
                const tableBody = $('#reportData');
                const resultCount = $('#resultCount');
                const exportButton = $('#exportButton');

                $('#totalAntrian').text(summary.total || 0);
                $('#totalSelesai').text(summary.selesai || 0);
                $('#totalDilewati').text(summary.dilewati || 0);
                $('#estimasiWaktu').text(`${summary.estimasi_waktu || 0} Menit`);
                
                tableBody.empty();
                
                if (Array.isArray(data) && data.length > 0) {
                    $('#summarySection').slideDown();
                    resultCount.text(`${data.length} hasil ditemukan`);
                    exportButton.show();
                    
                    let tableRows = '';
                    data.forEach((item, index) => {
                        const visitorData = item.pengunjung ? JSON.stringify(item.pengunjung) : '{}';
                        const escapedVisitorData = visitorData.replace(/'/g, '&apos;');

                        tableRows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${formatDateTime(item.waktu_daftar)}</td>
                                <td><span class="font-weight-bold">${item.nomor_antrian_lengkap || '-'}</span></td>
                                <td>${item.nama_pengunjung || '-'}</td>
                                <td>${item.nama_layanan || '-'}</td>
                                <td>${item.nama_departemen || '-'}</td>
                                <td>${item.nama_loket || '-'}</td>
                                <td>${getStatusBadge(item.status)}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='showVisitorDetails(${escapedVisitorData})' title="Lihat Detail Pengunjung">
                                        <i class="material-icons align-middle fs-6">visibility</i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tableBody.html(tableRows);
                } else {
                    resultCount.text('0 hasil ditemukan');
                    exportButton.hide();
                    tableBody.html('<tr><td colspan="9" class="text-center">Tidak ada data yang cocok dengan filter Anda.</td></tr>');
                }
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.status, xhr.responseText);
                $('#reportData').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data. Periksa konsol (F12) untuk detail.</td></tr>');
            },
            complete: function() {
                $('#filterButton').prop('disabled', false).html('<i class="material-icons align-middle fs-6">search</i> Tampilkan');
            }
        });
    }

    // Fungsi ini SUDAH BENAR. Ia mengambil `visitor.foto_wajah_url`
    // yang sudah disiapkan oleh Api/ReportController.php
    function showVisitorDetails(visitor) {
        if (!visitor) return;
        
        $('#detailNama').text(visitor.nama_pengunjung || '-');
        $('#detailHp').text(visitor.no_hp || '-');
        $('#detailJk').text(visitor.jenis_kelamin || '-');
        $('#detailAlamat').text(visitor.alamat || '-');

        const wajahContainer = $('#containerFotoWajah');
        const placeholder = 'https://via.placeholder.com/200x120.png?text=Tidak+Ada+Foto';

        if (visitor.foto_wajah_url) {
            wajahContainer.html(`
                <a href="${visitor.foto_wajah_url}" target="_blank" title="Lihat ukuran penuh">
                    <img src="${visitor.foto_wajah_url}" class="img-thumbnail" style="max-height: 150px;" alt="Foto Wajah">
                </a>
                <a href="${visitor.foto_wajah_url}" class="btn btn-sm btn-primary mt-2" download>
                    <i class="material-icons align-middle fs-6">download</i> Unduh
                </a>
            `);
        } else {
            wajahContainer.html(`<img src="${placeholder}" class="img-thumbnail" alt="Tidak ada foto Wajah">`);
        }
        
        var myModal = new bootstrap.Modal(document.getElementById('visitorDetailModal'));
        myModal.show();
    }

    function resetFilter() {
        $('#filterForm')[0].reset();
        const today = new Date().toISOString().split('T')[0];
        $('#startDate').val(today);
        $('#endDate').val(today);
        fetchReportData();
    }

    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '-';
        return new Date(dateTimeString).toLocaleString('id-ID', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function getStatusBadge(statusText) {
        const normalizedStatus = (statusText || '').trim().toLowerCase();
        switch (normalizedStatus) {
            case 'menunggu': return '<span class="badge bg-warning text-dark">Menunggu</span>';
            case 'dipanggil': return '<span class="badge bg-info text-dark">Dipanggil</span>';
            case 'selesai': return '<span class="badge bg-success">Selesai</span>';
            case 'dilewati': return '<span class="badge bg-secondary">Dilewati</span>';
            default: return `<span class="badge bg-light text-dark">${statusText || 'N/A'}</span>`;
        }
    }
</script>
@endpush