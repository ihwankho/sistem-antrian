<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\Pengunjung;
use App\Models\Pelayanan;
use App\Models\Loket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpParser\Node\Expr\Cast;
use App\Models\Departemen;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AntrianController extends Controller
{
    //Menambah antrian
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // [PERBAIKAN] Validasi NIK dibuat lebih ketat, harus 16 digit angka.
            $validated = $request->validate([
                'nama_pengunjung' => 'required|string|max:255',
                'nik' => 'required|digits:16',
                'no_hp' => 'nullable|string|max:15',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'alamat' => 'nullable|string',
                'id_pelayanan' => 'required|exists:pelayanans,id',
                'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'foto_wajah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $fotoKtpPath = null;
            $fotoWajahPath = null;

            if ($request->hasFile('foto_ktp')) {
                $fotoKtpPath = $request->file('foto_ktp')->store('ktp', 'public');
            }
            if ($request->hasFile('foto_wajah')) {
                $fotoWajahPath = $request->file('foto_wajah')->store('wajah', 'public');
            }

            $pengunjung = Pengunjung::create([
                'nama_pengunjung' => $validated['nama_pengunjung'],
                'nik' => $validated['nik'],
                'no_hp' => $validated['no_hp'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'foto_ktp' => $fotoKtpPath,
                'foto_wajah' => $fotoWajahPath
            ]);

            $pelayanan = Pelayanan::with('departemen.loket')->findOrFail($validated['id_pelayanan']);
            $idLoket = $pelayanan->departemen->loket->id;

            $lastQueue = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->whereDate('antrians.created_at', now()->toDateString())
                ->max('nomor_antrian');

            $nomorAntrian = $lastQueue ? $lastQueue + 1 : 1;

            $antrian = Antrian::create([
                'uuid' => Str::uuid(),
                'nomor_antrian' => $nomorAntrian,
                'status_antrian' => 1, // 1 = menunggu
                'id_pengunjung' => $pengunjung->id,
                'id_pelayanan' => $pelayanan->id
            ]);

            // [CATATAN] Query ini akan dijalankan setiap kali ada antrian baru.
            // Untuk optimasi di masa depan, daftar loket ini bisa di-cache.
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets);
            $kodeHuruf = chr(65 + $loketIndex);

            $kodeAntrian = $kodeHuruf . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT);

            DB::commit();

            $tiket = [
                'id' => $antrian->id,
                'uuid' => $antrian->uuid,
                'nomor_antrian' => $kodeAntrian,
                'nama_departemen' => $pelayanan->departemen->nama_departemen ?? null,
                'nama_loket' => $pelayanan->departemen->loket->nama_loket ?? null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Tiket antrian berhasil dibuat',
                'data' => $tiket
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat tiket antrian: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat tiket antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Daftar antrian berdasarkan loket
    public function getByLoket($id_loket)
    {
        try {
            $antrian = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->where('lokets.id', $id_loket)
                // [PERBAIKAN] Menambahkan filter HANYA untuk antrian hari ini.
                ->whereDate('antrians.created_at', now()->toDateString())
                ->select(
                    'antrians.id',
                    'antrians.nomor_antrian',
                    'departemens.id_loket',
                    'departemens.nama_departemen',
                    'lokets.nama_loket',
                    'antrians.status_antrian',
                    'antrians.created_at'
                )
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->get();

            // [PERBAIKAN] Logika penanganan "antrian kosong" dipindah ke bawah agar lebih akurat.
            if ($antrian->isEmpty()) {
                return response()->json([
                    'status' => true, // Diubah menjadi true karena query berhasil, hanya datanya yang kosong.
                    'message' => 'Tidak ada antrian untuk loket ini hari ini.',
                    'data' => [] // Kembalikan array kosong.
                ], 200);
            }

            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();

            $antrian = $antrian->map(function ($item) use ($lokets) {
                $loketIndex = array_search($item->id_loket, $lokets);
                $kodeHuruf = chr(65 + $loketIndex);
                $item->kode_antrian = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
                unset($item->id_loket);
                return $item;
            });

            return response()->json([
                'status' => true,
                'loket' => $antrian->first()->nama_loket,
                'data' => $antrian
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //semua antrian dari semua loket
 public function getAllAntrian()
 {
     try {
         // [BARU] Hitung rata-rata waktu pelayanan untuk hari ini
         $completedToday = Antrian::where('status_antrian', 3) // Hanya yang Selesai
             ->whereDate('updated_at', today())
             ->whereNotNull('waktu_panggil')
             ->get();

         $avgServiceTime = 0;
         if ($completedToday->count() > 0) {
             $totalMinutes = $completedToday->sum(function ($antrian) {
                 $waktuPanggil = \Carbon\Carbon::parse($antrian->waktu_panggil);
                 $waktuSelesai = \Carbon\Carbon::parse($antrian->updated_at);
                 return $waktuSelesai->isAfter($waktuPanggil) ? $waktuSelesai->diffInMinutes($waktuPanggil) : 0;
             });
             // Jika total menit lebih dari 0, hitung rata-rata. Minimal 1 menit.
             if ($totalMinutes > 0) {
                 $avgServiceTime = max(1, round($totalMinutes / $completedToday->count()));
             }
         }

         // Jika tidak ada data, gunakan default 7 menit
         if ($avgServiceTime === 0) {
             $avgServiceTime = 7; // Default waktu pelayanan (dalam menit)
         }


         // Ambil data antrian saat ini (logika lama tetap sama)
         $data = \DB::table('antrians')
             ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
             ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
             ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
             ->select('antrians.id', 'antrians.nomor_antrian', 'departemens.id_loket', 'lokets.nama_loket', 'antrians.status_antrian')
             ->whereDate('antrians.created_at', today())
             ->whereIn('antrians.status_antrian', [1, 2]) // Hanya ambil yang Menunggu & Dipanggil
             ->orderBy('lokets.id', 'asc')
             ->orderBy('antrians.nomor_antrian', 'asc')
             ->get();

         $lokets = \DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();

         $data = $data->map(function ($item) use ($lokets) {
             $loketIndex = array_search($item->id_loket, $lokets);
             $kodeHuruf = chr(65 + $loketIndex);
             $item->kode_antrian = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
             unset($item->id_loket);
             return $item;
         });

         $grouped = $data->groupBy('nama_loket')->map(function ($items, $loket) {
             return ['loket' => $loket, 'antrian' => $items->values()];
         })->values();

         // [DIUBAH] Tambahkan avg_service_time ke dalam response JSON
         return response()->json([
             'status' => true,
             'message' => 'Data antrian berhasil diambil',
             'avg_service_time' => $avgServiceTime,
             'data' => $grouped
         ], 200);

     } catch (\Throwable $e) {
         \Log::error("Error in getAllAntrian: " . $e->getMessage());
         return response()->json([
             'status' => false,
             'message' => 'Terjadi kesalahan saat mengambil data antrian',
             'error' => $e->getMessage()
         ], 500);
     }
 }

    public function showPublicTicketByUuid($uuid)
    {
        try {
            $antrian = Antrian::with(['pelayanan.departemen.loket'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($antrian->pelayanan->departemen->loket->id, $lokets);
            $kodeHuruf = chr(65 + $loketIndex);
            $kodeAntrian = $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);

            $data = [
                'id' => $antrian->id,
                'uuid' => $antrian->uuid,
                'nomor_antrian_lengkap' => $kodeAntrian,
                'nama_layanan' => $antrian->pelayanan->nama_layanan,
                'nama_loket' => $antrian->pelayanan->departemen->loket->nama_loket,
                'created_at' => $antrian->created_at,
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            // [KONSISTENSI] Menggunakan format respons JSON yang konsisten dengan method lain.
            return response()->json(['status' => false, 'message' => 'Tiket tidak ditemukan'], 404);
        }
    }

    public function showDetailByUuid($uuid)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            return response()->json($antrian);
        } catch (\Exception $e) {
            // [KONSISTENSI] Menggunakan format respons JSON yang konsisten.
            return response()->json([
                'status' => false,
                'message' => 'Data detail antrian tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }


public function callNextAntrian(Request $request)
{
    // Memulai transaksi untuk memastikan konsistensi data
    DB::beginTransaction();
    try {
        // Validasi input id_loket
        $validated = $request->validate([
            'id_loket' => 'required|exists:lokets,id',
        ]);
        $idLoket = $validated['id_loket'];

        // Cek apakah sudah ada antrian yang sedang dipanggil di loket ini
        $isCalling = Antrian::whereHas('pelayanan.departemen', fn ($q) => $q->where('id_loket', $idLoket))
            ->where('status_antrian', 2) // Status 'Dipanggil'
            ->whereDate('created_at', today())
            ->exists();

        if ($isCalling) {
            return response()->json([
                'status' => false,
                'message' => 'Masih ada antrian yang sedang dipanggil. Selesaikan atau lewati antrian tersebut.'
            ], 409); // 409 Conflict
        }

        // Ambil antrian berikutnya yang statusnya 'Menunggu'
        $nextAntrian = Antrian::whereHas('pelayanan.departemen', fn ($q) => $q->where('id_loket', $idLoket))
            ->where('status_antrian', 1) // Status 'Menunggu'
            ->whereDate('created_at', today())
            ->orderBy('nomor_antrian', 'asc')
            ->first();

        // Jika tidak ada antrian lagi
        if (!$nextAntrian) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada antrian lagi di loket ini.'
            ], 404); // 404 Not Found
        }

        // Update status antrian dan catat waktu panggil
        $nextAntrian->status_antrian = 2;
        $nextAntrian->waktu_panggil = now(); // <-- Mencatat waktu saat antrian dipanggil
        $nextAntrian->save();

        // Siapkan data untuk respons
        $loket = Loket::find($idLoket);
        $sortedLokets = Loket::orderBy('id')->pluck('id')->toArray();
        $loketIndex = array_search($idLoket, $sortedLokets);

        if ($loketIndex === false) {
            throw new \Exception('ID Loket tidak ditemukan dalam daftar loket sistem.');
        }

        $kodeHuruf = chr(65 + $loketIndex);
        $kodeAntrian = $kodeHuruf . str_pad($nextAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
        $kodeAntrianSpasi = implode(' ', str_split($kodeAntrian));

        // Menyimpan perubahan ke database
        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Antrian berhasil dipanggil',
            'data' => [
                'id' => $nextAntrian->id,
                'kode_antrian' => $kodeAntrian,
                'nomor_antrian' => $nextAntrian->nomor_antrian,
                'status' => $nextAntrian->status_antrian,
                'loket' => $loket->nama_loket,
                'voice_text' => "Silakan antrian $kodeAntrianSpasi menuju ke loket $loket->nama_loket"
            ]
        ], 200);
        
    } catch (\Throwable $e) {
        // Batalkan semua perubahan jika terjadi error
        DB::rollBack();
        Log::error('Gagal memanggil antrian: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Terjadi kesalahan saat memanggil antrian',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function finishAntrian(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'id_antrian' => 'required|exists:antrians,id',
            ]);

            $antrian = Antrian::findOrFail($validated['id_antrian']);

            // [PERBAIKAN] Pesan error dibuat lebih spesifik.
            if ($antrian->status_antrian != 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal, antrian ini tidak dalam status "Dipanggil".'
                ], 400); // 400 Bad Request lebih sesuai.
            }

            $antrian->status_antrian = 3;
            $antrian->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil diselesaikan',
                'data' => [
                    'id' => $antrian->id,
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'status' => $antrian->status_antrian
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyelesaikan antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // [KONSISTENSI] Menggunakan type-hint `Request $request` seperti method lainnya.
    public function SkipAntrian(Request $request)
    {
        $request->validate([
            'id_antrian' => 'required|exists:antrians,id',
        ]);

        try {
            $antrian = DB::transaction(function () use ($request) {
                $antrian = Antrian::findOrFail($request->id_antrian);

                if ($antrian->status_antrian != 2) {
                    // [PERBAIKAN] Menggunakan response JSON konsisten, bukan abort().
                    throw new Exception('Gagal, antrian ini tidak dalam status "Dipanggil".', 400);
                }

                $antrian->update(['status_antrian' => 4]); // 4 = Dilewati
                return $antrian;
            });

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dilewati',
                'data' => [
                    'id' => $antrian->id,
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'status' => $antrian->status_antrian
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getCode() == 400 ? $e->getMessage() : 'Gagal melewati antrian.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $e->getCode() == 400 ? 400 : 500);
        }
    }

    public function recallAntrian(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_loket' => 'required|exists:lokets,id',
            ]);

            $idLoket = $validated['id_loket'];

            // Query sudah benar dengan filter tanggal hari ini.
            $currentAntrian = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->where('antrians.status_antrian', 2)
                ->whereDate('antrians.created_at', now()->toDateString())
                ->select('antrians.*')
                ->orderBy('antrians.updated_at', 'desc')
                ->first();

            if (!$currentAntrian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian yang sedang dipanggil untuk dipanggil ulang.'
                ], 404);
            }

            $loket = Loket::find($idLoket);
            $kodeLoket = $loket->nama_loket ?? 'Loket';

            $lokets = Loket::orderBy('id')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets);
            $kodeHuruf = chr(65 + $loketIndex);
            $kodeAntrian = $kodeHuruf . str_pad($currentAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
            $kodeAntrianSpasi = implode(' ', str_split($kodeAntrian));

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dipanggil ulang',
                'data' => [
                    'id' => $currentAntrian->id,
                    'kode_antrian' => $kodeAntrian,
                    'nomor_antrian' => $currentAntrian->nomor_antrian,
                    'status' => $currentAntrian->status_antrian,
                    'loket' => $kodeLoket,
                    'voice_text' => "Silakan antrian $kodeAntrianSpasi menuju ke loket $kodeLoket"
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memanggil ulang antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAntrianDipanggil()
    {
        try {
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->get();

            $antrians = DB::table('antrians')
                ->where('antrians.status_antrian', 2)
                // [PERBAIKAN] Menambahkan filter HANYA untuk antrian hari ini.
                ->whereDate('antrians.created_at', now()->toDateString())
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select(
                    'antrians.nomor_antrian',
                    'lokets.id as id_loket',
                    'lokets.nama_loket'
                )
                ->get();

            $result = $lokets->map(function ($loket, $index) use ($antrians) {
                $antrian = $antrians->firstWhere('id_loket', $loket->id);
                $kodeHuruf = chr(65 + $index);
                $kodeAntrian = $antrian ? $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) : null;
                return [
                    'loket' => $loket->nama_loket,
                    'kode_antrian' => $kodeAntrian
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Data antrian yang sedang dipanggil berhasil diambil',
                'data' => $result
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data antrian yang sedang dipanggil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //ambil data antrian dan pengunjung
    public function ShowPe($id)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])->findOrFail($id);
            return response()->json($antrian);
        } catch (Exception $e) {
            // [KONSISTENSI] Menggunakan format respons JSON yang konsisten.
            return response()->json([
                'status' => false,
                'message' => 'Data antrian tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function laporanBulanan(Request $request)
    {
        try {
            $bulan = $request->input('bulan', Carbon::now()->month);
            $tahun = $request->input('tahun', Carbon::now()->year);

            $data = DB::table('antrians')
                ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total_antrian, SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as selesai, SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as skip')
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'ASC')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Laporan bulanan berhasil diambil',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPerformanceStats(Request $request)
    {
        try {
            $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $kpi = DB::table('antrians')
                ->selectRaw('COUNT(*) as total_antrian, SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as jumlah_selesai, SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as jumlah_dilewati')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->first();

            $grafikHarian = DB::table('antrians')
                ->select(DB::raw('DATE(created_at) as tanggal, COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get();

            $kinerjaDepartemen = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->select('departemens.nama_departemen', DB::raw('COUNT(*) as total_antrian, SUM(CASE WHEN antrians.status_antrian = 3 THEN 1 ELSE 0 END) as jumlah_selesai, SUM(CASE WHEN antrians.status_antrian = 4 THEN 1 ELSE 0 END) as jumlah_dilewati'))
                ->whereBetween('antrians.created_at', [$startDate, $endDate])
                ->groupBy('departemens.id', 'departemens.nama_departemen')
                ->orderBy('departemens.nama_departemen')
                ->get();

            return response()->json([
                'kpi' => [
                    'total_antrian' => (int)$kpi->total_antrian,
                    'jumlah_selesai' => (int)$kpi->jumlah_selesai,
                    'jumlah_dilewati' => (int)$kpi->jumlah_dilewati
                ],
                'grafik_harian' => [
                    'labels' => $grafikHarian->pluck('tanggal'),
                    'data' => $grafikHarian->pluck('total')
                ],
                'kinerja_departemen' => $kinerjaDepartemen
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPerformanceStats: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // [PERBAIKAN] Fungsi ini diubah total untuk menggunakan NIK, sesuai logika yang sudah dibangun sebelumnya.
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik'          => 'required|digits:16',
            'id_pelayanan' => 'required|integer|exists:pelayanans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Input tidak valid.', 'errors' => $validator->errors()], 400);
        }

        try {
            $nik = $request->input('nik');
            $idPelayanan = $request->input('id_pelayanan');

            // Mencari antrian berdasarkan NIK & layanan untuk hari ini dengan status aktif (1 atau 2).
            $antrian = Antrian::where('id_pelayanan', $idPelayanan)
                ->whereDate('created_at', now()->toDateString())
                ->whereIn('status_antrian', [1, 2]) // Hanya cek yang statusnya Menunggu atau Dipanggil
                ->whereHas('pengunjung', function ($query) use ($nik) {
                    $query->where('nik', $nik);
                })
                ->select('id', 'id_pelayanan', 'status_antrian as status')
                ->first();

            if ($antrian) {
                return response()->json(['status' => true, 'message' => 'Tiket aktif ditemukan.', 'data' => $antrian], 200);
            } else {
                // [KONSISTENSI] Kode 200 karena query berhasil, hanya data tidak ditemukan.
                return response()->json(['status' => false, 'message' => 'Tidak ada tiket aktif untuk NIK ini di layanan terkait.', 'data' => null], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error saat memeriksa antrian dengan NIK: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan pada server saat memeriksa tiket.'], 500);
        }
    }

    public function searchByHp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_hp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Nomor HP wajib diisi.'], 400);
        }

        try {
            $noHp = $request->input('no_hp');

            // [PERBAIKAN] Pencarian juga harus dibatasi pada tiket yang masih relevan (belum selesai/skip)
            $antrian = Antrian::whereDate('created_at', now()->toDateString())
                ->whereIn('status_antrian', [1, 2]) // Hanya tiket yang Menunggu atau Dipanggil
                ->whereHas('pengunjung', function ($query) use ($noHp) {
                    $query->where('no_hp', $noHp);
                })
                ->latest()
                ->first();

            if ($antrian) {
                return response()->json(['status' => true, 'message' => 'Tiket aktif ditemukan.', 'data' => ['uuid' => $antrian->uuid]], 200);
            }

            return response()->json(['status' => false, 'message' => 'Tidak ada tiket aktif yang ditemukan untuk nomor HP ini hari ini.'], 404);

        } catch (\Exception $e) {
            Log::error('Gagal mencari antrian by HP: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}