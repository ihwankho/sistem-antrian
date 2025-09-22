@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Laporan Aktivitas Antrian</h1>
            <a href="#" class="btn btn-success" id="exportButton" style="display: none;">
                <i class="material-icons align-middle fs-6">download</i> Export Excel
            </a>
        </div>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Hasil Laporan</h6>
            <span id="resultCount" class="badge bg-primary"></span>
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
                        </tr>
                    </thead>
                    <tbody id="reportData">
                        <tr>
                            <td colspan="8" class="text-center">Silakan gunakan filter di atas untuk menampilkan data.</td>
                        </tr>
                    </tbody>
                </table>
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
        const formData = $('#filterForm').serialize();
        const apiUrl = `/api/reports/activity-history?${formData}`;

        // Perbarui URL untuk tombol export setiap kali filter berubah
        const exportUrl = `{{ route('reports.activity.export') }}?${formData}`;
        $('#exportButton').attr('href', exportUrl);

        $.ajax({
            url: apiUrl,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + '{{ session("token") }}' },
            beforeSend: function() {
                $('#filterButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mencari...');
                $('#reportData').html('<tr><td colspan="8" class="text-center">Memuat data...</td></tr>');
            },
            success: function(response) {
                const tableBody = $('#reportData');
                const resultCount = $('#resultCount');
                const exportButton = $('#exportButton');
                tableBody.empty();
                
                if (Array.isArray(response) && response.length > 0) {
                    resultCount.text(`${response.length} hasil ditemukan`);
                    exportButton.show();
                    let tableRows = '';
                    response.forEach((item, index) => {
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
                            </tr>
                        `;
                    });
                    tableBody.html(tableRows);
                } else {
                    resultCount.text('0 hasil ditemukan');
                    exportButton.hide();
                    tableBody.html('<tr><td colspan="8" class="text-center">Tidak ada data yang cocok dengan filter Anda.</td></tr>');
                }
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.status, xhr.responseText);
                $('#reportData').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Periksa konsol (F12).</td></tr>');
            },
            complete: function() {
                $('#filterButton').prop('disabled', false).html('<i class="material-icons align-middle fs-6">search</i> Tampilkan');
            }
        });
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
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }

    function getStatusBadge(statusText) {
        const normalizedStatus = (statusText || '').trim().toLowerCase();
        switch (normalizedStatus) {
            case 'menunggu': return '<span class="badge bg-warning text-dark">Menunggu</span>';
            case 'dipanggil': return '<span class="badge bg-info">Dipanggil</span>';
            case 'selesai': return '<span class="badge bg-success">Selesai</span>';
            case 'dilewati': return '<span class="badge bg-secondary">Dilewati</span>';
            default: return `<span class="badge bg-light text-dark">${statusText || 'N/A'}</span>`;
        }
    }
</script>
@endpush