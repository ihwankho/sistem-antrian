@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/departemen.css') }}">
@endpush

@section('content')
<div class="card main-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Daftar Departemen</h2>
        <a href="{{ route('departemen.create') }}" class="btn btn-primary"><i class="material-icons me-2">add</i><span>Tambah</span></a>
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
                        @if(isset($departemen['nama_loket']))
                            {{ $departemen['nama_loket'] }}
                        @elseif(!empty($departemen['id_loket']))
                            @php
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
@endsection