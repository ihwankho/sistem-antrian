@extends('layouts.app')

{{-- Menambahkan CSS baru --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/display/display-settings.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Pengaturan Tampilan Display</h2>
        {{-- Style inline dihapus, diganti class .btn-add-new --}}
        <a href="{{ route('display-settings.create') }}" class="btn btn-primary btn-add-new">
            <i class="material-icons">add</i> Tambah Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                {{-- Menambah class "table-settings" untuk styling badge --}}
                <table class="table table-hover align-middle table-settings">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Status</th>
                            <th scope="col">Video URLs</th>
                            <th scope="col">Running Text</th>
                            {{-- Style inline dihapus, diganti class .table-col-action --}}
                            <th scope="col" class="table-col-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settings as $setting)
                            <tr>
                                <td>
                                    @if ($setting->status == 'active')
                                        {{-- Class badge asli Bootstrap, CSS baru mengatur padding --}}
                                        <span class="badge bg-success rounded-pill">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Style inline dihapus, diganti class .table-video-list --}}
                                    <ul class="table-video-list">
                                        @foreach ($setting->video_urls as $url)
                                            <li>
                                                {{-- Style inline dihapus --}}
                                                <i class="material-icons text-danger">smart_display</i>
                                                {{ Str::limit($url, 50) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    {{-- Style inline dihapus, diganti class .table-running-text --}}
                                    <span class="table-running-text">{{ Str::limit($setting->running_text, 100) }}</span>
                                </td>
                                <td>
                                    {{-- Style inline dihapus, diganti class .btn-action-icon --}}
                                    <a href="{{ route('display-settings.edit', $setting) }}" class="btn btn-sm btn-outline-primary btn-action-icon" title="Edit">
                                        <i class="material-icons">edit</i>
                                    </a>
                                    {{-- Style inline dihapus, diganti class .form-delete-inline --}}
                                    <form action="{{ route('display-settings.destroy', $setting) }}" method="POST" class="form-delete-inline" onsubmit="return confirm('Yakin ingin menghapus pengaturan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action-icon" title="Hapus" {{ $setting->status == 'active' ? 'disabled' : '' }}>
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada pengaturan yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection