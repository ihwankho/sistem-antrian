@extends('layouts.app')

@section('content')
<style>.card{border:none;border-radius:.75rem}.btn-primary{background-color:#6366f1;border-color:#6366f1}</style>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4">
                <h2 class="fw-bold text-center mb-4">Tambah Layanan Baru</h2>
                <form action="{{ route('pelayanan.store') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama_layanan') is-invalid @enderror" id="nama_layanan" name="nama_layanan" value="{{ old('nama_layanan') }}" placeholder="Nama Layanan" required>
                        <label for="nama_layanan">Nama Layanan</label>
                        @error('nama_layanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating mb-3">
                        <select class="form-select @error('id_departemen') is-invalid @enderror" id="id_departemen" name="id_departemen" required>
                            <option value="" disabled selected>Pilih Departemen</option>
                            @foreach ($departemens as $departemen)
                                <option value="{{ $departemen['id'] }}" {{ old('id_departemen') == $departemen['id'] ? 'selected' : '' }}>
                                    {{ $departemen['nama_departemen'] }}
                                </option>
                            @endforeach
                        </select>
                        <label for="id_departemen">Departemen</label>
                        @error('id_departemen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                        <a href="{{ route('pelayanan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection