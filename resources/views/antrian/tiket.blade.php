@extends('layouts.landing')

@push('styles')
<style>
    .page-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
        padding: 40px 24px;
        background-color: #f4f7f6; /* Warna latar belakang halaman */
    }
    
    /* [DESAIN BARU] Wrapper utama tiket */
    .ticket-wrapper {
        max-width: 400px; /* Sedikit lebih lebar */
        width: 100%;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        text-align: center;
        position: relative;
        /* Awalnya disembunyikan untuk mencegah flash konten kosong */
        visibility: hidden; 
    }
    .ticket-wrapper.visible {
        visibility: visible;
    }

    /* [BARU] Efek sisi sobekan tiket (gerigi) */
    .ticket-wrapper::before, .ticket-wrapper::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 12px;
        /* Gradient ini menciptakan ilusi lubang/sobekan */
        background-image: radial-gradient(circle at 0 8px, transparent, transparent 3px, #f4f7f6 3px, #f4f7f6 4px, transparent 4px);
        background-size: 12px 12px;
        background-repeat: repeat-y;
    }
    .ticket-wrapper::before {
        left: -6px; /* Setengah dari lebar */
    }
    .ticket-wrapper::after {
        right: -6px; /* Setengah dari lebar */
        transform: scaleX(-1); /* Membalik gradien untuk sisi kanan */
    }

    /* [BARU] Header Tiket dengan Logo */
    .ticket-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        border-bottom: 2px dashed #e0e0e0;
    }
    .icon-logo {width: 70px; height: 70px;  object-fit: contain;}        

    .ticket-header-text {
        text-align: left;
    }
    .ticket-header-text h4 {
        font-family: var(--font-heading);
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-green);
    }
    .ticket-header-text p {
        margin: 0;
        font-size: 0.85rem;
        color: #666;
    }

    /* [DESAIN BARU] Badan Tiket */
    .ticket-body {
        padding: 30px 24px;
    }
    .ticket-number-label {
        font-size: 1rem;
        color: #555;
    }
    .ticket-number {
        font-family: var(--font-heading);
        font-size: 5rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 5px 0 20px 0;
        color: var(--primary-green);
    }
    .qr-code {
        margin: 20px 0;
        min-height: 150px;
    }
    .ticket-info {
        text-align: left;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    .ticket-info p {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    .ticket-info strong {
        font-weight: 600;
        color: #333;
    }
    .ticket-info span {
        color: #555;
    }

    /* [BARU] Footer Tiket */
    .ticket-footer {
        padding: 16px 24px;
        background-color: #f8f9fa;
        border-top: 2px dashed #e0e0e0;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    .ticket-footer p {
        margin: 0;
        font-size: 0.8rem;
        color: #777;
        font-style: italic;
    }

    /* Tombol Aksi di luar tiket */
    .action-buttons { margin-top: 30px; display: flex; gap: 15px; justify-content: center; }
    .btn-action { border-radius: 50px; padding: 12px 24px; font-weight: 600; border: none; color: white; cursor: pointer; transition: all 0.3s ease; }
    .btn-print { background-color: var(--primary-green); }
    .btn-download { background-color: var(--accent-gold); }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }

    /* Style khusus untuk mencetak */
    @media print {
        body * { visibility: hidden; }
        .page-container {
            padding: 0;
            background: none;
            justify-content: flex-start;
        }
        .ticket-wrapper, .ticket-wrapper * { visibility: visible; }
        .ticket-wrapper {
            position: absolute; left: 0; top: 0; width: 100%; max-width: none;
            margin: 0; box-shadow: none; border: 2px solid #000;
            -webkit-print-color-adjust: exact; /* Memaksa browser mencetak warna latar */
            print-color-adjust: exact;
        }
        .ticket-wrapper::before, .ticket-wrapper::after {
            /* Ubah warna background sobekan menjadi putih untuk kertas cetak */
            background-image: radial-gradient(circle at 0 8px, transparent, transparent 3px, #fff 3px, #fff 4px, transparent 4px);
        }
        .action-buttons, .navbar, .footer { display: none !important; }
        main { padding-top: 0 !important; }
    }
</style>
@endpush

@section('content')
<div class="page-container" id="pageContainer">
    <div class="ticket-wrapper" id="ticketContainer">
        <div class="ticket-header">
            <div class="icon-logo ">
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" class="icon-logo">
            </div>
                        <div class="ticket-header-text">
                <h4>PENGADILAN NEGERI BANYUWANGI</h4>
                <p>E-PTSP (Pelayanan Terpadu Satu Pintu)</p>
            </div>
        </div>

        <div class="ticket-body">
            <p class="ticket-number-label">NOMOR ANTRIAN ANDA</p>
            <h1 class="ticket-number" id="ticketNumberDisplay"></h1>
            <div class="qr-code" id="qrCodeContainer"></div>
            <div class="ticket-info">
                <p><strong>Layanan:</strong> <span id="departemenDisplay"></span></p>
                <p><strong>Loket Tujuan:</strong> <span id="loketDisplay"></span></p>
                <p><strong>Tanggal:</strong> <span id="tanggalDisplay"></span></p>
            </div>
        </div>

        <div class="ticket-footer">
            <p>Harap simpan tiket ini dan tunggu hingga nomor Anda dipanggil.</p>
        </div>
        @php $tiketId = $tiket['id'] ?? null; @endphp
    </div>

    {{-- Tombol Aksi --}}
    <div class="action-buttons">
        <button class="btn-action btn-print" onclick="printTicket()"><i class="bi bi-printer-fill me-2"></i> Cetak Tiket</button>
        <button class="btn-action btn-download" onclick="downloadTicketAsImage()"><i class="bi bi-download me-2"></i> Unduh Tiket</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticketDataFromServer = @json($tiket ?? null);
        const ticketContainer = document.getElementById('ticketContainer');
        
        function displayTicket(ticketData) {
            const ticketNumberEl = document.getElementById('ticketNumberDisplay');
            const qrContainerEl = document.getElementById('qrCodeContainer');
            const loketEl = document.getElementById('loketDisplay');
            const departemenEl = document.getElementById('departemenDisplay');
            const tanggalEl = document.getElementById('tanggalDisplay');

            if (!ticketData) {
                window.location.href = "{{ route('landing.page') }}";
                return;
            }

            ticketNumberEl.textContent = ticketData.nomor_antrian;
            loketEl.textContent = ticketData.nama_loket;
            departemenEl.textContent = ticketData.nama_departemen;
            
            const date = new Date();
            const formattedDate = `${date.getDate().toString().padStart(2, '0')} ${date.toLocaleString('id-ID', { month: 'short' })} ${date.getFullYear()}, ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
            tanggalEl.textContent = `${formattedDate}`;

            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(ticketData.qr_code_url)}`;
            qrContainerEl.innerHTML = `<img src="${qrUrl}" alt="QR Code" style="border: 1px solid #ddd; border-radius: 8px;">`;
            
            ticketContainer.classList.add('visible');
        }

        if (ticketDataFromServer) {
            ticketDataFromServer.qr_code_url = "{{ $tiketId ? route('tiket.detail', ['id' => $tiketId]) : '' }}";
            sessionStorage.setItem('lastTicket', JSON.stringify(ticketDataFromServer));
            displayTicket(ticketDataFromServer);
        } else {
            const storedTicketJSON = sessionStorage.getItem('lastTicket');
            if (storedTicketJSON) {
                const storedTicketData = JSON.parse(storedTicketJSON);
                displayTicket(storedTicketData);
            } else {
                window.location.href = "{{ route('landing.page') }}";
            }
        }
    });

    function printTicket() {
        window.print();
    }

    function downloadTicketAsImage() {
        const ticketElement = document.getElementById('ticketContainer');
        const nomorAntrian = document.getElementById('ticketNumberDisplay').textContent.trim();
        
        html2canvas(ticketElement, { 
            scale: 2, 
            backgroundColor: '#f4f7f6', // Sama dengan warna .page-container
            useCORS: true // <-- Perbaikan utama ada di sini
        }).then(function(canvas) {
            const link = document.createElement('a');
            link.download = `tiket-antrian-${nomorAntrian}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).catch(function(error) {
            console.error('Gagal membuat gambar:', error);
            alert('Gagal mengunduh tiket.');
        });
    }
</script>
@endpush