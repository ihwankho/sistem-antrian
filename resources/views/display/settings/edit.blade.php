@extends('layouts.app')

{{-- Menambahkan CSS baru --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/display/display-settings.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        {{-- Style inline dihapus, diganti class .btn-back-icon --}}
        <a href="{{ route('display-settings.index') }}" class="btn btn-outline-primary me-3 btn-back-icon">
            <i class="material-icons">arrow_back</i>
        </a>
        <h2 class="mb-0">Edit Pengaturan Tampilan</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('display-settings.update', $setting) }}" method="POST">
                @csrf
                @method('PUT')
                
                @include('display.settings._form', ['setting' => $setting])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                    <a href="{{ route('display-settings.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection