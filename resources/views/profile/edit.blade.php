@extends('layouts.app')

@push('styles')
    {{-- Memanggil file CSS eksternal yang baru --}}
    <link rel="stylesheet" href="{{ asset('css/profil/profil.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card profile-card p-4">
                <h2 class="fw-bold text-center mb-4">Profil Saya</h2>
                
                {{-- Menampilkan Pesan Sukses (Tidak diubah) --}}
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="material-icons me-2">check_circle</i>
                        <div>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                {{-- Blok Error Validasi Global DIHAPUS dari sini --}}
                
                <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    
                    <div class="photo-upload-container" id="photo-field">
                        @if(isset($user['foto_url']) && $user['foto_url'])
                            <img id="photoPreview" src="{{ $user['foto_url'] }}" class="photo-preview" />
                            <div class="photo-placeholder" id="photoPlaceholder" style="display: none;">
                                <i class="material-icons">person</i>
                            </div>
                        @else
                            <div class="photo-placeholder" id="photoPlaceholder">
                                <i class="material-icons">person</i>
                            </div>
                            <img id="photoPreview" class="photo-preview" style="display: none;" />
                        @endif
                        
                        <input type="file" id="photoInput" name="foto" accept="image/jpeg,image/png,image/jpg" style="display: none;" class="@error('foto') is-invalid @enderror">
                        
                        <div>
                            <button type="button" class="btn btn-photo" onclick="document.getElementById('photoInput').click()">
                                {{-- Style inline dihapus --}}
                                <i class="material-icons me-1">camera_alt</i>
                                @if(isset($user['foto_url']) && $user['foto_url']) Ubah Foto @else Pilih Foto @endif
                            </button>
                        </div>
                        
                        <div class="text-muted small mt-2">Format: JPG, JPEG, PNG. Maksimal 2MB</div>
                        
                        {{-- Error Handler untuk 'foto' --}}
                        @error('foto')
                            <div class="validation-error-message text-center mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               name="nama" id="nama" value="{{ old('nama', $user['nama'] ?? '') }}" placeholder="Nama Lengkap" required>
                        <label for="nama">Nama Lengkap</label>
                        {{-- Error Handler untuk 'nama' --}}
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control @error('nama_pengguna') is-invalid @enderror" 
                               name="nama_pengguna" id="nama_pengguna" value="{{ old('nama_pengguna', $user['nama_pengguna'] ?? '') }}" placeholder="Email" required>
                        <label for="nama_pengguna">Email</label>
                        {{-- Error Handler untuk 'nama_pengguna' --}}
                        @error('nama_pengguna')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <p class="text-center small text-muted">Kosongkan password jika tidak ingin diubah.</p>

                    <div class="form-floating mb-3 password-wrapper">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" id="password" placeholder="Password Baru">
                        <label for="password">Password Baru</label>
                        <button type="button" class="toggle-password-btn" data-target="password">
                            {{-- Style inline dihapus --}}
                            <i class="material-icons">visibility</i>
                        </button>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" 
                               name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Password Baru">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        {{-- Error Handler untuk 'password' (termasuk konfirmasi) --}}
                        @error('password')
                            <div class="validation-error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        {{-- Style inline dihapus, class .btn-primary sudah di-handle di app.css --}}
                        <button type="submit" class="btn btn-primary btn-lg">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript tidak diubah sama sekali --}}
<script>
    // --- JAVASCRIPT UNTUK PREVIEW FOTO ---
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');

    if (photoInput) {
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                    photoPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // --- JAVASCRIPT UNTUK TOMBOL MATA (PASSWORD) ---
    const togglePasswordButtons = document.querySelectorAll('.toggle-password-btn');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetInputId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetInputId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.textContent = 'visibility_off'; // Ganti ikon
            } else {
                targetInput.type = 'password';
                icon.textContent = 'visibility'; // Kembalikan ikon
            }
        });
    });

    // Tampilkan notifikasi toastr jika ada dari redirect
    @if(session('success'))
        window.showNotification('success', "{{ session('success') }}", 'Berhasil');
    @endif
</script>
@endpush