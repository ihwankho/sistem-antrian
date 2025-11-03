@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/antrian/tiket.css') }}">
@endpush

@section('content')
<div class="page-container" id="pageContainer">

    @if($tiket)
        <div class="ticket-wrapper" id="ticketContainer">
            <div class="ticket-header">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" class="icon-logo">
                <div class="ticket-header-text">
                    <h4>PENGADILAN NEGERI BANYUWANGI</h4>
                    <p>E-PTSP (Pelayanan Terpadu Satu Pintu)</p>
                </div>
            </div>
            <div class="ticket-body">
                <p class="ticket-number-label">NOMOR ANTRIAN ANDA</p>
                <h1 class="ticket-number">{{ $tiket['nomor_antrian_lengkap'] }}</h1>
                <div class="qr-code" id="qrCodeContainer">
                    {{-- QR Code will be inserted by JavaScript --}}
                </div>
                <div class="ticket-info">
                    <p><strong>Loket Tujuan:</strong> <span>{{ $tiket['nama_loket'] ?? 'Tidak diketahui' }}</span></p>
                    <p><strong>Tanggal:</strong> <span>{{ \Carbon\Carbon::parse($tiket['created_at'])->translatedFormat('d M Y, H:i') }}</span></p>
                </div>
            </div>
            <div class="ticket-footer">
                <p>Harap simpan tiket ini dan tunggu hingga nomor Anda dipanggil.</p>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn-action btn-print" onclick="printTicket()"><i class="bi bi-printer-fill me-2"></i> Cetak Tiket</button>
            <button class="btn-action btn-download" onclick="downloadTicketAsImage()"><i class="bi bi-download me-2"></i> Unduh Tiket</button>
        </div>

    @else
        <div class="ticket-wrapper ticket-not-found">
            <h3>Tiket Tidak Ditemukan</h3>
            <p>Maaf, tiket antrian yang Anda cari tidak dapat ditemukan.</p>
            <div class="action-buttons">
                <a href="{{ route('landing.page') }}" class="btn-action btn-print">Kembali ke Beranda</a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticketData = @json($tiket ?? null);

        if (!ticketData) return;

        const qrContainerEl = document.getElementById('qrCodeContainer');
        const qrCodeUrl = "{{ route('antrian.tiket.detail', ['uuid' => $tiket['uuid'] ?? '']) }}";
        const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrCodeUrl)}`;
        
        const qrImage = new Image();
        qrImage.src = apiUrl;
        qrImage.alt = "QR Code";
        qrContainerEl.appendChild(qrImage);
    });

    function printTicket() {
        window.print();
    }

    function downloadTicketAsImage() {
        const ticketElement = document.getElementById('ticketContainer');
        const nomorAntrian = ticketElement.querySelector('.ticket-number').textContent.trim();

        html2canvas(ticketElement, {
            scale: 2,
            backgroundColor: '#f4f7f6',
            useCORS: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `tiket-antrian-${nomorAntrian}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).catch(error => {
            console.error('Failed to create image from ticket:', error);
            alert('Maaf, gagal mengunduh tiket. Silakan coba lagi.');
        });
    }
</script>
@endpush