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
                <div class="text-center mb-4"><h2 class="mb-1 fw-bold">Tambah Loket Baru</h2></div>
                <form action="{{ route('loket.store') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control @error('nama_loket') is-invalid @enderror" id="nama_loket" name="nama_loket" value="{{ old('nama_loket') }}" placeholder="Nama Loket" required>
                        <label for="nama_loket">Nama Loket</label>
                        @error('nama_loket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                        <a href="{{ route('loket.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection