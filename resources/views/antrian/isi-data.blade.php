@extends('layouts.landing')

@push('styles')
    {{-- Memuat ikon dan file CSS asli Anda --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Pastikan nama file CSS ini sesuai dengan milik Anda --}}
    <link rel="stylesheet" href="{{ asset('css/antrian/isi-data.css') }}"> 
@endpush

@section('content')
<div class="page-container">
    <div class="form-panel">
        <div class="section-heading">
            <h2>Formulir Data Diri</h2>
            <p>{{ $layanan['nama_layanan'] }}</p>
        </div>
        
        <form action="{{ route('antrian.buat-tiket') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
            <input type="file" name="foto_wajah" id="foto_wajah_file" class="d-none" accept="image/jpeg, image/png, image/jpg">

            {{-- Blok Error yang Sudah Diperbaiki --}}
            @if ($errors->any() || session('error'))
                <div class="server-error-box">
                    <h5 style="font-weight:700; margin-bottom: 0.5rem;">Terjadi Kesalahan:</h5>
                    <ul style="padding-left: 1.2rem; margin-bottom:0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @if (session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="form-main-layout">
                <div class="form-column">
                    <h4 class="form-section-title">Informasi Personal</h4>

                    <div class="form-group">
                        <label for="no_hp">No. Handphone</label>
                        <input 
                            type="tel" 
                            class="form-control @error('no_hp') is-invalid @enderror" 
                            id="no_hp" 
                            name="no_hp" 
                            value="{{ old('no_hp') }}" 
                            required
                            minlength="10"
                            maxlength="13"
                            pattern="\d{10,13}"
                            title="Nomor HP harus terdiri dari 10 hingga 13 digit angka.">
                        @error('no_hp')
                            <div class="alert-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="nama_pengunjung">Nama Lengkap</label>
                        <input type="text" class="form-control @error('nama_pengunjung') is-invalid @enderror" id="nama_pengunjung" name="nama_pengunjung" value="{{ old('nama_pengunjung') }}" required>
                    </div>              
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="" selected disabled>Pilih Jenis Kelamin...</option>
                            <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat') }}</textarea>
                    </div>
                </div>

                <div class="photo-column">
                    <h4 class="form-section-title">Foto Wajah (Wajib)</h4>
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
</div>

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
<script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeStream = null;
    let currentCameraType = null;
    
    const mainForm = document.querySelector('form');
    const faceFileInput = document.getElementById('foto_wajah_file');
    const facePreview = document.getElementById('face-preview');
    const submitBtn = document.getElementById('submit-btn');
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

        const errorElement = faceErrorJs;
        errorElement.style.display = 'none';

        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            errorElement.textContent = `File terlalu besar (Maks 2MB).`;
            errorElement.style.display = 'block';
            faceFileInput.value = '';
            return;
        }

        const preview = facePreview;
        
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
                const compressedFile = new File([result], file.name, { type: result.type, lastModified: file.lastModified });
                dataTransfer.items.add(compressedFile);
                
                faceFileInput.files = dataTransfer.files;

                document.querySelectorAll(`#${type}-zone .upload-actions button:not([id^='retake'])`).forEach(btn => btn.classList.add('d-none'));
                document.getElementById(`retake-${type}-btn`).classList.remove('d-none');
            },
            error(err) {
                console.error('Compressor Error:', err);
                errorElement.textContent = 'Format file tidak didukung. Gunakan JPG/PNG.';
                errorElement.style.display = 'block';
                faceFileInput.value = '';
            },
        });
    }

    function resetPhoto(type) {
        const preview = facePreview;
        const fileInput = faceFileInput;
        const errorElement = faceErrorJs;
        
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
        const facingMode = 'user';
        cameraModalTitle.textContent = 'Posisikan Wajah di Tengah';
        
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
        
        cameraCanvas.toBlob(blob => {
            const file = new File([blob], 'face_capture.jpg', { type: 'image/jpeg', lastModified: Date.now() });
            handlePhotoTaken(currentCameraType, file);
        }, 'image/jpeg', 0.9);

        closeModal();
    });
    
    document.getElementById('start-face-camera-btn').addEventListener('click', () => openCameraModal('face'));
    closeModalBtn.addEventListener('click', closeModal);
    document.getElementById('upload-face-btn').addEventListener('click', () => faceFileInput.click());
    faceFileInput.addEventListener('change', (e) => { if(e.target.files.length) handlePhotoTaken('face', e.target.files[0]); });
    document.getElementById('retake-face-btn').addEventListener('click', () => resetPhoto('face'));

    mainForm.addEventListener('submit', function(e) {
        if (!mainForm.checkValidity()) {
            e.preventDefault();
            mainForm.reportValidity();
            return;
        }
        
        const noHpInput = document.getElementById('no_hp');
        const noHpValue = noHpInput.value;
        if (noHpValue.length < 10 || noHpValue.length > 13) {
            e.preventDefault();
            alert('Nomor HP harus terdiri dari 10 hingga 13 digit.');
            noHpInput.focus();
            return;
        }

        if (!faceFileInput.files.length) {
            e.preventDefault();
            alert('Harap unggah foto wajah sebelum melanjutkan.');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    document.getElementById('no_hp').addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    window.addEventListener('beforeunload', stopActiveStream);
});
</script>
@endpush