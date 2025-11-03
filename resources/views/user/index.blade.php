@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengguna/pengguna.css') }}">
@endpush

@section('content')
{{-- Tag <main> dihapus --}}
<div class="container-fluid">
    <div class="card main-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manajemen Pengguna</h2>
            {{-- Style inline dihapus --}}
            <a href="{{ route('pengguna.create') }}" class="btn btn-primary">
                <i class="material-icons me-2">add</i><span>Tambah</span>
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th> <th>Role</th>
                        <th>Loket</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if(isset($user['foto']) && $user['foto'])
                                <img src="{{ $user['foto'] }}" alt="{{ $user['nama'] }}" class="user-avatar">
                            @else
                                <div class="user-avatar-placeholder">
                                    {{-- Style inline dihapus --}}
                                    <i class="material-icons">person</i>
                                </div>
                            @endif
                        </td>
                        <td>{{ $user['nama'] }}</td>
                        <td>{{ $user['nama_pengguna'] }}</td> <td>
                            @if($user['role'] == 1) 
                                <span class="badge text-bg-primary">Admin</span>
                            @else 
                                <span class="badge text-bg-success">Petugas</span> 
                            @endif
                        </td>
                        <td>
                            @if($user['role'] == 2 && !empty($user['nama_loket']))
                                {{ $user['nama_loket'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pengguna.edit', $user['id']) }}" class="btn btn-light btn-action me-1">
                                <i class="material-icons text-warning">edit</i>
                            </a>
                            <form action="{{ route('pengguna.destroy', $user['id']) }}" method="POST" class="d-inline"> 
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-light btn-action" onclick="return confirm('Yakin hapus pengguna ini?')">
                                    <i class="material-icons text-danger">delete</i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center p-5">Belum ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection