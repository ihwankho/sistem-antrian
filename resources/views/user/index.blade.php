@extends('layouts.app')

@section('content')
<style>
    .main-content { padding: 2rem; } 
    .main-card { border: none; border-radius: 12px; }
    .btn-action { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }
    .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f8f9fa;
        border: 2px solid #e9ecef;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1.2rem;
    }
</style>

@php
    // Siapkan data loket agar mudah dicari berdasarkan ID
    $loketsById = [];
    foreach ($lokets as $loket) {
        $loketsById[$loket['id']] = $loket['nama_loket'];
    }
@endphp

<div class="container-fluid">
    <div class="card main-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manajemen Pengguna</h2>
            <a href="{{ route('pengguna.create') }}" class="btn btn-primary" style="background-color: #6366f1; border:none;">
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
                        <th>Username</th>
                        <th>Role</th>
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
                                    <i class="material-icons" style="font-size: 20px;">person</i>
                                </div>
                            @endif
                        </td>
                        <td>{{ $user['nama'] }}</td>
                        <td>{{ $user['nama_pengguna'] }}</td>
                        <td>
                            @if($user['role'] == 1) 
                                <span class="badge text-bg-primary">Admin</span>
                            @else 
                                <span class="badge text-bg-success">Petugas</span> 
                            @endif
                        </td>
                        <td>
                            @if($user['role'] == 2 && !empty($user['id_loket']))
                                {{-- Cari nama loket dari array $loketsById --}}
                                {{ $loketsById[$user['id_loket']] ?? 'Loket Dihapus' }}
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