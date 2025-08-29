@extends('layouts.app')

@section('content')
<style>
    .main-content { padding: 2rem; background-color: #f8f9fa; }
    .main-card { border: none; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.05); background-color: white; }
    .btn-action { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; color: white !important; }
</style>

<main class="container-fluid">
    <div class="card main-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manajemen Loket</h2>
            <a href="{{ route('loket.create') }}" class="btn btn-primary" style="background-color: #6366f1; border:none;"><i class="material-icons me-2">add</i><span>Tambah Loket</span></a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>#</th><th>Nama Loket</th><th class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($lokets as $index => $loket)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        {{-- Data dari API berupa array, jadi kita gunakan sintaks array $loket['nama_loket'] --}}
                        <td>{{ $loket['nama_loket'] }}</td>
                        <td class="text-center">
                            <a href="{{ route('loket.edit', $loket['id']) }}" class="btn btn-warning btn-action me-1"><i class="material-icons">edit</i></a>
                            <form action="{{ route('loket.destroy', $loket['id']) }}" method="POST" style="display: inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-action" onclick="return confirm('Anda yakin?')"><i class="material-icons">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center p-5">Belum ada data loket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection