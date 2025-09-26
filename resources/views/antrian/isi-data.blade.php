@extends('layouts.landing')

@push('styles')
    {{-- Memuat ikon dan file CSS yang baru dibuat --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/isi-data.css') }}">
@endpush

@section('content')
<div class="page-container">
    {{-- Container ini sekarang tidak diperlukan karena sudah ada di dalam .page-container --}}
    {{-- <div class="container"> --}}
        <div class="form-panel">
            <div class="section-heading">
                <h2>Formulir Data Diri</h2>
                <p>{{ $layanan['nama_layanan'] }}</p>
            </div>
            
            <form action="{{ route('antrian.buat-tiket') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
                <input type="file" name="foto_ktp" id="foto_ktp_file" class="d-none" accept="image/jpeg, image/png, image/jpg">
                <input type="file" name="foto_wajah" id="foto_wajah_file" class="d-none" accept="image/jpeg, image/png, image/jpg">

                @if ($errors->any())
                    <div class="server-error-box">
                        <h5 style="font-weight:700; margin-bottom: 0.5rem;">Terjadi Kesalahan:</h5>
                        <ul style="padding-left: 1.2rem; margin-bottom:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h4 class="form-section-title">Informasi Personal</h4>
                <div class="form-group">
                    <label for="nik">NIK (Nomor Induk Kependudukan)</label>
                    <input 
                        type="text" 
                        class="form-control @error('nik') is-invalid @enderror" 
                        id="nik" 
                        name="nik" 
                        value="{{ old('nik') }}" 
                        required 
                        minlength="16" 
                        maxlength="16" 
                        pattern="\d{16}"
                        placeholder="Masukkan 16 digit NIK Anda">
                    @error('nik')
                        <div class="alert-error" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nama_pengunjung">Nama Lengkap (Sesuai KTP)</label>
                    <input type="text" class="form-control @error('nama_pengunjung') is-invalid @enderror" id="nama_pengunjung" name="nama_pengunjung" value="{{ old('nama_pengunjung') }}" required>
                    @error('nama_pengunjung')
                        <div class="alert-error" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="no_hp">No. Handphone</label>
                    <input type="tel" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required>
                </div>                
                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="" selected disabled>Pilih Jenis Kelamin...</option>
                        <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                </div>

                <h4 class="form-section-title mt-5">Verifikasi Foto (Wajib)</h4>
                <div class="row-input">
                    <div class="upload-zone" id="ktp-zone">
                        <h5><i class="bi bi-person-vcard-fill me-2"></i>Foto KTP</h5>
                        <img id="ktp-preview" src="#" alt="Preview KTP" class="preview-image d-none">
                        <div class="upload-actions">
                            <button type="button" id="start-ktp-camera-btn" class="btn btn-sm btn-secondary-outline">Buka Kamera</button>
                            <button type="button" id="upload-ktp-btn" class="btn btn-sm btn-secondary-outline">Pilih File</button>
                            <button type="button" id="retake-ktp-btn" class="btn btn-sm btn-secondary-outline d-none">Ulangi</button>
                        </div>
                        @error('foto_ktp')
                            <div class="alert-error" style="display: block;">{{ $message }}</div>
                        @enderror
                        <div class="alert-error" id="ktp-error-js"></div>
                    </div>
                     <div class="upload-zone" id="face-zone">
                        <h5><i class="bi bi-camera-fill me-2"></i>Foto Wajah</h5>
                        <img id="face-preview" src="#" alt="Preview Wajah" class="preview-image d-none">
                        <div class="upload-actions">
                            <button type="button" id="start-face-camera-btn" class="btn btn-sm btn-secondary-outline">Buka Kamera</button>
                            <button type="button" id="upload-face-btn" class="btn btn-sm btn-secondary-outline">Pilih File</button>
                            <button type="button" id="retake-face-btn" class="btn btn-sm btn-secondary-outline d-none">Ulangi</button>
                        </div>
                        @error('foto_wajah')
                            <div class="alert-error" style="display: block;">{{ $message }}</div>
                        @enderror
                        <div class="alert-error" id="face-error-js"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('antrian.pilih-layanan') }}" class="btn btn-secondary-outline">Kembali</a>
                    <button type="submit" class="btn btn-primary-submit" id="submit-btn">
                        <i class="bi bi-ticket-detailed-fill me-2"></i>Dapatkan Tiket
                        <div class="spinner-border ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </form>
        </div>
    {{-- </div> --}}
</div>

{{-- Kode HTML untuk Modal Kamera tidak berubah --}}
<div class="camera-modal-backdrop d-none" id="cameraModal">
    <div class="camera-modal-content">
        <h4 id="camera-modal-title">Arahkan Kamera</h4>
        <video id="camera-video" class="camera-modal-video" autoplay playsinline></video>
        <canvas id="camera-canvas" class="d-none"></canvas>
        <div class="camera-modal-actions">
            <button type="button" id="capture-btn" class="btn btn-sm btn-primary-submit">Ambil Gambar</button>
            <button type="button" id="close-modal-btn" class="btn btn-sm btn-secondary-outline">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Seluruh kode JavaScript Anda tetap sama persis --}}
