<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loket;
use App\Models\Antrian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisplayController extends Controller
{
    /**
     * Menampilkan halaman display antrian
     */
    public function index()
    {
        return view('Display');
    }

    /**
     * API untuk mengambil data antrian semua loket
     */
    public function getQueueData()
    {
        try {
            $today = Carbon::today();
            
            // Ambil semua loket dengan urutan berdasarkan ID
            $lokets = Loket::orderBy('id', 'ASC')->get();
            
            // Buat mapping ID loket ke huruf
            $loketIds = $lokets->pluck('id')->toArray();
            $loketMap = [];
            foreach ($loketIds as $index => $id) {
                $loketMap[$id] = chr(65 + $index); // A, B, C, dst
            }

            $loketData = [];
            
            foreach ($lokets as $loket) {
                $loketId = $loket->id;
                $kodeHuruf = $loketMap[$loketId];
                
                // Ambil antrian yang sedang dipanggil untuk loket ini
                $currentCalling = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loketId)
                    ->whereDate('antrians.created_at', $today)
                    ->where('antrians.status_antrian', 2) // status dipanggil
                    ->select('antrians.nomor_antrian')
                    ->first();
                
                // Format kode antrian yang sedang dipanggil
                $currentCallingFormatted = null;
                if ($currentCalling) {
                    $currentCallingFormatted = $kodeHuruf . str_pad($currentCalling->nomor_antrian, 3, '0', STR_PAD_LEFT);
                }
                
                // Ambil antrian yang menunggu untuk loket ini (max 10)
                $nextQueues = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loketId)
                    ->whereDate('antrians.created_at', $today)
                    ->where('antrians.status_antrian', 1) // status menunggu
                    ->orderBy('antrians.nomor_antrian', 'asc')
                    ->select('antrians.nomor_antrian')
                    ->limit(10)
                    ->get()
                    ->map(function ($antrian) use ($kodeHuruf) {
                        return $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
                    })
                    ->toArray();
                
                $loketData[] = [
                    'id' => $loket->id,
                    'nama_loket' => $loket->nama_loket,
                    'current_calling' => $currentCallingFormatted,
                    'next_queues' => $nextQueues,
                    'waiting_count' => count($nextQueues)
                ];
            }
            
            // Hitung statistik keseluruhan
            $stats = $this->getQueueStats($today);
            
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => [
                    'lokets' => $loketData,
                    'stats' => $stats,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Mengambil statistik antrian keseluruhan
     */
    private function getQueueStats($date)
    {
        try {
            // Total antrian hari ini
            $totalToday = Antrian::whereDate('created_at', $date)->count();
            
            // Antrian yang menunggu (status 1)
            $waiting = Antrian::whereDate('created_at', $date)
                ->where('status_antrian', 1)
                ->count();
            
            // Antrian yang sedang dipanggil (status 2)
            $calling = Antrian::whereDate('created_at', $date)
                ->where('status_antrian', 2)
                ->count();
            
            // Antrian yang sudah selesai (status 3)
            $completed = Antrian::whereDate('created_at', $date)
                ->where('status_antrian', 3)
                ->count();
            
            return [
                'total' => $totalToday,
                'waiting' => $waiting,
                'calling' => $calling,
                'completed' => $completed,
                'skipped' => Antrian::whereDate('created_at', $date)
                    ->where('status_antrian', 4)
                    ->count()
            ];
            
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'waiting' => 0,
                'calling' => 0,
                'completed' => 0,
                'skipped' => 0
            ];
        }
    }

    /**
     * API untuk mendapatkan antrian yang sedang dipanggil saja
     */
    public function getCurrentCallingOnly()
    {
        try {
            $today = Carbon::today();
            
            // Ambil semua loket
            $lokets = Loket::orderBy('id', 'ASC')->get();
            
            // Buat mapping ID loket ke huruf
            $loketIds = $lokets->pluck('id')->toArray();
            $loketMap = [];
            foreach ($loketIds as $index => $id) {
                $loketMap[$id] = chr(65 + $index);
            }

            $currentCallings = [];
            
            foreach ($lokets as $loket) {
                $loketId = $loket->id;
                $kodeHuruf = $loketMap[$loketId];
                
                // Ambil antrian yang sedang dipanggil
                $calling = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loketId)
                    ->whereDate('antrians.created_at', $today)
                    ->where('antrians.status_antrian', 2)
                    ->select('antrians.nomor_antrian')
                    ->first();
                
                $currentCallings[] = [
                    'loket' => $loket->nama_loket,
                    'kode_antrian' => $calling 
                        ? $kodeHuruf . str_pad($calling->nomor_antrian, 3, '0', STR_PAD_LEFT)
                        : null
                ];
            }
            
            return response()->json([
                'status' => true,
                'data' => $currentCallings
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan detail antrian berdasarkan loket
     */
    public function getQueueByLoket($loketId)
    {
        try {
            $today = Carbon::today();
            
            // Validasi loket
            $loket = Loket::findOrFail($loketId);
            
            // Ambil mapping huruf
            $allLokets = Loket::orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($loketId, $allLokets);
            $kodeHuruf = chr(65 + $loketIndex);
            
            // Ambil semua antrian untuk loket ini hari ini
            $antrians = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('pengunjungs', 'antrians.id_pengunjung', '=', 'pengunjungs.id')
                ->where('departemens.id_loket', $loketId)
                ->whereDate('antrians.created_at', $today)
                ->whereIn('antrians.status_antrian', [1, 2, 3, 4])
                ->select(
                    'antrians.id',
                    'antrians.nomor_antrian',
                    'antrians.status_antrian',
                    'antrians.created_at',
                    'pengunjungs.nama_pengunjung',
                    'pelayanans.nama_pelayanan'
                )
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->get()
                ->map(function ($antrian) use ($kodeHuruf) {
                    return [
                        'id' => $antrian->id,
                        'kode_antrian' => $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT),
                        'nomor_antrian' => $antrian->nomor_antrian,
                        'nama_pengunjung' => $antrian->nama_pengunjung,
                        'nama_pelayanan' => $antrian->nama_pelayanan,
                        'status_antrian' => $antrian->status_antrian,
                        'status_text' => $this->getStatusText($antrian->status_antrian),
                        'waktu_daftar' => $antrian->created_at->format('H:i:s')
                    ];
                });
            
            // Statistik untuk loket ini
            $stats = [
                'total' => $antrians->count(),
                'menunggu' => $antrians->where('status_antrian', 1)->count(),
                'dipanggil' => $antrians->where('status_antrian', 2)->count(),
                'selesai' => $antrians->where('status_antrian', 3)->count(),
                'dilewati' => $antrians->where('status_antrian', 4)->count(),
            ];
            
            return response()->json([
                'status' => true,
                'data' => [
                    'loket' => [
                        'id' => $loket->id,
                        'nama_loket' => $loket->nama_loket,
                        'kode_huruf' => $kodeHuruf
                    ],
                    'antrians' => $antrians,
                    'stats' => $stats
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Helper function untuk mendapatkan teks status
     */
    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'Menunggu';
            case 2:
                return 'Dipanggil';
            case 3:
                return 'Selesai';
            case 4:
                return 'Dilewati';
            default:
                return 'Tidak Dikenal';
        }
    }

    /**
     * API untuk mendapatkan ringkasan harian
     */
    public function getDailySummary(Request $request)
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $targetDate = Carbon::parse($date);
            
            // Ambil semua loket
            $lokets = Loket::orderBy('id', 'ASC')->get();
            
            $summary = [];
            
            foreach ($lokets as $index => $loket) {
                $kodeHuruf = chr(65 + $index);
                
                // Hitung statistik per loket
                $totalAntrian = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loket->id)
                    ->whereDate('antrians.created_at', $targetDate)
                    ->count();
                
                $selesai = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loket->id)
                    ->whereDate('antrians.created_at', $targetDate)
                    ->where('antrians.status_antrian', 3)
                    ->count();
                
                $dilewati = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loket->id)
                    ->whereDate('antrians.created_at', $targetDate)
                    ->where('antrians.status_antrian', 4)
                    ->count();
                
                // Waktu rata-rata pelayanan (estimasi berdasarkan antrian yang selesai)
                $avgTime = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                    ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                    ->where('departemens.id_loket', $loket->id)
                    ->whereDate('antrians.created_at', $targetDate)
                    ->where('antrians.status_antrian', 3)
                    ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, antrians.created_at, antrians.updated_at)'));
                
                $summary[] = [
                    'loket' => $loket->nama_loket,
                    'kode_huruf' => $kodeHuruf,
                    'total_antrian' => $totalAntrian,
                    'selesai' => $selesai,
                    'dilewati' => $dilewati,
                    'pending' => $totalAntrian - $selesai - $dilewati,
                    'efisiensi' => $totalAntrian > 0 ? round(($selesai / $totalAntrian) * 100, 1) : 0,
                    'rata_rata_waktu' => $avgTime ? round($avgTime, 1) : 0
                ];
            }
            
            return response()->json([
                'status' => true,
                'data' => [
                    'date' => $targetDate->format('Y-m-d'),
                    'date_formatted' => $targetDate->format('d F Y'),
                    'summary' => $summary,
                    'grand_total' => [
                        'total_antrian' => array_sum(array_column($summary, 'total_antrian')),
                        'selesai' => array_sum(array_column($summary, 'selesai')),
                        'dilewati' => array_sum(array_column($summary, 'dilewati')),
                        'pending' => array_sum(array_column($summary, 'pending'))
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Endpoint untuk testing koneksi
     */
    public function ping()
    {
        return response()->json([
            'status' => true,
            'message' => 'Server is running',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ], 200);
    }
}