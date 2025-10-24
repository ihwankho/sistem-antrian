<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Panduan;
use App\Models\User;
use App\Models\Loket;
use App\Models\Pelayanan; // Ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    /**
     * Menampilkan landing page dengan data yang diambil secara efisien.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Langkah 1: Ambil semua departemen.
            $departemens = Departemen::latest()->get();

            // Langkah 2: Ambil semua pelayanan dan kelompokkan berdasarkan id_departemen.
            $allPelayanans = Pelayanan::all()->groupBy('id_departemen');

            // Langkah 3: Sambungkan pelayanan ke setiap departemen.
            $departemens->each(function ($departemen) use ($allPelayanans) {
                $departemen->pelayanans = $allPelayanans->get($departemen->id, collect());
            });

            // Mengambil petugas (Role 2) dan loket terkait.
            $petugas = User::with('loket:id,nama_loket') // Eager load relasi loket
                ->where('role', 2) // Hanya ambil petugas
                ->orderBy('nama', 'asc')
                ->select('id', 'nama', 'nama_pengguna', 'role', 'id_loket', 'foto') // Pilih kolom yang perlu saja
                ->get()
                ->map(function ($user) {
                    // Transformasi data untuk view
                    return [
                        'nama' => $user->nama,
                        'nama_loket' => $user->loket->nama_loket ?? 'Tidak Ada Loket', // Cek null safety
                        
                        // [PERBAIKAN DI SINI]
                        // Langsung gunakan asset() karena $user->foto sudah berisi path 'images/user_foto/...'
                        'foto' => $user->foto ? asset($user->foto) : null, 
                    ];
                });

            // Mengambil data panduan.
            $panduans = Panduan::orderBy('created_at', 'asc')->get();

            // Mengambil nama loket untuk keperluan tampilan atau filter.
            $lokets = Loket::orderBy('id', 'ASC')->pluck('nama_loket');

        } catch (\Exception $e) {
            // Jika error, log dan siapkan data kosong
            Log::error('Error in LandingPageController@index: ' . $e->getMessage());
            $departemens = collect();
            $petugas = collect();
            $panduans = collect();
            $lokets = collect();
            // Opsional: Kirim pesan error ke view jika perlu
            // session()->flash('error', 'Gagal memuat data halaman.');
        }

        // Kirim data ke view 'home' (pastikan nama view benar)
        return view('home', compact('departemens', 'petugas', 'panduans', 'lokets'));
    }
}