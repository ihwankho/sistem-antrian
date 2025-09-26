<?php

namespace App\Http\Controllers;

use App\Models\Antrian; // <--- TAMBAHKAN BARIS INI
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use thiagoalessio\TesseractOCR\TesseractOCR;



class AntrianController extends Controller
{
    private $apiAntrianUrl;
    private $apiPelayananUrl;
    private $apiBaseUrl;

    public function __construct()
    {
        // Pastikan API_BASE_URL di file .env Anda diakhiri dengan /api
        // Contoh: API_BASE_URL=http://127.0.0.1:8001/api
        $this->apiBaseUrl = rtrim(env('API_BASE_URL', 'http://127.0.0.1:8001/api'), '/');
        $this->apiAntrianUrl = $this->apiBaseUrl . '/antrian';
        $this->apiPelayananUrl = $this->apiBaseUrl . '/pelayanan';
    }

    /**
     * Menampilkan halaman untuk memilih layanan.
     */
    public function pilihLayanan()
    {
        $pelayananGrouped = [];

        try {
            $response = Http::get($this->apiPelayananUrl);

            if ($response->successful() && ($response->json('status') === true)) {
                $pelayananList = $response->json('data');

                // Kelompokkan berdasarkan nama departemen
                foreach ($pelayananList as $layanan) {
                    $departemen = $layanan['departemen']['nama_departemen'] ?? 'Layanan Lainnya';
                    $pelayananGrouped[$departemen][] = $layanan;
                }
            } else {
                Log::error('Gagal mengambil data layanan dari API', ['response' => $response->body()]);
                return back()->with('error', 'Gagal mengambil data layanan dari server.');
            }
        } catch (\Exception $e) {
            Log::error('Tidak dapat terhubung ke API layanan', ['error' => $e->getMessage()]);
            return back()->with('error', 'Tidak dapat terhubung ke server layanan saat ini.');
        }

        return view('antrian.pilih-layanan', compact('pelayananGrouped'));
    }