<script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ... Seluruh kode JavaScript Anda ada di sini ...
    let activeStream = null;
    let currentCameraType = null;
    
    const mainForm = document.querySelector('form');
    const ktpFileInput = document.getElementById('foto_ktp_file');
    const faceFileInput = document.getElementById('foto_wajah_file');
    const ktpPreview = document.getElementById('ktp-preview');
    const facePreview = document.getElementById('face-preview');
    const submitBtn = document.getElementById('submit-btn');

    const ktpErrorJs = document.getElementById('ktp-error-js');
    const faceErrorJs = document.getElementById('face-error-js');

    const cameraModal = document.getElementById('cameraModal');
    const cameraVideo = document.getElementById('camera-video');
    const cameraCanvas = document.getElementById('camera-canvas');
    const captureBtn = document.getElementById('capture-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cameraModalTitle = document.getElementById('camera-modal-title');

    function stopActiveStream() {
        if (activeStream) {
            activeStream.getTracks().forEach(track => track.stop());
            activeStream = null;
        }
    }

    function handlePhotoTaken(type, file) {
        if (!file) return;

        const errorElement = (type === 'ktp') ? ktpErrorJs : faceErrorJs;
        errorElement.style.display = 'none';

        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            errorElement.textContent = `File terlalu besar (Maks 2MB).`;
            errorElement.style.display = 'block';
            const input = type === 'ktp' ? ktpFileInput : faceFileInput;
            input.value = '';
            return;
        }

        const preview = (type === 'ktp') ? ktpPreview : facePreview;
        
        new Compressor(file, {
            quality: 0.6,
            maxWidth: 1024,
            maxHeight: 1024,
            success(result) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(result);

                const dataTransfer = new DataTransfer();
                
                const compressedFile = new File([result], file.name, {
                    type: result.type,
                    lastModified: file.lastModified,
                });
                dataTransfer.items.add(compressedFile);
                
                const fileInput = (type === 'ktp') ? ktpFileInput : faceFileInput;
                fileInput.files = dataTransfer.files;

                document.querySelectorAll(`#${type}-zone .upload-actions button:not([id^='retake'])`).forEach(btn => btn.classList.add('d-none'));
                document.getElementById(`retake-${type}-btn`).classList.remove('d-none');
            },
            error(err) {
                console.error('Compressor Error:', err);
                errorElement.textContent = 'Format file tidak didukung. Gunakan JPG/PNG.';
                errorElement.style.display = 'block';
                const input = type === 'ktp' ? ktpFileInput : faceFileInput;
                input.value = '';
            },
        });
    }

    function resetPhoto(type) {
        const preview = (type === 'ktp') ? ktpPreview : facePreview;
        const fileInput = (type === 'ktp') ? ktpFileInput : faceFileInput;
        const errorElement = (type === 'ktp') ? ktpErrorJs : faceErrorJs;
        
        preview.classList.add('d-none');
        preview.removeAttribute('src');
        fileInput.value = '';
        errorElement.style.display = 'none';

        document.querySelectorAll(`#${type}-zone .upload-actions button:not([id^='retake'])`).forEach(btn => btn.classList.remove('d-none'));
        document.getElementById(`retake-${type}-btn`).classList.add('d-none');
    }
    
    async function openCameraModal(type) {
        stopActiveStream();
        currentCameraType = type;
        const facingMode = (type === 'ktp') ? 'environment' : 'user';
        cameraModalTitle.textContent = (type === 'ktp') ? 'Arahkan Kamera ke KTP Anda' : 'Posisikan Wajah di Tengah';
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode } });
            activeStream = stream;
            cameraVideo.srcObject = stream;
            await cameraVideo.play();
            cameraModal.classList.remove('d-none');
        } catch (err) {
            alert('Gagal mengakses kamera. Pastikan izin telah diberikan.');
            console.error(err);
        }
    }

    function closeModal() {
        cameraModal.classList.add('d-none');
        stopActiveStream();
    }

    captureBtn.addEventListener('click', () => {
        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;
        cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0);
        
        const fileName = (currentCameraType === 'ktp') ? 'ktp_capture.jpg' : 'face_capture.jpg';
        cameraCanvas.toBlob(blob => {
            const file = new File([blob], fileName, { type: 'image/jpeg', lastModified: Date.now() });
            handlePhotoTaken(currentCameraType, file);
        }, 'image/jpeg', 0.9);

        closeModal();
    });
    
    document.getElementById('start-ktp-camera-btn').addEventListener('click', () => openCameraModal('ktp'));
    document.getElementById('start-face-camera-btn').addEventListener('click', () => openCameraModal('face'));
    closeModalBtn.addEventListener('click', closeModal);

    document.getElementById('upload-ktp-btn').addEventListener('click', () => ktpFileInput.click());
    document.getElementById('upload-face-btn').addEventListener('click', () => faceFileInput.click());

    ktpFileInput.addEventListener('change', (e) => { if(e.target.files.length) handlePhotoTaken('ktp', e.target.files[0]); });
    faceFileInput.addEventListener('change', (e) => { if(e.target.files.length) handlePhotoTaken('face', e.target.files[0]); });

    document.getElementById('retake-ktp-btn').addEventListener('click', () => resetPhoto('ktp'));
    document.getElementById('retake-face-btn').addEventListener('click', () => resetPhoto('face'));

    mainForm.addEventListener('submit', function(e) {
        if (!mainForm.checkValidity()) {
            e.preventDefault();
            mainForm.reportValidity();
            return;
        }

        if (!ktpFileInput.files.length || !faceFileInput.files.length) {
            e.preventDefault();
            alert('Harap unggah foto KTP dan foto wajah sebelum melanjutkan.');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    document.getElementById('no_hp').addEventListener('input', e => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('0')) {
            value = '62' + value.substring(1);
        }
        e.target.value = value;
    });

    window.addEventListener('beforeunload', stopActiveStream);
});
</script>
@endpush