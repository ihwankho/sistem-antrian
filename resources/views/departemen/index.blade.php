@extends('layouts.app')

@section('content')
<style>
    .main-content { padding: 2rem; background-color: #f8f9fa; }
    .main-card { border: none; border-radius: 12px; }
    .btn-action { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; color: white !important; }
</style>

<main class="container-fluid">
    <div class="card main-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Daftar Departemen</h2>
            <a href="{{ route('departemen.create') }}" class="btn btn-primary" style="background-color: #6366f1; border:none;"><i class="material-icons me-2">add</i><span>Tambah</span></a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Departemen</th>
                        <th>Loket</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departemens as $departemen)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $departemen['nama_departemen'] }}</td>
                        <td>
                            {{-- Cek jika data sudah mengandung nama_loket --}}
                            @if(isset($departemen['nama_loket']))
                                {{ $departemen['nama_loket'] }}
                            {{-- Jika tidak, gunakan mapping manual --}}
                            @elseif(!empty($departemen['id_loket']))
                                @php
                                    // Siapkan data loket agar mudah dicari berdasarkan ID
                                    $loketsById = [];
                                    foreach ($lokets as $loket) {
                                        $loketsById[$loket['id']] = $loket['nama_loket'];
                                    }
                                @endphp
                                {{ $loketsById[$departemen['id_loket']] ?? 'N/A' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                             <a href="{{ route('departemen.edit', $departemen['id']) }}" class="btn btn-warning btn-action me-1" title="Edit"><i class="material-icons">edit</i></a>
                            <form action="{{ route('departemen.destroy', $departemen['id']) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-action" title="Hapus" onclick="return confirm('Anda yakin?')"><i class="material-icons">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center p-5">Belum ada data departemen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection