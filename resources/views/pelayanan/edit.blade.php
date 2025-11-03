@extends('layouts.app')

{{-- Menambahkan CSS baru --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelayanan/pelayanan.css') }}">
@endpush

@section('content')
{{-- Tag <main> dihapus --}}
<div class="row justify-content-center">
    <div class="col-md-7">
        {{-- Menambahkan class .form-card dari CSS baru --}}
        <div class="card form-card p-4">
            <h2 class="fw-bold text-center mb-4">Edit Layanan</h2>
            <form action="{{ route('pelayanan.update', $layanan['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('nama_layanan') is-invalid @enderror" name="nama_layanan" value="{{ old('nama_layanan', $layanan['nama_layanan']) }}" placeholder="Nama Layanan" required>
                    <label for="nama_layanan">Nama Layanan</label>
                    @error('nama_layanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-floating mb-3">
                    <select class="form-select @error('id_departemen') is-invalid @enderror" name="id_departemen" required>
                        <option value="" disabled>Pilih Departemen</option>
                        @foreach ($departemens as $departemen)
                            <option value="{{ $departemen['id'] }}" {{ old('id_departemen', $layanan['id_departemen']) == $departemen['id'] ? 'selected' : '' }}>
                                {{ $departemen['nama_departemen'] }}
                            </option>
                        @endforeach
                    </select>
                    <label for="id_departemen">Departemen</label>
                    @error('id_departemen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                    <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection