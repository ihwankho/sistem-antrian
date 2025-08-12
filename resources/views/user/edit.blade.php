@extends('layouts.app')

@section('content')
<style>
    .main-content { padding: 2rem; }
    .card { border: none; border-radius: .75rem; }
    .btn-primary { background-color: #6366f1; border-color: #6366f1; }
    .photo-preview { 
        width: 120px; 
        height: 120px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 3px solid #e9ecef;
        margin: 0 auto 15px;
        display: block;
    }
    .photo-upload-container {
        text-align: center;
        margin-bottom: 20px;
    }
    .photo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: #6c757d;
        font-size: 2rem;
    }
    .btn-photo {
        background-color: #6366f1;
        border-color: #6366f1;
        color: white;
        border-radius: 20px;
        padding: 5px 15px;
        font-size: 0.875rem;
        margin: 0 5px;
    }
    .btn-photo:hover {
        background-color: #5855eb;
        border-color: #5855eb;
        color: white;
    }
    .btn-photo.btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .btn-photo.btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
</style>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4">
                <h2 class="fw-bold text-center mb-4">Edit Pengguna</h2>
                
                <form action="{{ route('pengguna.update', $user['id']) }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    
                    <!-- Photo Upload Section - Only show for Petugas role -->
                    <div class="photo-upload-container" id="photo-field" 
                         style="display: {{ old('role', $user['role']) == 2 ? 'block' : 'none' }};">
                        @if(isset($user['foto']) && $user['foto'])
                            <img id="photoPreview" src="{{ $user['foto'] }}" class="photo-preview" />
                            <div class="photo-placeholder" id="photoPlaceholder" style="display: none;">
                                <i class="material-icons">person</i>
                            </div>
                        @else
                            <div class="photo-placeholder" id="photoPlaceholder">
                                <i class="material-icons">person</i>
                            </div>
                            <img id="photoPreview" class="photo-preview" style="display: none;" />
                        @endif
                        
                        <input type="file" id="photoInput" name="foto" accept="image/*" style="display: none;">
                        
                        <div>
                            <button type="button" class="btn btn-photo" onclick="document.getElementById('photoInput').click()">
                                <i class="material-icons me-1" style="font-size: 16px;">camera_alt</i>
                                @if(isset($user['foto']) && $user['foto']) Ubah Foto @else Pilih Foto @endif
                            </button>
                            
                            @if(isset($user['foto']) && $user['foto'])
                                <button type="button" class="btn btn-photo btn-danger" onclick="removePhoto()">
                                    <i class="material-icons me-1" style="font-size: 16px;">delete</i>
                                    Hapus
                                </button>
                            @endif
                        </div>
                        
                        <div class="text-muted small mt-2">Format: JPG, JPEG, PNG. Maksimal 2MB</div>
                        @error('foto')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               name="nama" value="{{ old('nama', $user['nama']) }}" placeholder="Nama" required>
                        <label>Nama Lengkap</label>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama_pengguna') is-invalid @enderror" 
                               name="nama_pengguna" value="{{ old('nama_pengguna', $user['nama_pengguna']) }}" placeholder="Username" required>
                        <label>Username</label>
                        @error('nama_pengguna')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="1" @if(old('role', $user['role'])==1) selected @endif>Admin</option>
                            <option value="2" @if(old('role', $user['role'])==2) selected @endif>Petugas</option>
                        </select>
                        <label>Role</label>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3" id="loket-field" style="display:none;">
                        <select class="form-select @error('id_loket') is-invalid @enderror" name="id_loket">
                            <option>Pilih Loket</option>
                            @foreach ($lokets as $loket)
                                <option value="{{ $loket['id'] }}" @if(old('id_loket', $user['id_loket'])==$loket['id']) selected @endif>
                                    {{ $loket['nama_loket'] }}
                                </option>
                            @endforeach
                        </select>
                        <label>Loket</label>
                        @error('id_loket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <p class="text-center small text-muted">Kosongkan password jika tidak ingin diubah.</p>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" placeholder="Password Baru">
                        <label>Password Baru</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                               name="password_confirmation" placeholder="Konfirmasi">
                        <label>Konfirmasi Password</label>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                        <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    const roleSelect = document.getElementById('role');
    const loketField = document.getElementById('loket-field');
    const photoField = document.getElementById('photo-field');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');

    // Toggle loket and photo field based on role selection
    const toggleFields = () => { 
        const isPetugas = roleSelect.value === '2';
        loketField.style.display = isPetugas ? 'block' : 'none';
        photoField.style.display = isPetugas ? 'block' : 'none';
        
        // Reset photo if role is not petugas
        if (!isPetugas) {
            photoInput.value = '';
            photoPreview.style.display = 'none';
            photoPlaceholder.style.display = 'flex';
        }
    };
    
    toggleFields();
    roleSelect.addEventListener('change', toggleFields);

    // Handle photo preview
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

    // Remove photo function
    function removePhoto() {
        photoInput.value = '';
        photoPreview.style.display = 'none';
        photoPlaceholder.style.display = 'flex';
        photoPreview.src = '';
    }
</script>
@endpush