    /**
     * Menampilkan form pengisian data berdasarkan layanan yang dipilih.
     */
    public function isiData(Request $request)
    {
        // Jika request pakai id_departemen, ambil layanan pertama
        if ($request->has('id_departemen')) {
            try {
                $response = Http::get($this->apiPelayananUrl);
                if ($response->successful() && $response->json('status') === true) {
                    $pelayananList = $response->json('data');
                    $layanan = collect($pelayananList)
                        ->firstWhere('departemen.id', (int) $request->id_departemen);

                    if ($layanan) {
                        $request->merge(['id_pelayanan' => $layanan['id']]);
                    } else {
                        return redirect()->route('antrian.pilih-layanan')->with('error', 'Tidak ada layanan di departemen ini.');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
            }
        }

        // Validasi id_pelayanan
        $request->validate([
            'id_pelayanan' => 'required|numeric'
        ]);

        try {
            $response = Http::get("{$this->apiPelayananUrl}/{$request->id_pelayanan}");
            if ($response->successful() && $response->json('status') === true) {
                $layanan = $response->json('data');
                return view('antrian.isi-data', compact('layanan'));
            }

            return redirect()->route('antrian.pilih-layanan')->with('error', 'Layanan tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
        }
    }

    /**
     * Mengirim data ke API untuk membuat tiket antrian.
     */
    private function extractNikFromOcr(string $ocrText): ?string
    {
        // Regex ini mencari blok 16 digit angka yang berdiri sendiri
        preg_match('/\b(\d{16})\b/', $ocrText, $matches);

        // Jika ditemukan, kembalikan NIK (hasil tangkapan pertama)
        return $matches[1] ?? null;
    }

    /**
     * Membuat tiket antrian dengan validasi dan pengecekan duplikasi berdasarkan NIK.
     */
    public function buatTiket(Request $request)
    {
        // [UBAH] Menambahkan validasi untuk NIK (wajib 16 digit angka)
        $validator = Validator::make($request->all(), [
            'nik'             => 'required|digits:16',
            'nama_pengunjung' => 'required|string|max:255',
            'no_hp'           => 'required|string|max:15',
            'jenis_kelamin'   => 'required|string',
            'alamat'          => 'required|string',
            'id_pelayanan'    => 'required|numeric',
            'foto_ktp'        => 'required|image|max:2048',
            'foto_wajah'      => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // --- Langkah 1: Cek tiket aktif berdasarkan NIK dan layanan ---
        try {
            // [UBAH] Mengirim NIK untuk pengecekan
            $response = Http::get($this->apiAntrianUrl . '/check', [
                'nik' => $request->input('nik'),
                'id_pelayanan' => $request->input('id_pelayanan'),
            ]);

            if ($response->successful() && $response->json('status') === true) {
                $existingTicket = $response->json('data');
                $existingTicketStatus = $existingTicket['status'];

                if ($existingTicketStatus == 1 || $existingTicketStatus == 2) {
                    // [UBAH] Pesan error disesuaikan untuk NIK
                    return back()->withErrors(['nik' => 'NIK Anda sudah memiliki tiket antrian yang masih aktif untuk layanan ini, anda bisa cek tiket anda di Cari Tiket.'])->withInput();
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API saat pengecekan tiket', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal terhubung ke server untuk verifikasi tiket. Silakan coba lagi.')->withInput();
        }

        // --- Langkah 2: Proses OCR untuk Validasi NIK ---
        try {
            $ktpPath = $request->file('foto_ktp')->getRealPath();
            $ocrText = (new TesseractOCR($ktpPath))->lang('ind')->run();
            // [UBAH] Memanggil fungsi untuk ekstraksi NIK
            $nikDiKTP = $this->extractNikFromOcr($ocrText);

            if (!$nikDiKTP) {
                // [UBAH] Pesan error disesuaikan untuk NIK
                return back()->withErrors(['nik' => 'NIK tidak dapat terdeteksi pada gambar KTP. Pastikan gambar jelas.'])->withInput();
            }

            // [UBAH] Membandingkan NIK dari input dengan NIK dari KTP
            if (trim($nikDiKTP) !== trim($request->input('nik'))) {
                 return back()->withErrors(['nik' => 'NIK yang dimasukkan tidak sesuai dengan NIK pada KTP. (Terdeteksi: ' . $nikDiKTP . ')'])->withInput();
            }

        } catch (\Exception $e) {
            Log::error('Tesseract OCR Gagal', ['error' => $e->getMessage()]);
            return back()->withErrors(['foto_ktp' => 'Gagal memproses gambar KTP. Silakan coba lagi.'])->withInput();
        }

        // --- Langkah 3: Jika semua validasi lolos, buat tiket baru ---
        try {
            $validatedData = $validator->validated();
            $postData = $validatedData;
            // [HAPUS] Baris "$postData['nik'] = 0;" dihapus karena NIK sudah valid dari input
            
            $http = Http::asMultipart();
            $fotoKtp = $request->file('foto_ktp');
            $http->attach('foto_ktp', file_get_contents($fotoKtp->getRealPath()), $fotoKtp->getClientOriginalName());
            $fotoWajah = $request->file('foto_wajah');
            $http->attach('foto_wajah', file_get_contents($fotoWajah->getRealPath()), $fotoWajah->getClientOriginalName());

            $response = $http->post($this->apiAntrianUrl, $postData);

            if ($response->successful() && $response->json('status') === true) {
                $tiketData = $response->json('data');
                return redirect()->route('antrian.tiket', ['uuid' => $tiketData['uuid']]);
            }

            Log::error('API mengembalikan error saat membuat tiket', ['response' => $response->body()]);
            return back()->withErrors($response->json('errors') ?? ['Terjadi kesalahan dari server API'])->withInput();

        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API saat buat tiket', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal terhubung ke server. Silakan coba lagi nanti.')->withInput();
        }
    }

    
/**
 * Menampilkan halaman tiket yang didapatkan oleh pengunjung.
 */
public function tampilTiket($uuid) // Parameter adalah $uuid
{
    try {
        // === PERBAIKAN DI SINI ===
        // Panggil API endpoint publik dengan variabel $uuid yang diterima
        $response = Http::get($this->apiBaseUrl . '/tiket/' . $uuid);
        
        if ($response->successful()) {
            $tiket = $response->json();
            return view('antrian.tiket', compact('tiket'));
        }
        
        return view('antrian.tiket', ['tiket' => null]);

    } catch (\Exception $e) {
        Log::error('Gagal terhubung ke API saat menampilkan tiket pengunjung', [
            'uuid' => $uuid, // Log variabel yang benar
            'error' => $e->getMessage()
        ]);
        return view('antrian.tiket', ['tiket' => null]);
    }
}

    /**
     * Menampilkan halaman detail data diri berdasarkan ID tiket.
     * Fungsi ini dipanggil ketika QR Code di-scan.
     */
    public function detailTiket($uuid) // <-- Menerima $uuid, bukan $id
    {
        try {
            // Panggil API endpoint BARU yang menggunakan UUID
            $response = Http::get($this->apiBaseUrl . '/antrian/detail/' . $uuid);
            
            if ($response->successful() && !empty($response->json())) {
                $antrianDetail = $response->json();
                return view('antrian.detail-tiket', ['tiket' => $antrianDetail]);
            }
            
            return view('antrian.detail-tiket', ['tiket' => null]);

        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API saat detail tiket', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            return view('antrian.detail-tiket', ['tiket' => null]);
        }
    }
    public function cariTiketJson(Request $request)
    {
        $validated = $request->validate(['nik' => 'required|digits:16']);
    
        try {
            $antrians = Antrian::with(['pelayanan.departemen.loket'])
                ->whereDate('created_at', now()->toDateString())
                ->whereIn('status_antrian', [1, 2])
                ->whereHas('pengunjung', function ($query) use ($validated) {
                    $query->where('nik', $validated['nik']);
                })
                ->get();
    
            if ($antrians->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada tiket aktif yang ditemukan untuk NIK Anda hari ini.'], 404);
            }
    
            // Ambil data loket sekali saja untuk efisiensi
            $all_lokets = Loket::orderBy('id', 'asc')->pluck('id')->toArray();
    
            // Format data tiket agar mudah digunakan oleh JavaScript
            $formattedTickets = $antrians->map(function ($antrian) use ($all_lokets) {
                $loket_id = $antrian->pelayanan->departemen->id_loket;
                $loket_index = array_search($loket_id, $all_lokets);
                $kode_huruf = chr(65 + $loket_index);
                $nomor_lengkap = $kode_huruf . '-' . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
    
                return [
                    'uuid' => $antrian->uuid,
                    'nomor_lengkap' => $nomor_lengkap,
                    'nama_layanan' => $antrian->pelayanan->nama_layanan,
                    'nama_loket' => $antrian->pelayanan->departemen->loket->nama_loket,
                    'url' => route('antrian.tiket.detail', ['uuid' => $antrian->uuid]) // URL untuk di-klik
                ];
            });
    
            return response()->json(['success' => true, 'tickets' => $formattedTickets]);
    
        } catch (\Exception $e) {
            \Log::error('API Cari Tiket Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}