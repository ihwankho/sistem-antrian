<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loket;
use App\Models\Antrian;
use App\Models\DisplaySetting; // <-- TAMBAHAN
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; // <-- TAMBAHAN

class DisplayController extends Controller
{
    /**
     * Menampilkan halaman display antrian.
     */
    public function index()
    {
        // View ini sekarang akan memanggil API '/display/active-settings'
        // dan '/display/queue-data' menggunakan JavaScript
        return view('display.Display');
    }

    /**
     * [OPTIMASI] API untuk mengambil data antrian semua loket dalam beberapa kueri efisien.
     */
    public function getQueueData()
    {
        try {
            $today = Carbon::today();
            $lokets = Loket::orderBy('id', 'ASC')->get();
            $loketMap = $this->_getLoketMap($lokets);

            // 1. Ambil semua antrian yang sedang dipanggil (status 2) hari ini dalam satu kueri
            $allCurrentCalling = Antrian::with('pelayanan.departemen')
                ->whereDate('created_at', $today)
                ->where('status_antrian', 2)
                ->get()
                ->keyBy('pelayanan.departemen.id_loket');

            // 2. Ambil 10 antrian berikutnya (status 1) untuk semua loket dalam satu kueri
            $subQuery = Antrian::select('id', 'nomor_antrian', 'id_pelayanan', DB::raw('ROW_NUMBER() OVER (PARTITION BY id_pelayanan ORDER BY nomor_antrian ASC) as rn'))
                ->where('status_antrian', 1)
                ->whereDate('created_at', $today);

            $allNextQueues = DB::table(DB::raw("({$subQuery->toSql()}) as ranked_antrians"))
                ->mergeBindings($subQuery->getQuery())
                ->join('pelayanans', 'ranked_antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('rn', '<=', 10)
                ->select('ranked_antrians.nomor_antrian', 'departemens.id_loket')
                ->orderBy('departemens.id_loket')
                ->orderBy('rn')
                ->get()
                ->groupBy('id_loket');

            $loketData = [];
            foreach ($lokets as $loket) {
                $kodeHuruf = $loketMap[$loket->id];
                $currentAntrian = $allCurrentCalling->get($loket->id);

                $currentCallingFormatted = null;
                $voiceText = null;

                if ($currentAntrian) {
                    $currentCallingFormatted = $kodeHuruf . str_pad($currentAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
                    $kodeAntrianSpasi = implode(' ', str_split($currentCallingFormatted));
                    $voiceText = "Nomor antrian $kodeAntrianSpasi, silakan menuju ke, loket, {$loket->nama_loket}";
                }

                $nextQueuesForLoket = $allNextQueues->get($loket->id, collect())->map(function ($antrian) use ($kodeHuruf) {
                    return $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
                });

                $loketData[] = [
                    'id' => $loket->id,
                    'nama_loket' => $loket->nama_loket,
                    'current_calling' => $currentCallingFormatted,
                    'next_queues' => $nextQueuesForLoket->toArray(),
                    'waiting_count' => $nextQueuesForLoket->count(),
                    'voice_text' => $voiceText,
                    'call_timestamp' => $currentAntrian ? $currentAntrian->updated_at->timestamp : null,
                ];
            }

            $stats = $this->getQueueStats($today);

            return response()->json([
                'status' => true,
                'data' => [
                    'lokets' => $loketData,
                    'stats' => $stats,
                    'last_updated' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("DisplayController@getQueueData: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * [OPTIMASI] Mengambil statistik antrian keseluruhan dalam satu kueri.
     * * @param \Carbon\Carbon $date Tanggal yang akan dianalisis.
     * @return array
     */
    private function getQueueStats($date)
    {
        $stats = Antrian::whereDate('created_at', $date)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_antrian = 1 THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status_antrian = 2 THEN 1 ELSE 0 END) as calling,
                SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as skipped
            ")
            ->first();

        return $stats ? $stats->toArray() : ['total' => 0, 'waiting' => 0, 'calling' => 0, 'completed' => 0, 'skipped' => 0];
    }

    /**
     * [OPTIMASI] API untuk mendapatkan antrian yang sedang dipanggil saja dalam satu kueri.
     */
    public function getCurrentCallingOnly()
    {
        try {
            $today = Carbon::today();
            $lokets = Loket::orderBy('id', 'ASC')->get();
            $loketMap = $this->_getLoketMap($lokets);

            $callingData = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->whereDate('antrians.created_at', $today)
                ->where('antrians.status_antrian', 2)
                ->select('antrians.nomor_antrian', 'departemens.id_loket')
                ->get()
                ->keyBy('id_loket');

            $currentCallings = [];
            foreach ($lokets as $loket) {
                $calling = $callingData->get($loket->id);
                $kodeHuruf = $loketMap[$loket->id];

                $currentCallings[] = [
                    'loket' => $loket->nama_loket,
                    'kode_antrian' => $calling
                        ? $kodeHuruf . str_pad($calling->nomor_antrian, 3, '0', STR_PAD_LEFT)
                        : null
                ];
            }

            return response()->json(['status' => true, 'data' => $currentCallings]);

        } catch (\Exception $e) {
            Log::error("DisplayController@getCurrentCallingOnly: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * API untuk mendapatkan detail antrian berdasarkan loket.
     * * @param int $loketId ID loket.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueueByLoket($loketId)
    {
        try {
            $today = Carbon::today();
            $loket = Loket::findOrFail($loketId);
            $allLokets = Loket::orderBy('id', 'ASC')->get();
            $loketMap = $this->_getLoketMap($allLokets);
            $kodeHuruf = $loketMap[$loket->id] ?? '?';

            $antrians = Antrian::with(['pengunjung:id,nama_pengunjung', 'pelayanan:id,nama_pelayanan'])
                ->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId))
                ->whereDate('created_at', $today)
                ->whereIn('status_antrian', [1, 2, 3, 4])
                ->orderBy('nomor_antrian', 'asc')
                ->get()
                ->map(function ($antrian) use ($kodeHuruf) {
                    return [
                        'id' => $antrian->id,
                        'kode_antrian' => $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT),
                        'nomor_antrian' => $antrian->nomor_antrian,
                        'nama_pengunjung' => $antrian->pengunjung->nama_pengunjung ?? 'N/A',
                        'nama_pelayanan' => $antrian->pelayanan->nama_pelayanan ?? 'N/A',
                        'status_antrian' => $antrian->status_antrian,
                        'status_text' => $this->getStatusText($antrian->status_antrian),
                        'waktu_daftar' => $antrian->created_at->format('H:i:s')
                    ];
                });

            return response()->json([
                'status' => true,
                'data' => [
                    'loket' => [
                        'id' => $loket->id,
                        'nama_loket' => $loket->nama_loket,
                        'kode_huruf' => $kodeHuruf
                    ],
                    'antrians' => $antrians
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Loket tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error("DisplayController@getQueueByLoket: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * [OPTIMASI] API untuk mendapatkan ringkasan harian dalam satu kueri.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailySummary(Request $request)
    {
        try {
            $date = $request->input('date', Carbon::today()->toDateString());
            $targetDate = Carbon::parse($date);

            $lokets = Loket::orderBy('id', 'ASC')->get();
            $loketMap = $this->_getLoketMap($lokets);

            $summaryData = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->whereDate('antrians.created_at', $targetDate)
                ->select(
                    'departemens.id_loket',
                    DB::raw('COUNT(*) as total_antrian'),
                    DB::raw('SUM(CASE WHEN antrians.status_antrian = 3 THEN 1 ELSE 0 END) as selesai'),
                    DB::raw('SUM(CASE WHEN antrians.status_antrian = 4 THEN 1 ELSE 0 END) as dilewati'),
                    // Rata-rata waktu pelayanan dalam menit
                    DB::raw('AVG(CASE WHEN antrians.status_antrian = 3 THEN TIMESTAMPDIFF(MINUTE, antrians.created_at, antrians.updated_at) ELSE NULL END) as rata_rata_waktu')
                )
                ->groupBy('departemens.id_loket')
                ->get()
                ->keyBy('id_loket');

            $summary = [];
            foreach ($lokets as $loket) {
                $data = $summaryData->get($loket->id);
                $total = $data->total_antrian ?? 0;
                $selesai = $data->selesai ?? 0;
                $dilewati = $data->dilewati ?? 0;

                $summary[] = [
                    'loket' => $loket->nama_loket,
                    'kode_huruf' => $loketMap[$loket->id],
                    'total_antrian' => (int)$total,
                    'selesai' => (int)$selesai,
                    'dilewati' => (int)$dilewati,
                    'pending' => $total - $selesai - $dilewati,
                    'efisiensi' => $total > 0 ? round(($selesai / $total) * 100, 1) : 0,
                    'rata_rata_waktu' => $data && $data->rata_rata_waktu ? round($data->rata_rata_waktu, 1) : 0
                ];
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'date' => $targetDate->toDateString(),
                    'date_formatted' => $targetDate->translatedFormat('d F Y'),
                    'summary' => $summary,
                    'grand_total' => [
                        'total_antrian' => array_sum(array_column($summary, 'total_antrian')),
                        'selesai' => array_sum(array_column($summary, 'selesai')),
                        'dilewati' => array_sum(array_column($summary, 'dilewati')),
                        'pending' => array_sum(array_column($summary, 'pending'))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("DisplayController@getDailySummary: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Endpoint untuk testing koneksi.
     * * @return \Illuminate\Http\JsonResponse
     */
    public function ping()
    {
        return response()->json([
            'status' => true,
            'message' => 'Server is running',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    
    // ===================================================================
    // ▼▼▼ FUNGSI BARU UNTUK API PUBLIC DISPLAY SETTINGS ▼▼▼
    // ===================================================================

    /**
     * [API] Mengambil setting (video & text) yang sedang aktif.
     * Ini akan dipanggil oleh JavaScript di halaman display publik.
     */
    public function getActiveSettings()
    {
        $setting = DisplaySetting::where('status', 'active')->first();

        if (!$setting) {
            // Sediakan fallback jika tidak ada setting aktif
            return response()->json([
                'video_urls' => [
                    'https://www.youtube.com/watch?v=ScMzIvxBSi4', // Default video 1
                    'https://www.youtube.com/watch?v=kJQP7kiw5Fk', // Default video 2
                ],
                'running_text' => 'SELAMAT DATANG DI LAYANAN KAMI. ATUR PENGUMUMAN DI HALAMAN ADMIN.',
                'video_ids' => ['ScMzIvxBSi4', 'kJQP7kiw5Fk'] // Sediakan video_ids fallback
            ]);
        }
        
        // Helper untuk parse video ID dari URL
        $setting->video_ids = $this->parseVideoIds($setting->video_urls);

        return response()->json($setting);
    }
    
    /**
     * Helper untuk mengekstrak Video ID dari berbagai format URL YouTube.
     */
    private function parseVideoIds($urls)
    {
        if (!is_array($urls)) {
            return [];
        }

        $ids = [];
        // Regex ini akan mencocokkan ID dari format:
        // - https://www.youtube.com/watch?v=kJQP7kiw5Fk
        // - https://youtu.be/kJQP7kiw5Fk
        // - https://www.youtube.com/embed/kJQP7kiw5Fk
        // Dan variasi lainnya
        $regex = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';

        foreach ($urls as $url) {
            if (preg_match($regex, $url, $matches)) {
                if (!empty($matches[2]) && strlen($matches[2]) == 11) {
                    $ids[] = $matches[2];
                }
            }
        }
        
        return array_unique($ids); // Hanya ID unik
    }


    // ===================================================================
    // ▼▼▼ FUNGSI BARU UNTUK CRUD ADMIN DISPLAY SETTINGS ▼▼▼
    // ===================================================================

    /**
     * [ADMIN] Menampilkan daftar semua display settings.
     */
    public function settingsIndex()
    {
        $settings = DisplaySetting::orderBy('status', 'desc')->orderBy('updated_at', 'desc')->get();
        
        // [PERUBAHAN] Path view diubah ke 'display.settings.index'
        return view('display.settings.index', compact('settings'));
    }

    /**
     * [ADMIN] Menampilkan form untuk membuat setting baru.
     */
    public function settingsCreate()
    {
        // [PERUBAHAN] Path view diubah ke 'display.settings.create'
        return view('display.settings.create');
    }

    /**
     * [ADMIN] Menyimpan setting baru ke database.
     */
    public function settingsStore(Request $request)
    {
        $data = $this->validateRequest($request);

        // Logika Penting: Jika status baru adalah 'active',
        // nonaktifkan semua setting lain terlebih dahulu.
        if ($data['status'] == 'active') {
            DisplaySetting::where('status', 'active')->update(['status' => 'inactive']);
        }

        DisplaySetting::create($data);

        return redirect()->route('display-settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * [ADMIN] Menampilkan form untuk mengedit setting.
     */
    public function settingsEdit(DisplaySetting $setting)
    {
        // [PERUBAHAN] Path view diubah ke 'display.settings.edit'
        return view('display.settings.edit', compact('setting'));
    }

    /**
     * [ADMIN] Memperbarui setting di database.
     */
    public function settingsUpdate(Request $request, DisplaySetting $setting)
    {
        $data = $this->validateRequest($request);

        // Logika Penting: Jika status diubah jadi 'active',
        // nonaktifkan semua setting lain terlebih dahulu.
        if ($data['status'] == 'active') {
            DisplaySetting::where('status', 'active')
                         ->where('id', '!=', $setting->id)
                         ->update(['status' => 'inactive']);
        }

        $setting->update($data);

        return redirect()->route('display-settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }

    /**
     * [ADMIN] Menghapus setting dari database.
     */
    public function settingsDestroy(DisplaySetting $setting)
    {
        // Jangan hapus jika sedang aktif (opsional, tapi best practice)
        if ($setting->status == 'active') {
             return redirect()->route('display-settings.index')->with('error', 'Tidak dapat menghapus pengaturan yang sedang aktif.');
        }
        
        $setting->delete();
        return redirect()->route('display-settings.index')->with('success', 'Pengaturan berhasil dihapus.');
    }

    /**
     * [ADMIN] Helper untuk validasi dan pemrosesan data request.
     */
    private function validateRequest(Request $request)
    {
        $validated = $request->validate([
            'video_urls_text' => 'required|string',
            'running_text' => 'required|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        // Ubah 'video_urls_text' (dari textarea) menjadi array JSON
        $videoUrls = explode("\n", $validated['video_urls_text']);
        $videoUrls = array_map('trim', $videoUrls); // Hapus spasi
        $videoUrls = array_filter($videoUrls); // Hapus baris kosong

        if (empty($videoUrls)) {
            throw ValidationException::withMessages([
                'video_urls_text' => 'Minimal harus ada satu URL video.',
            ]);
        }
        
        // Kembalikan data yang siap disimpan
        return [
            'video_urls' => $videoUrls,
            'running_text' => $validated['running_text'],
            'status' => $validated['status'],
        ];
    }


    // --- (Fungsi helper privat Anda yang sudah ada) ---

    /**
     * Helper method privat untuk mendapatkan teks status.
     * * @param int $status Kode status antrian.
     * @return string
     */
    private function getStatusText($status)
    {
        return [
            1 => 'Menunggu',
            2 => 'Dipanggil',
            3 => 'Selesai',
            4 => 'Dilewati',
        ][$status] ?? 'Tidak Dikenal';
    }

    /**
     * [DRY] Helper untuk memetakan ID loket ke kode huruf (A, B, C).
     * * @param \Illuminate\Support\Collection $lokets Daftar loket.
     * @return array
     */
    private function _getLoketMap($lokets)
    {
        $map = [];
        foreach ($lokets as $index => $loket) {
            $map[$loket->id] = chr(65 + $index);
        }
        return $map;
    }
}