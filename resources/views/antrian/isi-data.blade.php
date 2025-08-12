@extends('layouts.landing')

@section('content')
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <!-- Alert/Warning Header -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                            <strong>Terjadi kesalahan!</strong>
                        </div>
                        <ul class="mb-0 ps-4">
                            @foreach ($errors->all() as $error)
                                <li class="mb-1">{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                <!-- Info Card -->
                <div class="card border-0 bg-primary bg-opacity-10 mb-4">
                    <div class="card-body text-center p-4">
                        <h5 class="fw-bold text-primary mb-2">
                            <i class="bi bi-info-circle me-2"></i>
                            Formulir Pengunjung - {{ $layanan['nama_layanan'] }}
                        </h5>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <p class="mb-2 text-muted small">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    Pastikan pencahayaan cukup terang
                                </p>
                                <p class="mb-2 text-muted small">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    Foto wajah harus jelas dan tidak blur
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2 text-muted small">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    KTP harus terlihat dengan jelas
                                </p>
                                <p class="mb-0 text-muted small">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    Data akan diverifikasi secara otomatis
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form Card -->
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('antrian.buat-tiket') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="id_pelayanan" value="{{ $layanan['id'] }}">
                            <input type="file" name="foto_ktp" id="foto_ktp_file" style="display: none;" required>
                            <input type="file" name="foto_wajah" id="foto_wajah_file" style="display: none;" required>

                            <!-- Personal Information Section -->
                            <div class="mb-5">
                                <h4 class="fw-bold text-primary mb-4">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Informasi Personal
                                </h4>
                                <div class="row g-4">
                                    {{-- Nama Pengunjung --}}
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control form-control-lg border-2" 
                                                   id="nama_pengunjung" 
                                                   name="nama_pengunjung" 
                                                   placeholder="Nama Lengkap" 
                                                   value="{{ old('nama_pengunjung') }}" 
                                                   required>
                                            <label for="nama_pengunjung">
                                                <i class="bi bi-person me-1"></i>
                                                Nama Lengkap
                                            </label>
                                            <div class="invalid-feedback">
                                                Mohon masukkan nama lengkap Anda.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- NIK & No HP --}}
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control border-2" 
                                                   id="nik" 
                                                   name="nik" 
                                                   placeholder="NIK" 
                                                   value="{{ old('nik') }}" 
                                                   required>
                                            <label for="nik">
                                                <i class="bi bi-card-text me-1"></i>
                                                NIK
                                            </label>
                                            <div class="invalid-feedback">
                                                Mohon masukkan NIK yang valid (16 digit).
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" 
                                                   class="form-control border-2" 
                                                   id="no_hp" 
                                                   name="no_hp" 
                                                   placeholder="No HP" 
                                                   value="{{ old('no_hp') }}">
                                            <label for="no_hp">
                                                <i class="bi bi-phone me-1"></i>
                                                No. Handphone
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Jenis Kelamin --}}
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select border-2" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">Pilih Jenis Kelamin</option>
                                                <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki</option>
                                                <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan</option>
                                            </select>
                                            <label for="jenis_kelamin">
                                                <i class="bi bi-gender-ambiguous me-1"></i>
                                                Jenis Kelamin
                                            </label>
                                            <div class="invalid-feedback">
                                                Mohon pilih jenis kelamin.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Alamat --}}
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control border-2" 
                                                      placeholder="Alamat" 
                                                      id="alamat" 
                                                      name="alamat" 
                                                      style="height: 120px" 
                                                      required>{{ old('alamat') }}</textarea>
                                            <label for="alamat">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                Alamat Lengkap
                                            </label>
                                            <div class="invalid-feedback">
                                                Mohon masukkan alamat lengkap.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Capture Section -->
                            <div class="mb-5">
                                <h4 class="fw-bold text-primary mb-4">
                                    <i class="bi bi-camera me-2"></i>
                                    Ambil Foto dengan Kamera
                                </h4>
                                <div class="row g-4">
                                    {{-- Foto KTP --}}
                                    <div class="col-md-6">
                                        <div class="card border-2 border-dashed h-100">
                                            <div class="card-body text-center p-4">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="bi bi-card-image fs-1 text-primary d-block mb-2"></i>
                                                    Foto KTP
                                                </h6>
                                                
                                                <!-- Camera View for KTP -->
                                                <div id="ktp-camera-container" class="mb-3">
                                                    <video id="ktp-camera" width="100%" height="200" style="display: none; border-radius: 8px;" autoplay></video>
                                                    <canvas id="ktp-canvas" style="display: none;"></canvas>
                                                </div>
                                                
                                                <!-- Preview Image for KTP -->
                                                <div id="ktp-preview-container" class="mb-3">
                                                    <img id="ktp-preview" src="#" alt="Preview KTP" class="img-fluid rounded shadow-sm d-none" style="max-height: 200px; max-width: 100%;">
                                                </div>
                                                
                                                <!-- Control Buttons for KTP -->
                                                <div class="d-grid gap-2">
                                                    <button type="button" id="start-ktp-camera" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-camera me-1"></i> Buka Kamera
                                                    </button>
                                                    <button type="button" id="capture-ktp" class="btn btn-success btn-sm" style="display: none;">
                                                        <i class="bi bi-camera-fill me-1"></i> Ambil Foto
                                                    </button>
                                                    <button type="button" id="retake-ktp" class="btn btn-warning btn-sm" style="display: none;">
                                                        <i class="bi bi-arrow-clockwise me-1"></i> Foto Ulang
                                                    </button>
                                                </div>
                                                
                                                <small class="text-muted d-block mt-2">Pastikan KTP terlihat jelas dan tidak blur</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Foto Wajah --}}
                                    <div class="col-md-6">
                                        <div class="card border-2 border-dashed h-100">
                                            <div class="card-body text-center p-4">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="bi bi-person-square fs-1 text-success d-block mb-2"></i>
                                                    Foto Wajah
                                                </h6>
                                                
                                                <!-- Camera View for Face -->
                                                <div id="face-camera-container" class="mb-3">
                                                    <video id="face-camera" width="100%" height="200" style="display: none; border-radius: 8px;" autoplay></video>
                                                    <canvas id="face-canvas" style="display: none;"></canvas>
                                                </div>
                                                
                                                <!-- Preview Image for Face -->
                                                <div id="face-preview-container" class="mb-3">
                                                    <img id="face-preview" src="#" alt="Preview Wajah" class="img-fluid rounded-circle shadow-sm d-none" style="max-height: 200px; max-width: 100%;">
                                                </div>
                                                
                                                <!-- Control Buttons for Face -->
                                                <div class="d-grid gap-2">
                                                    <button type="button" id="start-face-camera" class="btn btn-success btn-sm">
                                                        <i class="bi bi-camera me-1"></i> Buka Kamera
                                                    </button>
                                                    <button type="button" id="capture-face" class="btn btn-success btn-sm" style="display: none;">
                                                        <i class="bi bi-camera-fill me-1"></i> Ambil Foto
                                                    </button>
                                                    <button type="button" id="retake-face" class="btn btn-warning btn-sm" style="display: none;">
                                                        <i class="bi bi-arrow-clockwise me-1"></i> Foto Ulang
                                                    </button>
                                                </div>
                                                
                                                <small class="text-muted d-block mt-2">Pastikan wajah terlihat jelas dengan pencahayaan yang baik</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="row g-3 mt-4">
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm" id="submit-btn" disabled>
                                        <i class="bi bi-ticket-detailed-fill me-2"></i> 
                                        Dapatkan Tiket Antrian
                                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('antrian.pilih-layanan') }}" class="btn btn-outline-secondary btn-lg w-100 py-3">
                                        <i class="bi bi-arrow-left-circle me-1"></i> 
                                        Kembali
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    let ktpStream = null;
    let faceStream = null;
    let ktpCaptured = false;
    let faceCaptured = false;

    // Convert base64 to File object
    function base64ToFile(base64String, filename) {
        const arr = base64String.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename, { type: mime });
    }

    // Image compression function
    function compressImage(canvas, quality = 0.8) {
        return new Promise((resolve) => {
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });
    }

    // Create file input from compressed image
    async function createFileInput(canvas, inputId, filename) {
        const compressedBlob = await compressImage(canvas, 0.8);
        const file = new File([compressedBlob], filename, { type: 'image/jpeg' });
        
        // Create a new FileList
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        // Set the file to the input
        document.getElementById(inputId).files = dataTransfer.files;
    }

    // KTP Camera Functions
    document.getElementById('start-ktp-camera').addEventListener('click', async function() {
        try {
            ktpStream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'environment' // Use back camera if available
                } 
            });
            
            const video = document.getElementById('ktp-camera');
            video.srcObject = ktpStream;
            video.style.display = 'block';
            
            this.style.display = 'none';
            document.getElementById('capture-ktp').style.display = 'block';
        } catch (err) {
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.');
        }
    });

    document.getElementById('capture-ktp').addEventListener('click', async function() {
        const video = document.getElementById('ktp-camera');
        const canvas = document.getElementById('ktp-canvas');
        const preview = document.getElementById('ktp-preview');
        
        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Create compressed file and set to input
        await createFileInput(canvas, 'foto_ktp_file', 'ktp_photo.jpg');
        
        // Show preview
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        preview.src = dataURL;
        preview.classList.remove('d-none');
        
        // Hide camera, show retake button
        video.style.display = 'none';
        this.style.display = 'none';
        document.getElementById('retake-ktp').style.display = 'block';
        
        // Stop camera stream
        if (ktpStream) {
            ktpStream.getTracks().forEach(track => track.stop());
        }
        
        ktpCaptured = true;
        checkFormCompletion();
    });

    document.getElementById('retake-ktp').addEventListener('click', function() {
        const preview = document.getElementById('ktp-preview');
        preview.classList.add('d-none');
        
        // Clear the file input
        document.getElementById('foto_ktp_file').value = '';
        
        this.style.display = 'none';
        document.getElementById('start-ktp-camera').style.display = 'block';
        
        ktpCaptured = false;
        checkFormCompletion();
    });/*  */

    // Face Camera Functions
    document.getElementById('start-face-camera').addEventListener('click', async function() {
        try {
            faceStream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user' // Use front camera for selfie
                } 
            });
            
            const video = document.getElementById('face-camera');
            video.srcObject = faceStream;
            video.style.display = 'block';
            
            this.style.display = 'none';
            document.getElementById('capture-face').style.display = 'block';
        } catch (err) {
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.');
        }
    });

    document.getElementById('capture-face').addEventListener('click', async function() {
        const video = document.getElementById('face-camera');
        const canvas = document.getElementById('face-canvas');
        const preview = document.getElementById('face-preview');
        
        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Create compressed file and set to input
        await createFileInput(canvas, 'foto_wajah_file', 'face_photo.jpg');
        
        // Show preview
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        preview.src = dataURL;
        preview.classList.remove('d-none');
        
        // Hide camera, show retake button
        video.style.display = 'none';
        this.style.display = 'none';
        document.getElementById('retake-face').style.display = 'block';
        
        // Stop camera stream
        if (faceStream) {
            faceStream.getTracks().forEach(track => track.stop());
        }
        
        faceCaptured = true;
        checkFormCompletion();
    });

    document.getElementById('retake-face').addEventListener('click', function() {
        const preview = document.getElementById('face-preview');
        preview.classList.add('d-none');
        
        // Clear the file input
        document.getElementById('foto_wajah_file').value = '';
        
        this.style.display = 'none';
        document.getElementById('start-face-camera').style.display = 'block';
        
        faceCaptured = false;
        checkFormCompletion();
    });

    // Check if form is complete
    function checkFormCompletion() {
        const submitBtn = document.getElementById('submit-btn');
        if (ktpCaptured && faceCaptured) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }
    }

    // Form Validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false || !ktpCaptured || !faceCaptured) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        if (!ktpCaptured || !faceCaptured) {
                            alert('Mohon ambil foto KTP dan foto wajah terlebih dahulu.');
                        }
                    } else {
                        // Show loading spinner
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const spinner = submitBtn.querySelector('.spinner-border');
                        spinner.classList.remove('d-none');
                        submitBtn.disabled = true;
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Phone number formatting
    document.getElementById('no_hp').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('0')) {
            value = '62' + value.substring(1);
        }
        e.target.value = value;
    });

    // NIK validation (16 digits)
    document.getElementById('nik').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 16) {
            value = value.substring(0, 16);
        }
        e.target.value = value;
    });

    // Clean up camera streams when page unloads
    window.addEventListener('beforeunload', function() {
        if (ktpStream) {
            ktpStream.getTracks().forEach(track => track.stop());
        }
        if (faceStream) {
            faceStream.getTracks().forEach(track => track.stop());
        }
    });
</script>

<style>
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        opacity: .65;
        transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
    }

    .border-dashed {
        border-style: dashed !important;
        border-color: var(--bs-border-color-translucent) !important;
        transition: all 0.3s ease;
    }

    .border-dashed:hover {
        border-color: var(--bs-primary) !important;
        background-color: var(--bs-primary-bg-subtle) !important;
    }

    .card {
        transition: all 0.3s ease;
    }

    .btn {
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
    }

    #ktp-camera, #face-camera {
        border: 2px solid #dee2e6;
        background: #f8f9fa;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endsection