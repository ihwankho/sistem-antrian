@extends('layouts.landing')
@section('content')
<style>
    .ticket-container { 
        max-width: 400px; 
        margin: 50px auto; 
        padding: 30px; 
        border: 2px dashed #333; 
        text-align: center; 
        background: #fff; 
        position: relative;
    }
    .ticket-container h1 { 
        font-size: 5rem; 
        font-weight: bold; 
        margin: 20px 0; 
        color: #1a3a5f; 
    }
    .qr-code {
        margin: 20px 0;
        display: flex;
        justify-content: center;
    }
    .action-buttons {
        margin-top: 30px;
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-print {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .btn-download {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .btn-print:hover {
        background-color: #218838;
    }
    .btn-download:hover {
        background-color: #0056b3;
    }

    /* Print styles untuk printer termal */
    @media print {
        body * {
            visibility: hidden;
        }
        .ticket-container, .ticket-container * {
            visibility: visible;
        }
        .ticket-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 58mm; /* Ukuran kertas printer termal */
            max-width: none;
            margin: 0;
            padding: 10px;
            border: 1px solid #000;
        }
        .ticket-container h1 {
            font-size: 2rem;
        }
        .action-buttons {
            display: none;
        }
        .qr-code img {
            width: 80px !important;
            height: 80px !important;
        }
    }

    /* Hide buttons saat print */
    @media print {
        .action-buttons {
            display: none !important;
        }
    }
</style>

<div class="ticket-container" id="ticketContainer">
    <h3 class="fw-bold">TIKET ANTRIAN ANDA</h3>
    <p class="text-muted">Harap simpan atau cetak tiket ini</p>
    
    {{-- Nomor Antrian --}}
    <h1>{{ $tiket['nomor_antrian'] }}</h1>
    
    {{-- QR Code --}}
    <div class="qr-code">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(json_encode(['nomor_antrian' => $tiket['nomor_antrian'], 'nama_departemen' => $tiket['nama_departemen'], 'nama_loket' => $tiket['nama_loket'], 'timestamp' => time()])) }}" 
             alt="QR Code" 
             style="border: 1px solid #ddd;">
    </div>
    
    <p class="fs-5">Silakan menuju ke <strong>{{ $tiket['nama_loket'] ?? 'Loket' }}</strong></p>
    <hr>
    <p><strong>Departemen:</strong> {{ $tiket['nama_departemen'] ?? 'N/A' }}</p>
    <p>Tanggal: {{ date('d M Y, H:i') }}</p>
</div>

{{-- Action Buttons --}}
<div class="action-buttons">
    <button class="btn-print" onclick="printThermalTicket()">
        🖨️ Cetak Tiket
    </button>
    <button class="btn-download" onclick="downloadTicketAsImage()">
        📥 Unduh Tiket
    </button>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
// Fungsi untuk mencetak tiket ke printer termal
function printThermalTicket() {
    // Buka jendela print baru dengan styling khusus untuk printer termal
    const printWindow = window.open('', '_blank');
    const ticketContent = document.getElementById('ticketContainer').innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak Tiket</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    margin: 0;
                    padding: 10px;
                    width: 58mm;
                    background: white;
                }
                .ticket-container {
                    width: 100%;
                    text-align: center;
                    border: 1px dashed #000;
                    padding: 10px;
                    margin: 0;
                }
                h1 {
                    font-size: 2rem;
                    margin: 10px 0;
                    font-weight: bold;
                }
                h3 {
                    font-size: 14px;
                    margin: 5px 0;
                }
                p {
                    margin: 3px 0;
                    font-size: 11px;
                }
                .qr-code img {
                    width: 80px;
                    height: 80px;
                }
                hr {
                    border: 0;
                    border-top: 1px dashed #000;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="ticket-container">
                ${ticketContent}
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    // Tunggu konten dimuat lalu print
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}

// Fungsi untuk mengunduh tiket sebagai gambar
function downloadTicketAsImage() {
    const ticketElement = document.getElementById('ticketContainer');
    
    // Konfigurasi html2canvas untuk hasil yang lebih baik
    const options = {
        backgroundColor: '#ffffff',
        scale: 2, // Meningkatkan kualitas gambar
        useCORS: true,
        allowTaint: true,
        width: ticketElement.offsetWidth,
        height: ticketElement.offsetHeight
    };
    
    html2canvas(ticketElement, options).then(function(canvas) {
        // Buat link download
        const link = document.createElement('a');
        link.download = `tiket-antrian-{{ $tiket['nomor_antrian'] ?? 'ticket' }}-{{ date('YmdHis') }}.png`;
        link.href = canvas.toDataURL('image/png');
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }).catch(function(error) {
        console.error('Error generating image:', error);
        alert('Terjadi kesalahan saat mengunduh tiket. Silakan coba lagi.');
    });
}

// Tambahkan event listener untuk keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+P untuk print
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printThermalTicket();
    }
    // Ctrl+S untuk download
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        downloadTicketAsImage();
    }
});
</script>

{{-- Tambahan: Modal untuk preview QR Code --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(json_encode(['nomor_antrian' => $tiket['nomor_antrian'], 'nama_departemen' => $tiket['nama_departemen'], 'nama_loket' => $tiket['nama_loket'], 'timestamp' => time()])) }}" 
                     alt="QR Code" class="img-fluid">
                <p class="mt-3 small text-muted">
                    Scan QR code ini untuk verifikasi tiket
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk modal QR --}}
<script>
// Tambahkan event click pada QR code untuk membuka modal
document.querySelector('.qr-code img').addEventListener('click', function() {
    if (typeof bootstrap !== 'undefined') {
        const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        qrModal.show();
    }
});
</script>

@endsection