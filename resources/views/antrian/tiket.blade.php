@extends('layouts.landing')

@push('styles')
    {{-- Memuat file CSS eksternal yang sudah dipisahkan --}}
    <link rel="stylesheet" href="{{ asset('css/tiket.css') }}">
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
{{-- JavaScript libraries --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

{{-- Custom page script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticketData = @json($tiket ?? null);

        // Guard clause to stop execution if there's no ticket
        if (!ticketData)return;

        const qrContainerEl = document.getElementById('qrCodeContainer');
        const qrCodeUrl = "{{ route('antrian.tiket.detail', ['uuid' => $tiket['uuid'] ?? '']) }}";
        const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrCodeUrl)}`;
        
        // Create an image element for the QR code
        const qrImage = new Image();
        qrImage.src = apiUrl;
        qrImage.alt = "QR Code";
        qrContainerEl.appendChild(qrImage);
    });

    // Function to trigger the browser's print dialog
    function printTicket() {
        window.print();
    }

    // Function to download the ticket element as a PNG image
    function downloadTicketAsImage() {
        const ticketElement = document.getElementById('ticketContainer');
        const nomorAntrian = ticketElement.querySelector('.ticket-number').textContent.trim();

        // Use html2canvas to render the element to a canvas
        html2canvas(ticketElement, {
            scale: 2, // Higher scale for better resolution
            backgroundColor: '#f4f7f6', // Match the page background
            useCORS: true // Important for external images if any
        }).then(canvas => {
            // Create a temporary link to trigger the download
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