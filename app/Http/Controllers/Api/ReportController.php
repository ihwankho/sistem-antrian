<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Antrian;
use App\Models\Loket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage; // Tidak diperlukan
use Illuminate\Support\Facades\Auth; 


class ReportController extends Controller
{
    /**
     * Mengambil ringkasan dan daftar aktivitas antrian untuk laporan.
     */
    public function getActivityHistory(Request $request)
    {
        try {
            $query = \App\Models\Antrian::with(['pengunjung', 'pelayanan.departemen.loket']);
    
            // Filter tanggal
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->whereBetween('created_at', [today()->startOfDay(), today()->endOfDay()]);
            }
    
            // Filter Role
            $user = Auth::user();
            if ($user && $user->role === 2) {
                $loketId = $user->id_loket;
                if ($loketId) {
                    $query->whereHas('pelayanan.departemen', function ($q) use ($loketId) {
                        $q->where('id_loket', $loketId);
                    });
                } else {
                    $summaryKosong = ['total'=>0, 'menunggu'=>0, 'dipanggil'=>0, 'selesai'=>0, 'dilewati'=>0, 'estimasi_waktu'=>0];
                    return response()->json(['summary' => $summaryKosong, 'data' => []]);
                }
            }
            else if ($user && $user->role === 1) {
                if ($request->filled('department_id')) {
                    // Membersihkan input (menghapus ':')
                    $deptId = str_replace(':', '', $request->department_id);
                    if (is_numeric($deptId)) {
                         $query->whereHas('pelayanan.departemen', fn ($q) => $q->where('id', $deptId));
                    }
                }
            }
    
            // Filter status
            if ($request->filled('status')) {
                $query->where('status_antrian', (int)$request->status);
            }
    
            $results = $query->orderBy('created_at', 'desc')->get();
            $allLokets = \App\Models\Loket::orderBy('id', 'ASC')->pluck('id')->toArray();
    
            // Logika Summary
            $totalSelesai = $results->where('status_antrian', 3)->count();
            $waktuPenyelesaian = 0;
            if ($totalSelesai > 0) {
                $totalMenit = $results->where('status_antrian', 3)->sum(function ($antrian) {
                    if ($antrian->waktu_panggil && $antrian->updated_at) {
                        $waktuPanggil = \Carbon\Carbon::parse($antrian->waktu_panggil);
                        $waktuSelesai = \Carbon\Carbon::parse($antrian->updated_at);
                        if ($waktuSelesai->isAfter($waktuPanggil)) {
                            return $waktuSelesai->diffInMinutes($waktuPanggil);
                        }
                    }
                    return 0;
                });
                $waktuPenyelesaian = round($totalMenit / $totalSelesai);
            }
    
            $summary = [
                'total' => $results->count(), 'menunggu' => $results->where('status_antrian', 1)->count(),
                'dipanggil' => $results->where('status_antrian', 2)->count(), 'selesai' => $totalSelesai,
                'dilewati' => $results->where('status_antrian', 4)->count(), 'estimasi_waktu' => $waktuPenyelesaian,
            ];
    
            $formattedResults = $results->map(function ($item) use ($allLokets) {
                $pengunjung = $item->pengunjung;
                if ($pengunjung) {
                    
                    // --- [PERUBAHAN DI SINI] ---
                    // Menggunakan asset() untuk menunjuk ke 'public/images/...'
                    // basename() digunakan untuk keamanan, mengambil nama file saja
                    // Ini akan mengubah 'images/foto.jpg' atau 'wajah/foto.jpg' (data lama) menjadi 'foto.jpg'
                    // lalu digabung dengan 'images/'
                    $pengunjung->foto_ktp_url = $pengunjung->foto_ktp ? asset('images/' . basename($pengunjung->foto_ktp)) : null;
                    $pengunjung->foto_wajah_url = $pengunjung->foto_wajah ? asset('images/' . basename($pengunjung->foto_wajah)) : null;
                    // --- [AKHIR PERUBAHAN] ---

                }
                $statusMap = [1 => 'Menunggu', 2 => 'Dipanggil', 3 => 'Selesai', 4 => 'Dilewati'];
                $nama_pengunjung = optional($pengunjung)->nama_pengunjung ?? 'Data Hilang';
                $nama_layanan = optional($item->pelayanan)->nama_layanan ?? 'Data Hilang';
                $nama_departemen = optional(optional($item->pelayanan)->departemen)->nama_departemen ?? 'Data Hilang';
                $nama_loket = optional(optional(optional($item->pelayanan)->departemen)->loket)->nama_loket ?? 'Data Hilang';
                $nomorAntrianLengkap = 'N/A';
                if ($item->pelayanan && $item->pelayanan->departemen) {
                    $loket_id = $item->pelayanan->departemen->id_loket;
                    $loketIndex = array_search($loket_id, $allLokets);
                    if ($loketIndex !== false) {
                        $kodeHuruf = chr(65 + $loketIndex);
                        $nomorAntrianLengkap = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
                    }
                }
                return [
                    'waktu_daftar' => $item->created_at->toDateTimeString(), 'nomor_antrian_lengkap' => $nomorAntrianLengkap,
                    'nama_pengunjung' => $nama_pengunjung, 'nama_layanan' => $nama_layanan,
                    'nama_departemen' => $nama_departemen, 'nama_loket' => $nama_loket,
                    'status' => $statusMap[$item->status_antrian] ?? 'Tidak Diketahui', 'pengunjung' => $pengunjung,
                ];
            });
    
            return response()->json(['summary' => $summary, 'data' => $formattedResults]);
    
        } catch (\Exception $e) {
            Log::error('Error in ReportController@getActivityHistory: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Kesalahan Server.', 'error' => $e->getMessage()], 500);
        }
    }

    // Anda bisa menambahkan fungsi laporan lainnya di sini di masa depan
}