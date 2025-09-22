@extends('layouts.landing')

@push('styles')
{{-- Bootstrap 5 Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    .page-container {
        padding: 60px 24px;
        background-color: #f4f7f6;
    }
    .section-heading { text-align: center; margin-bottom: 40px; }
    .section-heading h2 { font-family: var(--font-heading); font-size: clamp(32px, 5vw, 42px); font-weight: 700; color: var(--primary-green); }
    .section-heading p { font-size: 1.2rem; color: #555; max-width: 700px; margin: 10px auto 0 auto; font-weight: 500; }
    .form-panel { max-width: 800px; margin: 0 auto; background-color: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border: 1px solid #e9ecef; }
    .form-section-title { font-family: var(--font-heading); font-size: 1.75rem; font-weight: 700; color: var(--text-dark); margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e9ecef; }
    .form-group { margin-bottom: 1.25rem; }
    .form-group label { display: block; font-weight: 600; font-size: 0.9rem; color: #343a40; margin-bottom: 8px; }
    .form-control, .form-select { width: 100%; padding: 12px 16px; font-size: 1rem; border: 1px solid #ced4da; border-radius: 8px; transition: border-color 0.2s, box-shadow 0.2s; font-family: var(--font-body); }
    .form-control:focus, .form-select:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(10, 69, 49, 0.15); outline: none; }
    .row-input { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .upload-zone { background-color: #f8f9fa; border: 1px dashed #ced4da; border-radius: 8px; padding: 24px; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .upload-zone h5 { font-weight: 600; color: #495057; margin-bottom: 16px; }
    .preview-image { max-width: 100%; max-height: 150px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #ddd; object-fit: cover; }
    .upload-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
    .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 40px; padding-top: 24px; border-top: 1px solid #e9ecef; }
    .btn { padding: 12px 24px; font-weight: 600; border-radius: 50px; border: 2px solid transparent; transition: all 0.2s ease; cursor: pointer; }
    .btn-primary-submit { background-color: var(--primary-green); color: #fff; }
    .btn-primary-submit:hover { background-color: #083828; transform: translateY(-2px); }
    .btn-primary-submit:disabled { background-color: #aaa; cursor: not-allowed; transform: none; }
    .btn-secondary-outline { background-color: transparent; color: #495057; border-color: #ced4da; }
    .btn-secondary-outline:hover { background-color: #f8f9fa; border-color: #adb5bd; }
    .btn-sm { padding: 8px 16px; font-size: 0.875rem; }
    .d-none { display: none !important; }
    .spinner-border { display: inline-block; width: 1rem; height: 1rem; vertical-align: text-bottom; border: .2em solid currentColor; border-right-color: transparent; border-radius: 50%; animation: spinner-border .75s linear infinite; }
    @keyframes spinner-border { to { transform: rotate(360deg); } }
    .camera-modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); z-index: 1050; display: flex; justify-content: center; align-items: center; }
    .camera-modal-content { background-color: #fff; padding: 20px; border-radius: 12px; max-width: 640px; width: 95%; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
    .camera-modal-video { width: 100%; height: auto; border-radius: 8px; background-color: #000; }
    .camera-modal-actions { margin-top: 15px; display: flex; justify-content: center; gap: 15px; }
    @media (max-width: 768px) {
        .row-input { grid-template-columns: 1fr; }
        .form-panel { padding: 24px; }
        .form-actions { flex-direction: column-reverse; }
        .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="container">
        <div class="form-panel">
            <div class="section-heading">
                <h2>Formulir Data Diri</h2>
                <p>{{ $layanan['nama_layanan'] }}</p>
            </div>
            
            <form action="{{ route('antrian.buat-tiket') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
                <input type="file" name="foto_ktp" id="foto_ktp_file" class="d-none" accept="image/*">
                <input type="file" name="foto_wajah" id="foto_wajah_file" class="d-none" accept="image/*">

                <h4 class="form-section-title">Informasi Personal</h4>
                <div class="form-group"><label for="nama_pengunjung">Nama Lengkap (Sesuai KTP)</label><input type="text" class="form-control" id="nama_pengunjung" name="nama_pengunjung" value="{{ old('nama_pengunjung') }}" required></div>
                {{-- code dengan nik --}}
                {{-- <div class="row-input"><div class="form-group"><label for="nik">NIK</label><input type="text" inputmode="numeric" class="form-control" id="nik" name="nik" value="{{ old('nik') }}" required></div><div class="form-group"><label for="no_hp">No. Handphone</label><input type="tel" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"></div></div> --}}
                {{-- code tanpa nik --}}
                <div class="form-group"><label for="no_hp">No. Handphone</label><input type="tel" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required></div>                
                <div class="form-group"><label for="jenis_kelamin">Jenis Kelamin</label><select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required><option value="" selected disabled>Pilih Jenis Kelamin...</option><option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option><option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option></select></div>
                <div class="form-group"><label for="alamat">Alamat Lengkap</label><textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea></div>

                <h4 class="form-section-title mt-5">Verifikasi Foto (Opsional)</h4>
                <div class="row-input">
                    <div class="upload-zone" id="ktp-zone">
                        <h5><i class="bi bi-person-vcard-fill me-2"></i>Foto KTP</h5>
                        <img id="ktp-preview" src="#" alt="Preview KTP" class="preview-image d-none">
                        <div class="upload-actions">
                            <button type="button" id="start-ktp-camera-btn" class="btn btn-sm btn-secondary-outline">Buka Kamera</button>
                            <button type="button" id="upload-ktp-btn" class="btn btn-sm btn-secondary-outline">Pilih File</button>
                            <button type="button" id="retake-ktp-btn" class="btn btn-sm btn-secondary-outline d-none">Ulangi</button>
                        </div>
                    </div>
                     <div class="upload-zone" id="face-zone">
                        <h5><i class="bi bi-camera-fill me-2"></i>Foto Wajah</h5>
                        <img id="face-preview" src="#" alt="Preview Wajah" class="preview-image d-none">
                        <div class="upload-actions">
                            <button type="button" id="start-face-camera-btn" class="btn btn-sm btn-secondary-outline">Buka Kamera</button>
                            <button type="button" id="upload-face-btn" class="btn btn-sm btn-secondary-outline">Pilih File</button>
                            <button type="button" id="retake-face-btn" class="btn btn-sm btn-secondary-outline d-none">Ulangi</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('antrian.pilih-layanan') }}" class="btn btn-secondary-outline">Kembali</a>
                    {{-- [DIPERBAIKI] Atribut 'disabled' dihapus agar tombol aktif dari awal --}}
                    <button type="submit" class="btn btn-primary-submit" id="submit-btn">
                        <i class="bi bi-ticket-detailed-fill me-2"></i>Dapatkan Tiket
                        <div class="spinner-border ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="camera-modal-backdrop d-none" id="ktpCameraModal">
    <div class="camera-modal-content">
        <h4>Arahkan Kamera ke KTP Anda</h4>
        <video id="ktp-camera-video" class="camera-modal-video" autoplay playsinline></video>
        <canvas id="ktp-canvas" class="d-none"></canvas>
        <div class="camera-modal-actions">
            <button type="button" id="capture-ktp-btn" class="btn btn-sm btn-primary-submit">Ambil Gambar</button>
            <button type="button" id="close-ktp-modal-btn" class="btn btn-sm btn-secondary-outline">Tutup</button>
        </div>
    </div>
</div>

<div class="camera-modal-backdrop d-none" id="faceCameraModal">
    <div class="camera-modal-content">
        <h4>Posisikan Wajah di Tengah</h4>
        <video id="face-camera-video" class="camera-modal-video" autoplay playsinline></video>
        <canvas id="face-canvas" class="d-none"></canvas>
        <div class="camera-modal-actions">
            <button type="button" id="capture-face-btn" class="btn btn-sm btn-primary-submit">Ambil Gambar</button>
            <button type="button" id="close-face-modal-btn" class="btn btn-sm btn-secondary-outline">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let ktpStream = null;
    let faceStream = null;
    
    // Element selectors
    const mainForm = document.querySelector('form');
    const ktpFileInput = document.getElementById('foto_ktp_file');
    const faceFileInput = document.getElementById('foto_wajah_file');
    const ktpPreview = document.getElementById('ktp-preview');
    const facePreview = document.getElementById('face-preview');
    const submitBtn = document.getElementById('submit-btn');

    const ktpCameraModal = document.getElementById('ktpCameraModal');
    const faceCameraModal = document.getElementById('faceCameraModal');
    const ktpVideo = document.getElementById('ktp-camera-video');
    const faceVideo = document.getElementById('face-camera-video');
    const ktpCanvas = document.getElementById('ktp-canvas');
    const faceCanvas = document.getElementById('face-canvas');

    function stopAllStreams() {
        if (ktpStream) ktpStream.getTracks().forEach(track => track.stop());
        if (faceStream) faceStream.getTracks().forEach(track => track.stop());
        ktpStream = null;
        faceStream = null;
    }

    // [DIHAPUS] Fungsi checkFormCompletion() tidak diperlukan lagi
    // function checkFormCompletion() { ... }

    function handlePhotoTaken(type) {
        const preview = (type === 'ktp') ? ktpPreview : facePreview;
        const fileInput = (type === 'ktp') ? ktpFileInput : faceFileInput;
        const file = fileInput.files[0];

        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);

        document.querySelectorAll(`#${type}-zone .upload-actions button:not([id^='retake'])`).forEach(btn => btn.classList.add('d-none'));
        document.getElementById(`retake-${type}-btn`).classList.remove('d-none');
    }

    function resetPhoto(type) {
        const preview = (type === 'ktp') ? ktpPreview : facePreview;
        const fileInput = (type === 'ktp') ? ktpFileInput : faceFileInput;
        
        preview.classList.add('d-none');
        preview.removeAttribute('src');
        fileInput.value = '';

        document.querySelectorAll(`#${type}-zone .upload-actions button:not([id^='retake'])`).forEach(btn => btn.classList.remove('d-none'));
        document.getElementById(`retake-${type}-btn`).classList.add('d-none');
    }
    
    async function openCameraModal(type) {
        stopAllStreams();
        const modal = (type === 'ktp') ? ktpCameraModal : faceCameraModal;
        const video = (type === 'ktp') ? ktpVideo : faceVideo;
        const facingMode = (type === 'ktp') ? 'environment' : 'user';
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode, width: { ideal: 1280 }, height: { ideal: 720 } } });
            if (type === 'ktp') ktpStream = stream;
            else faceStream = stream;
            
            video.srcObject = stream;
            video.play();
            modal.classList.remove('d-none');
        } catch (err) {
            alert('Gagal mengakses kamera. Pastikan izin telah diberikan dan coba lagi.');
            console.error(err);
        }
    }

    function closeModal(type) {
        const modal = (type === 'ktp') ? ktpCameraModal : faceCameraModal;
        modal.classList.add('d-none');
        stopAllStreams();
    }

    function captureImage(type) {
        const video = (type === 'ktp') ? ktpVideo : faceVideo;
        const canvas = (type === 'ktp') ? ktpCanvas : faceCanvas;
        const fileInput = (type === 'ktp') ? ktpFileInput : faceFileInput;
        const fileName = (type === 'ktp') ? 'ktp_capture.jpg' : 'face_capture.jpg';

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        canvas.toBlob(blob => {
            const file = new File([blob], fileName, { type: 'image/jpeg', lastModified: Date.now() });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            handlePhotoTaken(type);
        }, 'image/jpeg', 0.9);

        closeModal(type);
    }
    
    // Event Listeners untuk Tombol-Tombol
    document.getElementById('start-ktp-camera-btn').addEventListener('click', () => openCameraModal('ktp'));
    document.getElementById('close-ktp-modal-btn').addEventListener('click', () => closeModal('ktp'));
    document.getElementById('capture-ktp-btn').addEventListener('click', () => captureImage('ktp'));
    
    document.getElementById('start-face-camera-btn').addEventListener('click', () => openCameraModal('face'));
    document.getElementById('close-face-modal-btn').addEventListener('click', () => closeModal('face'));
    document.getElementById('capture-face-btn').addEventListener('click', () => captureImage('face'));

    document.getElementById('upload-ktp-btn').addEventListener('click', () => ktpFileInput.click());
    document.getElementById('upload-face-btn').addEventListener('click', () => faceFileInput.click());

    ktpFileInput.addEventListener('change', () => handlePhotoTaken('ktp'));
    faceFileInput.addEventListener('change', () => handlePhotoTaken('face'));

    document.getElementById('retake-ktp-btn').addEventListener('click', () => resetPhoto('ktp'));
    document.getElementById('retake-face-btn').addEventListener('click', () => resetPhoto('face'));

    // Event Listener untuk SUBMIT FORM (LOGIKA DIPERBAIKI)
    mainForm.addEventListener('submit', function(e) {
        // [DIHAPUS] Pengecekan wajib foto dihapus dari sini
        // if (!isKtpCaptured || !isFaceCaptured) { ... }

        // Cek validasi bawaan browser untuk field lain (nama, nik, dll)
        if (!mainForm.checkValidity()) {
            e.preventDefault(); // Hentikan submit jika ada field required yang kosong
            mainForm.reportValidity();
            return;
        }
        
        // Jika validasi lolos, lanjutkan proses submit
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    // Input formatters
    document.getElementById('no_hp').addEventListener('input', e => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('0')) {
            value = '62' + value.substring(1);
        }
        e.target.value = value;
    });
    document.getElementById('nik').addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 16);
    });

    window.addEventListener('beforeunload', stopAllStreams);
});
</script>
@endpush