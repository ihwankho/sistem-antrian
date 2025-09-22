<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Antrian;
use App\Models\Loket; // <-- Tambahkan ini untuk mengambil data loket

class ReportController extends Controller
{
    public function getActivityHistory(Request $request)
    {
        $query = Antrian::with([
            'pelayanan.departemen.loket',
            'pengunjung'
        ]);

        // ... (Logika filter tidak ada yang diubah)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status_antrian', $request->status);
        }
        $user = Auth::user();
        if ($user && $user->role === 2) {
            $loketId = $user->id_loket;
            if ($loketId) {
                $query->whereHas('pelayanan.departemen', function ($q) use ($loketId) {
                    $q->where('id_loket', $loketId);
                });
            } else {
                return response()->json([]);
            }
        } else if ($user && $user->role === 1) {
            if ($request->filled('department_id')) {
                $departmentId = $request->department_id;
                $query->whereHas('pelayanan.departemen', function ($q) use ($departmentId) {
                    $q->where('id', $departmentId);
                });
            }
        }

        $reportData = $query->orderBy('created_at', 'desc')->get();

        // ===================================================================
        // PERUBAHAN DIMULAI DI SINI
        // 1. Ambil daftar ID loket yang sudah terurut sekali saja sebelum loop
        // ===================================================================
        $sortedLoketIds = Loket::orderBy('id', 'asc')->pluck('id')->toArray();

        $formattedData = $reportData->map(function ($item) use ($sortedLoketIds) {
            
            // 2. Hitung kode antrian berdasarkan posisi ID loket
            $kodeAntrian = 'X'; // Default jika loket tidak ditemukan
            $currentLoketId = $item->pelayanan->departemen->loket->id ?? null;

            if ($currentLoketId) {
                // Cari posisi/index dari ID loket saat ini (hasilnya: 0, 1, 2, ...)
                $loketIndex = array_search($currentLoketId, $sortedLoketIds);
                
                if ($loketIndex !== false) {
                    // Ubah index menjadi huruf (0 -> A, 1 -> B, dst.)
                    $kodeAntrian = chr(65 + $loketIndex);
                }
            }
            // ===================================================================
            // AKHIR PERUBAHAN
            // ===================================================================

            $nomorAntrianLengkap = $kodeAntrian . str_pad($item->nomor_antrian ?? 0, 3, '0', STR_PAD_LEFT);
            $namaLoket = $item->pelayanan->departemen->loket->nama_loket ?? '-';
            
            $statusText = 'Tidak Diketahui';
            switch ($item->status_antrian) {
                case 1: $statusText = 'Menunggu'; break;
                case 2: $statusText = 'Dipanggil'; break;
                case 3: $statusText = 'Selesai'; break;
                case 4: $statusText = 'Dilewati'; break;
            }

            return [
                'waktu_daftar' => $item->created_at,
                'nomor_antrian_lengkap' => $nomorAntrianLengkap,
                'nama_pengunjung' => $item->pengunjung->nama_pengunjung ?? '-',
                'nama_layanan' => $item->pelayanan->nama_layanan ?? '-',
                'nama_departemen' => $item->pelayanan->departemen->nama_departemen ?? '-',
                'nama_loket' => $namaLoket,
                'status' => $statusText,
            ];
        });

        return response()->json($formattedData);
    }
}