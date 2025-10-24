<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AntrianController extends Controller
{
    private $apiBaseUrl;
    private $apiAntrianUrl;
    private $apiPelayananUrl;

    /**
     * Menginisialisasi URL API dari file konfigurasi layanan.
     */
    public function __construct()
    {
        // --- PERUBAHAN DI SINI: Ambil dari config('services.api.base_url') ---
        // Nilai default 'http://127.0.0.1:8001/api' di sini tidak lagi diperlukan
        // karena sudah didefinisikan sebagai default di config/services.php
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
        // --------------------------------------------------------------------

        $this->apiAntrianUrl = $this->apiBaseUrl . '/antrian';
        $this->apiPelayananUrl = $this->apiBaseUrl . '/pelayanan';
    }

    public function pilihLayanan()
    {
        try {
            $response = Http::get($this->apiPelayananUrl);

            if ($response->successful() && $response->json('status') === true) {
                $pelayananList = $response->json('data');
                $pelayananGrouped = [];
                foreach ($pelayananList as $layanan) {
                    $departemen = $layanan['departemen']['nama_departemen'] ?? 'Layanan Lainnya';
                    $pelayananGrouped[$departemen][] = $layanan;
                }
                return view('antrian.pilih-layanan', compact('pelayananGrouped'));
            } else {
                Log::error('Gagal mengambil data layanan dari API', ['response' => $response->body()]);
                return back()->with('error', 'Gagal mengambil data layanan dari server.');
            }
        } catch (\Exception $e) {
            Log::error('Tidak dapat terhubung ke API layanan', ['error' => $e->getMessage()]);
            return back()->with('error', 'Tidak dapat terhubung ke server layanan saat ini.');
        }
    }

    public function isiData(Request $request)
    {
        if ($request->has('id_departemen')) {
            try {
                $response = Http::get($this->apiPelayananUrl);
                if ($response->successful() && $response->json('status') === true) {
                    $pelayananList = $response->json('data');
                    $layanan = collect($pelayananList)
                        ->firstWhere('departemen.id', (int) $request->id_departemen);

                    if ($layanan) {
                        // Langsung lanjutkan ke validasi dengan id_pelayanan yang ditemukan
                        $request->merge(['id_pelayanan' => $layanan['id']]);
                    } else {
                        return redirect()->route('antrian.pilih-layanan')->with('error', 'Tidak ada layanan di departemen ini.');
                    }
                } else {
                     return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal memuat daftar layanan dari API.');
                }
            } catch (\Exception $e) {
                return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
            }
        }

        $request->validate(['id_pelayanan' => 'required|numeric']);

        try {
            $response = Http::get("{$this->apiPelayananUrl}/{$request->id_pelayanan}");
            if ($response->successful() && $response->json('status') === true) {
                $layanan = $response->json('data');
                return view('antrian.isi-data', compact('layanan'));
            }
            return redirect()->route('antrian.pilih-layanan')->with('error', 'Layanan yang dipilih tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
        }
    }

    public function buatTiket(Request $request)
{
    $request->merge([
        'nama_pengunjung' => strip_tags($request->input('nama_pengunjung')),
        'alamat'          => $request->filled('alamat') ? strip_tags($request->input('alamat')) : null,
        'no_hp'           => strip_tags($request->input('no_hp')),
    ]);

    // ✅ Validasi dasar (tanpa image|mimes)
    $validator = Validator::make($request->all(), [
        'nama_pengunjung' => 'required|string|max:255',
        'no_hp'           => 'required|string|regex:/^[0-9]{10,13}$/',
        'jenis_kelamin'   => 'nullable|string',
        'alamat'          => 'nullable|string',
        'id_pelayanan'    => 'required|numeric',
        'foto_wajah'      => 'required|file|max:2048',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // ✅ Validasi manual file gambar
    if ($request->hasFile('foto_wajah')) {
        $foto = $request->file('foto_wajah');
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $ext = strtolower($foto->getClientOriginalExtension());

        if (!in_array($ext, $allowedExt)) {
            return back()->withErrors(['foto_wajah' => 'Ekstensi file tidak diizinkan. Hanya JPG atau PNG.'])->withInput();
        }

        if (!@getimagesize($foto->getRealPath())) {
            return back()->withErrors(['foto_wajah' => 'File bukan gambar yang valid.'])->withInput();
        }
    }

    try {
        // ✅ Cek tiket aktif via API
        $checkResponse = Http::get($this->apiAntrianUrl . '/check', [
            'no_hp' => $request->input('no_hp'),
            'id_pelayanan' => $request->input('id_pelayanan'),
        ]);

        if ($checkResponse->successful() && $checkResponse->json('status') === true) {
            return back()->withErrors(['no_hp' => 'Nomor HP Anda sudah memiliki tiket aktif untuk layanan ini.'])->withInput();
        }
    } catch (\Exception $e) {
        Log::error('Gagal terhubung ke API saat pengecekan tiket', ['error' => $e->getMessage()]);
        return back()->with('error', 'Gagal terhubung ke server untuk verifikasi tiket. Silakan coba lagi.')->withInput();
    }

    try {
        $validatedData = $validator->validated();
        $http = Http::asMultipart();

        if ($request->hasFile('foto_wajah')) {
            $fotoWajah = $request->file('foto_wajah');
            // ✅ attach file ke API
            $http->attach('foto_wajah', file_get_contents($fotoWajah->getRealPath()), $fotoWajah->getClientOriginalName());
        }

        $response = $http->post($this->apiAntrianUrl, Arr::except($validatedData, ['foto_wajah']));

        if ($response->successful() && $response->json('status') === true) {
            $tiketData = $response->json('data');
            return redirect()->route('antrian.tiket', ['uuid' => $tiketData['uuid']]);
        }

        Log::error('API mengembalikan error saat membuat tiket', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return back()->with('error', $response->json('message') ?? 'Terjadi kesalahan dari server API. Silakan coba lagi.')->withInput();

    } catch (\Exception $e) {
        Log::error('Gagal terhubung ke API saat buat tiket', ['error' => $e->getMessage()]);
        return back()->with('error', 'Gagal terhubung ke server. Silakan coba lagi nanti.')->withInput();
    }
}


    public function tampilTiket(string $uuid)
    {
        if (!Str::isUuid($uuid)) {
            abort(404);
        }

        // [DIUBAH] Mengambil data langsung dari database, bukan via HTTP call
        $tiket = Antrian::with(['pelayanan.departemen.loket'])
                        ->where('uuid', $uuid)
                        ->first();

        if ($tiket) {
            // Ubah ke array agar konsisten dengan view yang sudah ada
            $tiketArray = $tiket->toArray();

            // Panggil helper untuk memformat nomor antrian
            $tiketData = $this->_formatTiketData($tiketArray);

            return view('antrian.tiket', ['tiket' => $tiketData]);
        }

        return view('antrian.tiket', ['tiket' => null]);
    }

    public function detailTiket(string $uuid)
    {
        if (!Str::isUuid($uuid)) {
            abort(404);
        }

        // [DIUBAH] Mengambil data langsung dari database, bukan via HTTP call
        $tiket = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])
                        ->where('uuid', $uuid)
                        ->first();

        if ($tiket) {
            // Ubah ke array agar konsisten dengan view yang sudah ada
            $tiketArray = $tiket->toArray();

            // Panggil helper untuk memformat nomor antrian
            $tiketData = $this->_formatTiketData($tiketArray);

            return view('antrian.detail-tiket', ['tiket' => $tiketData]);
        }

        return view('antrian.detail-tiket', ['tiket' => null]);
    }



    public function cariTiketJson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_hp' => ['required', 'string', 'regex:/^[0-9]+$/']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Format Nomor HP tidak valid.'], 400);
        }

        try {
            // Logika ini seharusnya memanggil API, bukan database lokal, agar konsisten
            // Untuk saat ini, kita biarkan sesuai kode asli Anda
            $antrians = Antrian::with(['pelayanan.departemen.loket', 'pengunjung'])
                ->whereDate('created_at', now()->toDateString())
                ->whereIn('status_antrian', [1, 2])
                ->whereHas('pengunjung', function ($query) use ($request) {
                    $query->where('no_hp', $request->no_hp);
                })
                ->get();

            if ($antrians->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada tiket aktif yang ditemukan untuk Nomor HP Anda hari ini.'], 404);
            }

            $all_lokets = Loket::orderBy('id', 'asc')->pluck('id')->toArray();

            $formattedTickets = $antrians->map(function ($antrian) use ($all_lokets) {
                $loket_id = $antrian->pelayanan->departemen->id_loket;
                $loket_index = array_search($loket_id, $all_lokets);
                $kode_huruf = ($loket_index !== false) ? chr(65 + $loket_index) : '?';
                $nomor_lengkap = $kode_huruf . '-' . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);

                return [
                    'uuid' => $antrian->uuid,
                    'nomor_lengkap' => $nomor_lengkap,
                    'nama_layanan' => $antrian->pelayanan->nama_layanan,
                    'nama_loket' => $antrian->pelayanan->departemen->loket->nama_loket,
                    'url' => route('antrian.tiket', ['uuid' => $antrian->uuid])
                ];
            });

            return response()->json(['success' => true, 'tickets' => $formattedTickets]);

        } catch (\Exception $e) {
            Log::error('API Cari Tiket Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    /**
     * [BARU] Helper method privat untuk memformat data tiket.
     * Menambahkan 'nomor_antrian_lengkap' ke dalam array tiket.
     */
    private function _formatTiketData(array $tiket): array
    {
        if (empty($tiket['pelayanan'])) {
            $tiket['nomor_antrian_lengkap'] = $tiket['nomor_antrian'] ?? 'N/A';
            return $tiket;
        }

        $allLokets = Loket::orderBy('id', 'ASC')->pluck('id')->toArray();
        $idLoket = $tiket['pelayanan']['departemen']['loket']['id'] ?? null;

        $loketIndex = $idLoket ? array_search($idLoket, $allLokets) : false;
        $kodeHuruf = ($loketIndex !== false) ? chr(65 + $loketIndex) : '?';

        $tiket['nomor_antrian_lengkap'] = $kodeHuruf . str_pad($tiket['nomor_antrian'], 3, '0', STR_PAD_LEFT);

        return $tiket;
    }
}