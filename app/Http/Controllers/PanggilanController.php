<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loket;
use App\Models\Antrian;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PanggilanController extends Controller
{
    // Halaman Admin - untuk admin yang dapat memilih loket
    public function admin(Request $request)
    {
        $user = Auth::user();

        // Jika petugas, redirect ke halaman petugas
        if ($user->role === 2) {
            return redirect()->route('panggilan.petugas');
        }

        $today = Carbon::today();

        // Ambil semua loket untuk dropdown
        $lokets = Loket::orderBy('id')->get();

        // Mapping ID loket ke huruf
        $loketIds = $lokets->pluck('id')->toArray();
        $loketMap = [];
        foreach ($loketIds as $index => $id) {
            $loketMap[$id] = chr(65 + $index);
        }

        // Loket yang dipilih (default loket pertama)
        $selectedLoketId = $request->input('loket', $lokets->first()->id);
        $selectedLoket = Loket::find($selectedLoketId);

        // Hitung antrian tersisa
        $antrianTersisa = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $selectedLoketId)
            ->whereDate('antrians.created_at', $today)
            ->where('antrians.status_antrian', 1)
            ->count();

        // Ambil antrian yang sedang dipanggil untuk loket yang dipilih
        $currentCalling = Antrian::with(['pelayanan.departemen.loket', 'pengunjung'])
            ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $selectedLoketId)
            ->whereDate('antrians.created_at', $today)
            ->where('antrians.status_antrian', 2)
            ->select('antrians.*')
            ->first();

        if ($currentCalling) {
            $currentCalling->kode_antrian = ($loketMap[$selectedLoketId] ?? 'X') . str_pad($currentCalling->nomor_antrian, 3, '0', STR_PAD_LEFT);
        }

        // Untuk tabel daftar antrian aktif
        $antrians = Antrian::with(['pelayanan.departemen.loket', 'pengunjung'])
            ->whereDate('created_at', $today)
            ->whereIn('status_antrian', [1,2,4])
            ->when($request->has('search'), function($query) use ($request) {
                $search = $request->search;
                $query->whereHas('pengunjung', function($q) use ($search) {
                    $q->where('nama_pengunjung', 'like', "%$search%")
                      ->orWhere('nik', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->perPage ?? 10)
            ->withQueryString();

        // Format kode antrian
        $antrians->getCollection()->transform(function ($antrian) use ($loketMap) {
            $loketId = $antrian->pelayanan->departemen->loket->id;
            $huruf = $loketMap[$loketId] ?? 'X';
            $antrian->kode_antrian = $huruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
            $antrian->nama_loket = $antrian->pelayanan->departemen->loket->nama_loket;
            return $antrian;
        });

        return view('panggilan.admin', compact(
            'lokets',
            'selectedLoketId',
            'selectedLoket',
            'antrianTersisa',
            'currentCalling',
            'antrians'
        ));
    }

    // Halaman Petugas - hanya untuk loket yang ditugaskan
    public function petugas(Request $request)
    {
        $user = Auth::user();

        // Pastikan user adalah petugas dan memiliki loket yang ditugaskan
        if ($user->role !== 2 || !$user->id_loket) {
            return redirect('/login')->with('error', 'Anda tidak memiliki akses ke halaman ini atau belum ditugaskan ke loket tertentu.');
        }

        $today = Carbon::today();
        $loketId = $user->id_loket;

        // Ambil data loket petugas
        $loket = Loket::findOrFail($loketId);

        // Mapping ID loket ke huruf (untuk konsistensi dengan sistem admin)
        $lokets = Loket::orderBy('id')->get();
        $loketIds = $lokets->pluck('id')->toArray();
        $loketMap = [];
        foreach ($loketIds as $index => $id) {
            $loketMap[$id] = chr(65 + $index);
        }

        // Hitung antrian tersisa untuk loket petugas
        $antrianTersisa = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $loketId)
            ->whereDate('antrians.created_at', $today)
            ->where('antrians.status_antrian', 1)
            ->count();

        // Ambil antrian yang sedang dipanggil untuk loket petugas
        $currentCalling = Antrian::with(['pelayanan.departemen.loket', 'pengunjung'])
            ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $loketId)
            ->whereDate('antrians.created_at', $today)
            ->where('antrians.status_antrian', 2)
            ->select('antrians.*')
            ->first();

        if ($currentCalling) {
            $currentCalling->kode_antrian = ($loketMap[$loketId] ?? 'X') . str_pad($currentCalling->nomor_antrian, 3, '0', STR_PAD_LEFT);
        }

        // Untuk tabel daftar antrian aktif (hanya untuk loket petugas)
        $antrians = Antrian::with(['pelayanan.departemen.loket', 'pengunjung'])
            ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $loketId)
            ->whereDate('antrians.created_at', $today)
            ->whereIn('antrians.status_antrian', [1,2,4])
            ->when($request->has('search'), function($query) use ($request) {
                $search = $request->search;
                $query->whereHas('pengunjung', function($q) use ($search) {
                    $q->where('nama_pengunjung', 'like', "%$search%")
                      ->orWhere('nik', 'like', "%$search%");
                });
            })
            ->select('antrians.*')
            ->orderBy('antrians.created_at', 'desc')
            ->paginate($request->perPage ?? 10)
            ->withQueryString();

        // Format kode antrian
        $antrians->getCollection()->transform(function ($antrian) use ($loketMap) {
            $loketId = $antrian->pelayanan->departemen->loket->id;
            $huruf = $loketMap[$loketId] ?? 'X';
            $antrian->kode_antrian = $huruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
            $antrian->nama_loket = $antrian->pelayanan->departemen->loket->nama_loket;
            return $antrian;
        });

        return view('panggilan.petugas', compact(
            'loket',
            'loketId',
            'antrianTersisa',
            'currentCalling',
            'antrians'
        ));
    }

    // Aksi Next - untuk admin dan petugas
    public function next(Request $request)
    {
        $user = Auth::user();

        // Admin menggunakan parameter id_loket, petugas menggunakan loket yang ditugaskan
        if ($user->role === 1) {
            // Admin - validasi parameter
            $request->validate([
                'id_loket' => 'required|exists:lokets,id'
            ]);
            $idLoket = $request->id_loket;
        } elseif ($user->role === 2) {
            // Petugas - gunakan loket yang ditugaskan
            if (!$user->id_loket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda belum ditugaskan ke loket tertentu.'
                ], 403);
            }
            $idLoket = $user->id_loket;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
            ], 403);
        }

        try {
            // Import AntrianController dan panggil method langsung
            $antrianController = new \App\Http\Controllers\Api\AntrianController();
            $fakeRequest = new Request(['id_loket' => $idLoket]);
            $response = $antrianController->callNextAntrian($fakeRequest);

            // Parse response
            $responseData = json_decode($response->getContent(), true);
            $statusCode = $response->getStatusCode();

            return response()->json($responseData, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memanggil antrian berikutnya: ' . $e->getMessage()
            ], 500);
        }
    }

    // Aksi Recall - untuk admin dan petugas
    public function recall(Request $request)
    {
        $user = Auth::user();

        // Admin menggunakan parameter id_loket, petugas menggunakan loket yang ditugaskan
        if ($user->role === 1) {
            // Admin - validasi parameter
            $request->validate([
                'id_loket' => 'required|exists:lokets,id'
            ]);
            $idLoket = $request->id_loket;
        } elseif ($user->role === 2) {
            // Petugas - gunakan loket yang ditugaskan
            if (!$user->id_loket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda belum ditugaskan ke loket tertentu.'
                ], 403);
            }
            $idLoket = $user->id_loket;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
            ], 403);
        }

        try {
            // Import AntrianController dan panggil method langsung
            $antrianController = new \App\Http\Controllers\Api\AntrianController();
            $fakeRequest = new Request(['id_loket' => $idLoket]);
            $response = $antrianController->recallAntrian($fakeRequest);

            // Parse response
            $responseData = json_decode($response->getContent(), true);
            $statusCode = $response->getStatusCode();

            return response()->json($responseData, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan recall: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk finish antrian - untuk admin dan petugas
    public function finish(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_antrian' => 'required|exists:antrians,id'
        ]);

        // Jika petugas, validasi bahwa antrian ini milik loket petugas
        if ($user->role === 2 && $user->id_loket) {
            $antrian = Antrian::with(['pelayanan.departemen'])
                ->where('id', $request->id_antrian)
                ->first();

            if (!$antrian || $antrian->pelayanan->departemen->id_loket != $user->id_loket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Antrian tidak ditemukan atau bukan milik loket Anda.'
                ], 404);
            }
        }

        try {
            $idAntrian = $request->id_antrian;

            // Import AntrianController dan panggil method langsung
            $antrianController = new \App\Http\Controllers\Api\AntrianController();
            $fakeRequest = new Request(['id_antrian' => $idAntrian]);
            $response = $antrianController->finishAntrian($fakeRequest);

            // Parse response
            $responseData = json_decode($response->getContent(), true);
            $statusCode = $response->getStatusCode();

            return response()->json($responseData, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyelesaikan antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk skip antrian - untuk admin dan petugas
    public function skip(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_antrian' => 'required|exists:antrians,id'
        ]);

        // Jika petugas, validasi bahwa antrian ini milik loket petugas
        if ($user->role === 2 && $user->id_loket) {
            $antrian = Antrian::with(['pelayanan.departemen'])
                ->where('id', $request->id_antrian)
                ->first();

            if (!$antrian || $antrian->pelayanan->departemen->id_loket != $user->id_loket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Antrian tidak ditemukan atau bukan milik loket Anda.'
                ], 404);
            }
        }

        try {
            $idAntrian = $request->id_antrian;

            // Import AntrianController dan panggil method langsung
            $antrianController = new \App\Http\Controllers\Api\AntrianController();
            $fakeRequest = new Request(['id_antrian' => $idAntrian]);
            $response = $antrianController->SkipAntrian($fakeRequest);

            // Parse response
            $responseData = json_decode($response->getContent(), true);
            $statusCode = $response->getStatusCode();

            return response()->json($responseData, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal melewati antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk mendapatkan statistik - untuk petugas
    public function getStats()
    {
        $user = Auth::user();

        if ($user->role !== 2 || !$user->id_loket) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $today = Carbon::today();
        $loketId = $user->id_loket;

        $stats = [
            'menunggu' => Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $loketId)
                ->whereDate('antrians.created_at', $today)
                ->where('antrians.status_antrian', 1)
                ->count(),

            'dipanggil' => Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $loketId)
                ->whereDate('antrians.created_at', $today)
                ->where('antrians.status_antrian', 2)
                ->count(),

            'selesai' => Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $loketId)
                ->whereDate('antrians.created_at', $today)
                ->where('antrians.status_antrian', 3)
                ->count(),

            'dilewati' => Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $loketId)
                ->whereDate('antrians.created_at', $today)
                ->where('antrians.status_antrian', 4)
                ->count(),
        ];

        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }
}