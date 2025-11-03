@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengguna/pengguna.css') }}">
@endpush

@section('content')
{{-- Tag <main> dihapus agar tidak duplikat dengan layout utama --}}
<div class="row justify-content-center">
    <div class="col-md-7">
        {{-- Class .card diganti .form-card --}}
        <div class="card form-card p-4">
            <h2 class="fw-bold text-center mb-4">Tambah Pengguna Baru</h2>
            
            <form action="{{ route('pengguna.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="photo-upload-container" id="photo-field" style="display:none;">
                    <div class="photo-placeholder" id="photoPlaceholder">
                        <i class="material-icons">person</i>
                    </div>
                    <img id="photoPreview" class="photo-preview" style="display: none;" />
                    <input type="file" id="photoInput" name="foto" accept="image/*" style="display: none;">
                    <button type="button" class="btn btn-photo" onclick="document.getElementById('photoInput').click()">
                        {{-- Style inline dihapus --}}
                        <i class="material-icons me-1">camera_alt</i>
                        Pilih Foto
                    </button>
                    <div class="text-muted small mt-2">Format: JPG, JPEG, PNG. Maksimal 2MB</div>
                    @error('foto')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                           name="nama" value="{{ old('nama') }}" placeholder="Nama" required>
                    <label>Nama Lengkap</label>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('nama_pengguna') is-invalid @enderror" 
                           name="nama_pengguna" value="{{ old('nama_pengguna') }}" placeholder="Email" required>
                    <label>Email</label>
                    @error('nama_pengguna')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option disabled selected>Pilih Role</option>
                        <option value="1" @if(old('role')==1) selected @endif>Admin</option>
                        <option value="2" @if(old('role')==2) selected @endif>Petugas</option>
                    </select>
                    <label>Role</label>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3" id="loket-field" style="display:none;">
                    <select class="form-select @error('id_loket') is-invalid @enderror" name="id_loket">
                        <option disabled selected>Pilih Loket</option>
                        @foreach ($lokets as $loket)
                            <option value="{{ $loket['id'] }}" @if(old('id_loket')==$loket['id']) selected @endif>
                                {{ $loket['nama_loket'] }}
                            </option>
                        @endforeach
                    </select>
                    <label>Loket</label>
                    @error('id_loket')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3 password-wrapper">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="Password" required>
                    <label>Password</label>
                    <button type="button" class="toggle-password-btn" data-target="password">
                        {{-- Style inline dihapus --}}
                        <i class="material-icons">visibility</i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                           id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi" required>
                    <label>Konfirmasi Password</label>
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                    <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript tidak diubah --}}
<script>
    const roleSelect = document.getElementById('role');
    const loketField = document.getElementById('loket-field');
    const photoField = document.getElementById('photo-field');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');

    const toggleFields = () => { 
        const isPetugas = roleSelect.value === '2';
        loketField.style.display = isPetugas ? 'block' : 'none';
        photoField.style.display = isPetugas ? 'block' : 'none';
        
        if (!isPetugas) {
            photoInput.value = '';
            photoPreview.style.display = 'none';
            photoPlaceholder.style.display = 'flex';
        }
    };
    
    toggleFields();
    roleSelect.addEventListener('change', toggleFields);

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
        } else {
            photoPreview.style.display = 'none';
            photoPlaceholder.style.display = 'flex';
        }
    });

    const togglePasswordButtons = document.querySelectorAll('.toggle-password-btn');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetInputId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetInputId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                targetInput.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    });
</script>
@endpush