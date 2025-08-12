@extends('layouts.landing')

@push('styles')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    :root {
        --primary: #1a3a5f; /* Warna utama, biru tua */
        --secondary: #4CAF50; /* Warna sekunder, hijau */
        --accent: #336021; /* Warna aksen, hijau gelap */
        --light: #f8f9fa;
        --dark: #212529;
        --text: #333;
        --gray: #6c757d;
        --white: #ffffff;
        --light-blue: #e3f2fd;
        --gold: #ffc107;
    }

    body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: var(--text);
        line-height: 1.6;
        min-height: 100vh;
    }

    /* GENERAL CONTAINER */
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* CSS ini khusus untuk menata semua section konten di halaman home */
    .section-title {
        font-size: 2.2rem; text-align: center; margin-bottom: 1.5rem;
        color: var(--primary); position: relative; padding-bottom: 15px;
    }
    .section-title::after {
        content: ''; position: absolute; bottom: 0; left: 50%;
        transform: translateX(-50%); width: 80px; height: 4px;
        background: var(--secondary); border-radius: 2px;
    }
    .section-subtitle { text-align: center; margin-bottom: 2.5rem; color: var(--gray); font-size: 1.1rem; }

    /* Hero Section */
    .hero {
        background: linear-gradient(120deg, var(--primary) 0%, #1a2530 100%);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: var(--white);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 85vh;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
        max-width: 55%;
        padding: 0 20px;
        text-align: left;
    }

    .hero-content h1 {
        font-size: clamp(2rem, 4vw, 3.5rem);
        margin-bottom: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    .hero-content .subtitle {
        font-size: clamp(1.1rem, 2.5vw, 1.5rem);
        margin-bottom: 30px;
        opacity: 0.9;
        max-width: none;
    }

    .divider { 
        width: 80px; 
        height: 4px; 
        background: var(--secondary); 
        margin: 30px 0; 
    }
    .cta-button {
        display: inline-block; background: var(--secondary); color: var(--white);
        padding: 15px 30px; border-radius: 5px; text-decoration: none;
        font-weight: bold; font-size: 18px; transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
        border: none;
        cursor: pointer;
    }
    .cta-button:hover { 
        background: #3d8b40; 
        transform: translateY(-3px); 
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5); 
    }
    .building-image-container { 
        flex: 1; 
        display: flex; 
        justify-content: flex-end; 
        align-items: center; 
        min-width: 300px; 
    }
    .building-image-container img { 
        max-width: 100%; 
        height: auto; 
        max-height: 500px; 
        border-radius: 10px; 
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        transform: perspective(1000px) rotateY(-5deg);
        transition: transform 0.5s ease;
    }
    .building-image-container img:hover {
        transform: perspective(1000px) rotateY(0deg);
    }

    /* Stats Section */
    .stats { padding: 90px 0; background: var(--white); }
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 30px; text-align: center; }
    .stat-card {
        background: var(--light); padding: 30px 20px; border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;
        position: relative; overflow: hidden;
    }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: var(--secondary); }
    .stat-card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
    .loket-number { font-size: 1.1rem; font-weight: 600; color: var(--primary); margin-bottom: 10px; }
    .stat-number { 
        font-size: clamp(2rem, 6vw, 3.5rem); 
        font-weight: 700; 
        color: var(--primary); 
        margin-bottom: 10px; 
        letter-spacing: 1px;
    }
    .stat-text { font-size: clamp(1rem, 2vw, 1.2rem); color: var(--gray); font-weight: 500; }
    .stat-time { 
        font-size: 1.1rem; 
        color: var(--secondary); 
        margin-top: 15px; 
        background: rgba(76, 175, 80, 0.1);
        padding: 8px;
        border-radius: 5px;
        display: inline-block;
        font-weight: 500;
    }
    
    /* Leadership Section */
    .leadership-section { 
        padding: 100px 0; 
        text-align: center; 
        background-color: var(--primary);
        background-image: linear-gradient(rgba(26, 58, 95, 0.9), rgba(26, 58, 95, 0.9)), 
                         url('https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80');
        background-size: cover;
        background-position: center;
        color: var(--white);
        position: relative;
        overflow: hidden;
    }
    .leadership-section .section-title {
        color: var(--white);
    }
    .leadership-section .section-title::after {
        background: var(--gold);
    }
    .leadership-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 30px; }
    .leader-card { 
        background-color: rgba(255, 255, 255, 0.1); 
        padding: 25px; 
        border-radius: 15px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .leader-card:hover { 
        transform: translateY(-10px); 
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        background: rgba(255, 255, 255, 0.15);
    }
    .leader-photo { 
        width: 150px; 
        height: 150px; 
        border-radius: 50%; 
        overflow: hidden; 
        margin: 0 auto 20px; 
        border: 5px solid rgba(255, 255, 255, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
    }
    .leader-photo img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        object-position: center top;
    }
    .leader-photo.no-image {
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.7);
    }
    .leader-name { 
        font-size: 1.3rem; 
        font-weight: 600; 
        margin-bottom: 5px; 
        color: var(--white); 
    }
    .leader-position { 
        color: rgba(255, 255, 255, 0.8); 
        font-weight: 400;
        line-height: 1.4;
    }
    
    /* About & Panduan Section */
    .about-ptsp-section, .panduan-section { padding: 90px 0; }
    .about-ptsp-section { background: var(--light); }
    .panduan-section { background: var(--white); }
    .about-content, .panduan-content { 
        display: flex; 
        align-items: center; 
        gap: 50px; 
        flex-wrap: wrap; 
        justify-content: space-between;
    }
    .image-box { 
        flex: 1; 
        min-width: 300px; 
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .image-box img { 
        width: 100%; 
        display: block; 
        border-radius: 12px; 
        transition: transform 0.5s ease;
    }
    .image-box:hover img {
        transform: scale(1.03);
    }
    .text-content { 
        flex: 1; 
        min-width: 300px; 
    }
    .text-content h2 { 
        font-size: 2rem; 
        color: var(--primary); 
        margin-bottom: 20px; 
        position: relative;
        padding-bottom: 15px;
    }
    .text-content h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 70px;
        height: 4px;
        background: var(--secondary);
    }
    .text-content p {
        margin-bottom: 25px;
        line-height: 1.8;
    }
    
    /* New styles for pelayanan section */
    .pelayanan-container {
        max-height: 300px;
        overflow-y: auto;
        margin-top: 15px;
        padding-right: 15px;
    }
    
    .pelayanan-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .pelayanan-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .pelayanan-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .pelayanan-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .faq-items { 
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .faq-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: var(--white);
        border-radius: 8px; 
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background-color: #f0f8ff;
    }
    
    .faq-item span { 
        font-weight: 500;
        font-size: 1.05rem;
    }
    
    /* Panduan styles */
    .panduan-container {
        max-height: 400px;
        overflow-y: auto;
        margin-top: 15px;
        padding-right: 15px;
    }
    
    .panduan-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .panduan-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .panduan-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .panduan-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .panduan-items {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .panduan-item {
        background: var(--light);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        position: relative;
        border-left: 5px solid var(--secondary);
    }
    
    .panduan-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        background: #f8fff8;
    }
    
    .panduan-number {
        position: absolute;
        top: -10px;
        left: 15px;
        background: var(--secondary);
        color: var(--white);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    }
    
    .panduan-text {
        font-weight: 500;
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--text);
        margin-left: 10px;
        text-align: justify;
    }
    
    .no-panduan {
        text-align: center;
        color: var(--gray);
        font-style: italic;
        padding: 40px;
        background: var(--light);
        border-radius: 12px;
        border: 2px dashed #ddd;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .hero {
            flex-direction: column;
            text-align: center;
            padding: 60px 0;
        }
        
        .hero-content {
            max-width: 100%;
            margin-bottom: 40px;
        }
        
        .divider {
            margin: 30px auto;
        }
        
        .building-image-container {
            justify-content: center;
        }
        
        .building-image-container img {
            max-height: 400px;
        }
        
        .pelayanan-container, .panduan-container {
            max-height: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr 1fr;
        }
        
        .leader-card {
            padding: 20px;
        }
        
        .leader-photo {
            width: 120px;
            height: 120px;
        }
        
        .about-content, .panduan-content {
            flex-direction: column;
        }
        
        .panduan-content {
            flex-direction: column-reverse;
        }
        
        .pelayanan-container, .panduan-container {
            max-height: 200px;
        }
    }

    @media (max-width: 576px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .leader-photo {
            width: 100px;
            height: 100px;
        }
        
        .pelayanan-container, .panduan-container {
            max-height: 180px;
        }
        
        .panduan-item {
            padding: 15px;
        }
        
        .panduan-number {
            width: 25px;
            height: 25px;
            font-size: 0.8rem;
        }
        
        .panduan-text {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<section class="hero">
    <div class="hero-content">
        <h1>Selamat Datang di PTSP Pengadilan Negeri Banyuwangi</h1>
        <p class="subtitle">Kami Akan Memberikan Pelayanan Terbaik Untuk Anda</p>
        
        <div class="divider"></div>
        
        <a href="{{ route('antrian.pilih-layanan') }}" class="cta-button">Mengambil Antrian</a>
    </div>

</section>

<section class="stats">
    <div class="container">
        <h2 class="section-title">Antrian Saat Ini</h2>
        <p class="section-subtitle">Estimasi waktu per antrian: 10 menit</p>
        <div class="stats-container" id="stats-container">
            @if(isset($lokets) && $lokets->isNotEmpty())
                @foreach($lokets as $index => $nama_loket)
                    @php
                        $kodeHuruf = chr(65 + $index); // A, B, C, dst
                    @endphp
                    <div class="stat-card" 
                         data-loket-name="{{ $nama_loket }}" 
                         data-default-code="{{ $kodeHuruf }}-000">
                        <div class="loket-number">{{ $nama_loket }}</div>
                        <div class="stat-number">
                            {{ $kodeHuruf }}-000
                        </div>
                        <div class="stat-text">Tidak ada antrian</div>
                        <div class="stat-time">
                            Siap melayani
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Fallback default jika tidak ada data loket --}}
                <div class="stat-card" data-loket-name="Loket 1" data-default-code="A-000">
                    <div class="loket-number">Loket 1</div>
                    <div class="stat-number">A-000</div>
                    <div class="stat-text">Tidak ada antrian</div>
                    <div class="stat-time">Siap melayani</div>
                </div>
                <div class="stat-card" data-loket-name="Loket 2" data-default-code="B-000">
                    <div class="loket-number">Loket 2</div>
                    <div class="stat-number">B-000</div>
                    <div class="stat-text">Tidak ada antrian</div>
                    <div class="stat-time">Siap melayani</div>
                </div>
                <div class="stat-card" data-loket-name="Loket 3" data-default-code="C-000">
                    <div class="loket-number">Loket 3</div>
                    <div class="stat-number">C-000</div>
                    <div class="stat-text">Tidak ada antrian</div>
                    <div class="stat-time">Siap melayani</div>
                </div>
                <div class="stat-card" data-loket-name="Loket 4" data-default-code="D-000">
                    <div class="loket-number">Loket 4</div>
                    <div class="stat-number">D-000</div>
                    <div class="stat-text">Tidak ada antrian</div>
                    <div class="stat-time">Siap melayani</div>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="leadership-section">
    <div class="container">
        <h2 class="section-title">Petugas Pelayanan Terpadu Satu Pintu</h2>
        <div class="leadership-grid">
            @if(count($petugas) > 0)
                @foreach($petugas as $staff)
                    <div class="leader-card">
                        <div class="leader-photo {{ $staff['foto'] ? '' : 'no-image' }}">
                            @if($staff['foto'])
                                <img src="{{ $staff['foto'] }}" alt="{{ $staff['nama'] }}">
                            @else
                                👤
                            @endif
                        </div>
                        <h3 class="leader-name">{{ $staff['nama'] }}</h3>
                        <p class="leader-position">{{ $staff['nama_loket'] }}</p>
                    </div>
                @endforeach
            @else
                <div class="leader-card">
                    <div class="leader-photo no-image">👤</div>
                    <h3 class="leader-name">Belum Ada Petugas</h3>
                    <p class="leader-position">Sistem Sedang Dalam Pengembangan</p>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="about-ptsp-section">
    <div class="container">
        <div class="about-content">
            <div class="image-box"><img src="{{ asset('images/foto-ptsp.jpg') }}" alt="Tentang PTSP"></div>
            <div class="text-content">
                <h2>Tentang E-PTSP</h2>
                <p>Nikmati kemudahan layanan PTSP Pengadilan Negeri Banyuwangi dengan sistem pengambilan antrean online kami. Cukup pilih layanan yang Anda inginkan, dapatkan nomor antrean, dan datang sesuai jadwal tanpa menunggu lama.</p>
                <h2>Pelayanan yang Tersedia</h2>
                
                <div class="pelayanan-container">
                    <div class="faq-items">
                        @php 
                            $allPelayanans = [];
                            foreach ($departemens as $departemen) {
                                foreach ($departemen->pelayanans as $pelayanan) {
                                    $allPelayanans[] = $pelayanan;
                                }
                            }
                        @endphp
                        
                        @if(count($allPelayanans) > 0)
                            @foreach($allPelayanans as $pelayanan)
                                <div class="faq-item">
                                    <span>{{ $pelayanan->nama_layanan }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-center">Belum ada layanan yang tersedia</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="panduan-section">
    <div class="container">
        <div class="panduan-content">
            <div class="text-content">
                <h2>Panduan Pengambilan Tiket</h2>
                <p>Ikuti langkah-langkah berikut untuk mengambil nomor antrian dengan mudah dan efisien melalui sistem E-PTSP kami.</p>
                
                <div class="panduan-container">
                    @if(count($panduans) > 0)
                        <div class="panduan-items">
                            @foreach($panduans as $index => $panduan)
                                <div class="panduan-item">
                                    <div class="panduan-number">{{ $index + 1 }}</div>
                                    <div class="panduan-text">{{ $panduan->isi_panduan }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-panduan">
                            <p>Panduan pengambilan tiket belum tersedia. Silakan hubungi petugas untuk informasi lebih lanjut.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="image-box">
                <img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Panduan Pengambilan Tiket">
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Fungsi untuk update data antrian real-time
    function updateQueueInfo() {
        // Konstanta waktu pelayanan (dalam menit) - DIPINDAHKAN KE DALAM FUNGSI
        const WAKTU_PELAYANAN_RATA_RATA = 7;
        const WAKTU_PELAYANAN_MINIMAL = 5;
        
        fetch('/api/antrian_all', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data) {
                // Reset semua card ke default
                document.querySelectorAll('.stat-card').forEach(card => {
                    const nomorElement = card.querySelector('.stat-number');
                    const waktuElement = card.querySelector('.stat-time');
                    const textElement = card.querySelector('.stat-text');
                    
                    if (nomorElement && waktuElement && textElement) {
                        nomorElement.textContent = card.dataset.defaultCode;
                        textElement.textContent = 'Tidak ada antrian';
                        waktuElement.textContent = 'Siap melayani';
                        waktuElement.style.backgroundColor = '';
                        waktuElement.style.color = '';
                        card.classList.remove('active');
                    }
                });

                // Proses data untuk setiap loket
                data.data.forEach(loketData => {
                    if (loketData.antrian && loketData.antrian.length > 0) {
                        // 1. Hitung jumlah antrian menunggu
                        const antrianMenunggu = loketData.antrian.filter(a => a.status_antrian == 1);
                        const jumlahMenunggu = antrianMenunggu.length;
                        
                        // 2. Hitung estimasi waktu
                        let estimasiMenit = Math.max(
                            jumlahMenunggu * WAKTU_PELAYANAN_MINIMAL,
                            Math.floor(jumlahMenunggu * WAKTU_PELAYANAN_RATA_RATA * 0.7)
                        );
                        
                        // 3. Format estimasi waktu
                        let estimasiText = `Estimasi: ${estimasiMenit} menit`;
                        if (estimasiMenit > 60) {
                            const jam = Math.floor(estimasiMenit / 60);
                            const menit = estimasiMenit % 60;
                            estimasiText = `Estimasi: ${jam} jam ${menit} menit`;
                        }
                        
                        // 4. Cari antrian aktif (sedang dipanggil atau berikutnya)
                        const antrianAktif = loketData.antrian.find(a => a.status_antrian == 2) || 
                                           antrianMenunggu[0];
                        
                        if (antrianAktif) {
                            // 5. Cari card yang sesuai dengan nama loket
                            const card = document.querySelector(`.stat-card[data-loket-name="${loketData.loket}"]`);
                            
                            if (card) {
                                const nomorElement = card.querySelector('.stat-number');
                                const waktuElement = card.querySelector('.stat-time');
                                const textElement = card.querySelector('.stat-text');
                                
                                // 6. Update informasi antrian
                                nomorElement.textContent = antrianAktif.kode_antrian;
                                textElement.textContent = antrianAktif.nama_departemen || 'Layanan';
                                
                                // 7. Tampilkan status khusus untuk antrian yang dipanggil
                                if (antrianAktif.status_antrian == 2) {
                                    waktuElement.textContent = 'Sedang dilayani';
                                    waktuElement.style.backgroundColor = '#4CAF50';
                                    waktuElement.style.color = 'white';
                                } else {
                                    waktuElement.textContent = estimasiText;
                                }
                                
                                // 8. Tambahkan efek visual
                                card.classList.add('active');
                                card.style.transform = 'scale(1.02)';
                                card.style.boxShadow = '0 8px 25px rgba(76, 175, 80, 0.2)';
                                
                                setTimeout(() => {
                                    card.style.transform = '';
                                    card.style.boxShadow = '';
                                }, 300);
                            }
                        }
                    }
                });
                
                console.log('✅ Data antrian berhasil diperbarui:', new Date().toLocaleTimeString());
            }
        })
        .catch(error => {
            console.error('❌ Gagal mengambil data antrian:', error);
        });
    }


    // Fungsi untuk update indikator waktu
    function updateTimeIndicator() {
        const indicator = document.getElementById('update-indicator');
        if (indicator) {
            indicator.style.opacity = '1';
            setTimeout(() => {
                indicator.style.opacity = '0.7';
            }, 1000);
        }
    }

    // Fungsi untuk menampilkan notifikasi error (opsional)
    function showErrorNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff6b6b;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 1000;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Update pertama kali
        updateQueueInfo();
        
        // Update otomatis setiap 15 detik
        setInterval(updateQueueInfo, 15000);
        
        // Tambahkan indikator live update
        const statsTitle = document.querySelector('.stats .section-title');
        if (statsTitle) {
            const indicator = document.createElement('span');
            indicator.innerHTML = ' <span id="update-indicator" style="color: #4CAF50; font-size: 0.8rem; opacity: 0.7;">';
            statsTitle.appendChild(indicator);
        }
        
        console.log('🚀 Sistem antrian real-time dimulai');
    });

    // Animasi hover untuk stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.style.transform.includes('scale')) {
                this.style.transform = 'translateY(-10px) scale(1.02)';
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.15)';
                this.style.transition = 'all 0.3s ease';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('updating')) {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.08)';
            }
        });
    });

    // CSS tambahan untuk efek visual
    const style = document.createElement('style');
    style.textContent = `
        .stat-card.active {
            border-left: 5px solid #4CAF50;
            background: linear-gradient(135deg, #ffffff 0%, #f8fff8 100%);
        }
        
        .stat-card.active .stat-number {
            color: #2c5f2d;
            font-weight: 800;
        }
        
        #update-indicator {
            transition: opacity 0.3s ease;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 5px 15px rgba(76, 175, 80, 0.1); }
            50% { box-shadow: 0 10px 25px rgba(76, 175, 80, 0.3); }
            100% { box-shadow: 0 5px 15px rgba(76, 175, 80, 0.1); }
        }
        
        .stat-card.active {
            animation: pulse 3s infinite;
        }
    `;
    document.head.appendChild(style);

    // Animasi untuk elemen lainnya
    document.querySelectorAll('.faq-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    document.querySelectorAll('.panduan-item').forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.6s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100 + 200);

        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    const panduanContainer = document.querySelector('.panduan-container');
    if (panduanContainer) {
        let isScrolling = false;
        
        panduanContainer.addEventListener('scroll', function() {
            if (!isScrolling) {
                window.requestAnimationFrame(function() {
                    panduanContainer.style.boxShadow = 'inset 0 0 10px rgba(0,0,0,0.1)';
                    
                    setTimeout(() => {
                        panduanContainer.style.boxShadow = 'none';
                    }, 150);
                    
                    isScrolling = false;
                });
                isScrolling = true;
            }
        });
    }
</script>
@endpush