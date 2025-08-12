@extends('layouts.app')

@section('content')
<style>
    .main-content { padding-top: 2rem; padding-bottom: 2rem; background-color: #f8f9fa; }
    .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .btn-primary { background-color: #6366f1; border-color: #6366f1; }
</style>

<main class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="mb-1 fw-bold">Tambah Departemen Baru</h2>
                </div>
                <form action="{{ route('departemen.store') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama_departemen') is-invalid @enderror" id="nama_departemen" name="nama_departemen" value="{{ old('nama_departemen') }}" placeholder="Nama Departemen" required>
                        <label for="nama_departemen">Nama Departemen</label>
                        @error('nama_departemen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DROPDOWN LOKET BARU --}}
                    <div class="form-floating mb-3">
                        <select class="form-select @error('id_loket') is-invalid @enderror" id="id_loket" name="id_loket" required>
                            <option value="" disabled selected>Pilih Loket</option>
                            @foreach ($lokets as $loket)
                                <option value="{{ $loket['id'] }}" {{ old('id_loket') == $loket['id'] ? 'selected' : '' }}>
                                    {{ $loket['nama_loket'] }}
                                </option>
                            @endforeach
                        </select>
                        <label for="id_loket">Loket</label>
                        @error('id_loket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                        <a href="{{ route('departemen.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection