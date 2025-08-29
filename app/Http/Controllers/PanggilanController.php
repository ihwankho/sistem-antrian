<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loket;
use App\Models\Antrian;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PanggilanController extends Controller
{
    // Halaman Admin
    public function admin(Request $request)
    {
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

    // Aksi Next - PERBAIKAN: Ganti menggunakan direct call, bukan HTTP
    public function next(Request $request)
    {
        $request->validate([
            'id_loket' => 'required|exists:lokets,id'
        ]);

        try {
            $idLoket = $request->id_loket;

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

    // Aksi Recall - PERBAIKAN: Ganti menggunakan direct call dan fix parameter
    public function recall(Request $request)
    {
        $request->validate([
            'id_loket' => 'required|exists:lokets,id'
        ]);

        try {
            $idLoket = $request->id_loket;

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

    // Method baru untuk finish antrian
    public function finish(Request $request)
    {
        $request->validate([
            'id_antrian' => 'required|exists:antrians,id'
        ]);

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

    // Method baru untuk skip antrian
    public function skip(Request $request)
    {
        $request->validate([
            'id_antrian' => 'required|exists:antrians,id'
        ]);

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
}