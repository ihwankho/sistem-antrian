<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Pelayanan;
use App\Models\Panduan;
use App\Models\User;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    /**
     * Display the landing page with departments, services, staff information, and guidelines
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Initialize default values
        $departemens = collect();
        $petugas = collect();
        $panduans = collect();
        $lokets = collect();

        try {
            // Ambil semua departemen dengan urutan terbaru
            $departemensQuery = Departemen::latest()->get();
            if ($departemensQuery) {
                $departemens = $departemensQuery;
            }
            
            // Ambil semua pelayanan dan kelompokkan berdasarkan id_departemen
            $pelayanansByDepartemen = Pelayanan::all()->groupBy('id_departemen');

            // Assign pelayanan ke setiap departemen
            if ($departemens->isNotEmpty()) {
                foreach ($departemens as $departemen) {
                    $departemen->pelayanans = $pelayanansByDepartemen->get($departemen->id, collect());
                }
            }
            
            // Ambil data petugas dengan role 2 (petugas pelayanan) beserta relasi loket
            try {
                $petugasQuery = User::with('loket')
                              ->where('role', 2)
                              ->orderBy('nama', 'asc')
                              ->get();
                
                if ($petugasQuery) {
                    $petugas = $petugasQuery->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'nama' => $user->nama,
                            'nama_pengguna' => $user->nama_pengguna,
                            'role' => $user->role,
                            'nama_loket' => $user->loket ? $user->loket->nama_loket : 'Tidak Ada Loket',
                            'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching petugas: ' . $e->getMessage());
                $petugas = collect();
            }
            
            // Ambil data panduan pengambilan tiket
            try {
                $panduansQuery = Panduan::orderBy('created_at', 'asc')->get();
                if ($panduansQuery) {
                    $panduans = $panduansQuery;
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching panduans: ' . $e->getMessage());
                $panduans = collect();
            }
            
            // Ambil semua loket untuk inisialisasi card antrian
            try {
                $loketsQuery = Loket::orderBy('id', 'ASC')->pluck('nama_loket');
                if ($loketsQuery) {
                    $lokets = $loketsQuery;
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching lokets: ' . $e->getMessage());
                $lokets = collect();
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in LandingPageController@index: ' . $e->getMessage());
        }
        
        return view('home', compact('departemens', 'petugas', 'panduans', 'lokets'));
    }
}