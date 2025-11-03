@extends('layouts.app')

{{-- Menambahkan CSS baru --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/loket/loket.css') }}">
@endpush

@section('content')
{{-- Tag <main> dihapus --}}
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        {{-- Menggunakan class .form-card dari CSS baru --}}
        <div class="card form-card p-4 p-md-5">
            <div class="text-center mb-4"><h2 class="mb-1 fw-bold">Edit Loket</h2></div>
            <form action="{{ route('loket.update', $loket['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('nama_loket') is-invalid @enderror" id="nama_loket" name="nama_loket" value="{{ old('nama_loket', $loket['nama_loket']) }}" placeholder="Nama Loket" required>
                    <label for="nama_loket">Nama Loket</label>
                    @error('nama_loket')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                    <a href="{{ route('loket.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection