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
    /**
     * Menambah antrian baru.
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // ✅ Validasi dasar tanpa 'image' atau 'mimes'
        $validated = $request->validate([
            'nama_pengunjung' => 'required|string|max:255',
            'no_hp'           => 'required|string|min:10|max:13',
            'jenis_kelamin'   => 'nullable|in:Laki-laki,Perempuan',
            'alamat'          => 'nullable|string',
            'id_pelayanan'    => 'required|exists:pelayanans,id',
            'foto_wajah'      => 'required|file|max:2048',
        ]);

        $fotoWajahPath = null;

        // ✅ Pemeriksaan manual file gambar
        if ($request->hasFile('foto_wajah')) {
            $foto = $request->file('foto_wajah');

            // 1️⃣ Periksa ekstensi file
            $allowedExt = ['jpg', 'jpeg', 'png'];
            $ext = strtolower($foto->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt)) {
                throw new \Exception('Ekstensi file tidak diizinkan. Hanya JPG atau PNG.');
            }

            // 2️⃣ Pastikan file benar-benar gambar
            if (!@getimagesize($foto->getRealPath())) {
                throw new \Exception('File yang diunggah bukan gambar yang valid.');
            }

            // 3️⃣ Simpan file ke public/images
            $filename = time() . '_' . $foto->getClientOriginalName();
            $foto->move(public_path('images'), $filename);

            // 4️⃣ Simpan path relatif ke database
            $fotoWajahPath = 'images/' . $filename;
        }

        // ✅ Simpan atau update data pengunjung
        $pengunjung = Pengunjung::firstOrCreate(
            ['no_hp' => $validated['no_hp']],
            [
                'nama_pengunjung' => $validated['nama_pengunjung'],
                'jenis_kelamin'   => $validated['jenis_kelamin'] ?? null,
                'alamat'          => $validated['alamat'] ?? null,
                'foto_wajah'      => $fotoWajahPath,
            ]
        );

        if (!$pengunjung->wasRecentlyCreated && $fotoWajahPath) {
            $pengunjung->foto_wajah = $fotoWajahPath;
            $pengunjung->save();
        }

        // ✅ Buat nomor antrian
        $pelayanan = Pelayanan::with('departemen.loket')->findOrFail($validated['id_pelayanan']);
        $idLoket = $pelayanan->departemen->loket->id;

        $lastQueue = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
            ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
            ->where('departemens.id_loket', $idLoket)
            ->whereDate('antrians.created_at', now()->toDateString())
            ->max('nomor_antrian');

        $nomorAntrian = $lastQueue ? $lastQueue + 1 : 1;

        $antrian = Antrian::create([
            'uuid'            => Str::uuid(),
            'nomor_antrian'   => $nomorAntrian,
            'status_antrian'  => 1,
            'id_pengunjung'   => $pengunjung->id,
            'id_pelayanan'    => $pelayanan->id,
        ]);

        // ✅ Format kode antrian
        $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
        $loketIndex = array_search($idLoket, $lokets);
        $kodeHuruf = chr(65 + $loketIndex);
        $kodeAntrian = $kodeHuruf . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT);

        DB::commit();

        // ✅ Data tiket untuk response
        $tiket = [
            'id'              => $antrian->id,
            'uuid'            => $antrian->uuid,
            'nomor_antrian'   => $kodeAntrian,
            'nama_departemen' => $pelayanan->departemen->nama_departemen ?? null,
            'nama_loket'      => $pelayanan->departemen->loket->nama_loket ?? null,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Tiket antrian berhasil dibuat',
            'data'    => $tiket,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal membuat tiket antrian: ' . $e->getMessage() . ' di baris ' . $e->getLine());
        return response()->json([
            'status'  => false,
            'message' => 'Gagal membuat tiket antrian',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Memeriksa apakah sudah ada tiket untuk no_hp dan layanan tertentu pada hari ini.
     */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_hp'          => 'required|string|min:10|max:13', // [DIPERBAIKI]
            'id_pelayanan' => 'required|integer|exists:pelayanans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Input tidak valid.', 'errors' => $validator->errors()], 400);
        }

        try {
            $noHp = $request->input('no_hp');
            $idPelayanan = $request->input('id_pelayanan');

            $antrianExists = Antrian::where('id_pelayanan', $idPelayanan)
                ->whereDate('created_at', now()->toDateString())
                ->whereHas('pengunjung', function ($query) use ($noHp) {
                    $query->where('no_hp', $noHp);
                })
                ->exists();

            if ($antrianExists) {
                return response()->json(['status' => true, 'message' => 'Tiket untuk nomor HP ini sudah terdaftar hari ini.'], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Nomor HP belum terdaftar untuk layanan ini hari ini.'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error saat memeriksa antrian dengan No HP: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan pada server saat memeriksa tiket.'], 500);
        }
    }

    /**
     * Daftar antrian berdasarkan loket.
     */
    public function getByLoket($id_loket)
    {
        try {
            $antrian = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->where('lokets.id', $id_loket)
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

            if ($antrian->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Tidak ada antrian untuk loket ini hari ini.',
                    'data' => []
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

    /**
     * Mengambil semua antrian dari semua loket untuk monitor.
     */
    public function getAllAntrian()
    {
        try {
            $completedToday = Antrian::where('status_antrian', 3)
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
                if ($totalMinutes > 0) {
                    $avgServiceTime = max(1, round($totalMinutes / $completedToday->count()));
                }
            }

            if ($avgServiceTime === 0) {
                $avgServiceTime = 7;
            }

            $data = \DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select('antrians.id', 'antrians.nomor_antrian', 'departemens.id_loket', 'lokets.nama_loket', 'antrians.status_antrian')
                ->whereDate('antrians.created_at', today())
                ->whereIn('antrians.status_antrian', [1, 2])
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

    /**
     * Menampilkan data tiket publik berdasarkan UUID.
     */
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
            return response()->json(['status' => false, 'message' => 'Tiket tidak ditemukan'], 404);
        }
    }

    /**
     * Menampilkan data detail antrian berdasarkan UUID.
     */
    public function showDetailByUuid($uuid)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            return response()->json($antrian);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data detail antrian tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Memanggil antrian berikutnya.
     */
    public function callNextAntrian(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'id_loket' => 'required|exists:lokets,id',
            ]);
            $idLoket = $validated['id_loket'];

            $isCalling = Antrian::whereHas('pelayanan.departemen', fn ($q) => $q->where('id_loket', $idLoket))
                ->where('status_antrian', 2)
                ->whereDate('created_at', today())
                ->exists();

            if ($isCalling) {
                return response()->json([
                    'status' => false,
                    'message' => 'Masih ada antrian yang sedang dipanggil. Selesaikan atau lewati antrian tersebut.'
                ], 409);
            }

            $nextAntrian = Antrian::whereHas('pelayanan.departemen', fn ($q) => $q->where('id_loket', $idLoket))
                ->where('status_antrian', 1)
                ->whereDate('created_at', today())
                ->orderBy('nomor_antrian', 'asc')
                ->first();

            if (!$nextAntrian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian lagi di loket ini.'
                ], 404);
            }

            $nextAntrian->status_antrian = 2;
            $nextAntrian->waktu_panggil = now();
            $nextAntrian->save();

            $loket = Loket::find($idLoket);
            $sortedLokets = Loket::orderBy('id')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $sortedLokets);

            if ($loketIndex === false) {
                throw new \Exception('ID Loket tidak ditemukan dalam daftar loket sistem.');
            }

            $kodeHuruf = chr(65 + $loketIndex);
            $kodeAntrian = $kodeHuruf . str_pad($nextAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
            $kodeAntrianSpasi = implode(' ', str_split($kodeAntrian));

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
            DB::rollBack();
            Log::error('Gagal memanggil antrian: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memanggil antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyelesaikan antrian.
     */
    public function finishAntrian(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'id_antrian' => 'required|exists:antrians,id',
            ]);

            $antrian = Antrian::findOrFail($validated['id_antrian']);

            if ($antrian->status_antrian != 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal, antrian ini tidak dalam status "Dipanggil".'
                ], 400);
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

    /**
     * Melewati antrian.
     */
    public function SkipAntrian(Request $request)
    {
        $request->validate([
            'id_antrian' => 'required|exists:antrians,id',
        ]);

        try {
            $antrian = DB::transaction(function () use ($request) {
                $antrian = Antrian::findOrFail($request->id_antrian);

                if ($antrian->status_antrian != 2) {
                    throw new Exception('Gagal, antrian ini tidak dalam status "Dipanggil".', 400);
                }

                $antrian->update(['status_antrian' => 4]);
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

    /**
     * Memanggil ulang antrian.
     */
    public function recallAntrian(Request $request)
    {
        $request->validate([
            'id_loket' => 'required|exists:lokets,id'
        ]);

        $idLoket = $request->id_loket;
        $today = Carbon::today();

        // 1. Cari antrian yang sedang dipanggil (status 2) di loket ini
        $activeAntrian = Antrian::whereHas('pelayanan.departemen', function ($query) use ($idLoket) {
                $query->where('id_loket', $idLoket);
            })
            ->where('status_antrian', 2)
            ->whereDate('created_at', $today)
            ->first();

        // 2. Jika antrian ditemukan
        if ($activeAntrian) {
            // 3. INI BAGIAN PENTING: Perbarui timestamp `updated_at`
            $activeAntrian->touch();

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dipanggil ulang.',
                'data' => [
                    'id' => $activeAntrian->id,
                    'nomor_antrian' => $activeAntrian->nomor_antrian
                ]
            ]);
        }

        // 4. Jika tidak ada antrian yang sedang dipanggil
        return response()->json([
            'status' => false,
            'message' => 'Tidak ada antrian aktif untuk dipanggil ulang.'
        ], 404);
    }

    /**
     * Mengambil antrian yang sedang dipanggil.
     */
    public function getAntrianDipanggil()
    {
        try {
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->get();

            $antrians = DB::table('antrians')
                ->where('antrians.status_antrian', 2)
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

    /**
     * Mengambil data antrian dan pengunjung.
     */
    public function ShowPe($id)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])->findOrFail($id);
            return response()->json($antrian);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data antrian tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mengambil laporan bulanan.
     */
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

    /**
     * Mengambil statistik performa.
     */
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

    /**
     * Mencari antrian berdasarkan nomor HP.
     */
    public function searchByHp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_hp' => 'required|string|min:10|max:13', // [DIPERBAIKI]
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Nomor HP wajib diisi dan harus 10-13 digit.'], 400);
        }

        try {
            $noHp = $request->input('no_hp');

            $antrian = Antrian::whereDate('created_at', now()->toDateString())
                ->whereIn('status_antrian', [1, 2])
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