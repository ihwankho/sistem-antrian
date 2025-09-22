<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'id_pelayanan' => 'required|numeric'
        ]);

        try {
            $response = Http::get("{$this->apiPelayananUrl}/{$request->id_pelayanan}");

            if ($response->successful() && ($response->json('status') === true)) {
                $layanan = $response->json('data');
                return view('antrian.isi-data', compact('layanan'));
            }

            return redirect()->route('antrian.pilih-layanan')->with('error', 'Layanan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API saat isi data', ['error' => $e->getMessage()]);
            return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
        }
    }

    /**
     * Mengirim data ke API untuk membuat tiket antrian.
     */
    public function buatTiket(Request $request)
    {
        // 1. Validasi diubah: 'nik' tidak lagi 'required'
        $validatedData = $request->validate([
            'nama_pengunjung' => 'required|string|max:255',
            'no_hp'           => 'required|string|max:15', // Sebaiknya no_hp juga required
            'jenis_kelamin'   => 'required|string',
            'alamat'          => 'required|string',
            'id_pelayanan'    => 'required|numeric',
            'foto_ktp'        => 'nullable|image|max:2048',
            'foto_wajah'      => 'nullable|image|max:2048',
        ]);

        try {
            // Siapkan data yang akan dikirim
            $postData = $validatedData;
            
            // 2. Tambahkan NIK secara manual dengan nilai 0
            $postData['nik'] = 0;

            // Memulai request sebagai multipart/form-data
            $http = Http::asMultipart();

            // Lampirkan file foto KTP jika ada
            if ($request->hasFile('foto_ktp')) {
                $fotoKtp = $request->file('foto_ktp');
                $http->attach(
                    'foto_ktp', // nama field di API
                    file_get_contents($fotoKtp->getRealPath()),
                    $fotoKtp->getClientOriginalName()
                );
            }

            // Lampirkan file foto Wajah jika ada
            if ($request->hasFile('foto_wajah')) {
                $fotoWajah = $request->file('foto_wajah');
                $http->attach(
                    'foto_wajah', // nama field di API
                    file_get_contents($fotoWajah->getRealPath()),
                    $fotoWajah->getClientOriginalName()
                );
            }

            // 3. Kirim data (yang sudah berisi nik: 0) ke API
            $response = $http->post($this->apiAntrianUrl, $postData);

            // Cek respons dari API
            if ($response->successful() && $response->json('status') === true) {
                $tiketData = $response->json('data');
                // Redirect ke halaman tiket dengan membawa data dari API
                return redirect()->route('antrian.tiket')->with('tiket', $tiketData);
            }
            
            // Jika API mengembalikan error
            Log::error('API mengembalikan error saat membuat tiket', ['response' => $response->body()]);
            return back()->withErrors($response->json('errors') ?? ['Terjadi kesalahan dari server API'])->withInput();

        } catch (\Exception $e) {
            // Jika koneksi ke API gagal
            Log::error('Gagal terhubung ke API saat buat tiket', ['error' => 'Pesan: ' . $e->getMessage(), 'Baris: ' . $e->getLine()]);
            return back()->with('error', 'Gagal terhubung ke server. Silakan coba lagi nanti.')->withInput();
        }
    }
    
    /**
     * Menampilkan halaman tiket yang sudah dibuat.
     */
    public function tampilTiket()
    {
        $tiket = session('tiket');
    
        if (!$tiket) {
            return redirect()->route('landing.page');
        }

        return view('antrian.tiket', compact('tiket'));
    }

    /**
     * Menampilkan halaman detail data diri berdasarkan ID tiket.
     * Fungsi ini dipanggil ketika QR Code di-scan.
     */
    public function detailTiket($id)
    {
        try {
            // Panggilan ini sekarang sudah benar karena rute API telah diperbaiki di routes/api.php
            $response = Http::get($this->apiAntrianUrl . '/show/' . $id);
            
            if ($response->successful() && !empty($response->json())) {
                $antrianDetail = $response->json();
                return view('antrian.detail-tiket', ['tiket' => $antrianDetail]);
            }
            
            // Fallback jika API gagal atau tiket tidak ditemukan
            Log::warning('Gagal mencari detail tiket via API', ['id' => $id, 'response' => $response->body()]);
            return view('antrian.detail-tiket', ['tiket' => null]);

        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API saat detail tiket', ['id' => $id, 'error' => $e->getMessage()]);
            return view('antrian.detail-tiket', ['tiket' => null]);
        }
    }
}