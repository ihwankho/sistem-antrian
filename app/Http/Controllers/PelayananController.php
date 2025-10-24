<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <-- DIPERLUKAN
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Pool;
use Illuminate\Pagination\LengthAwarePaginator; // <-- DIPERLUKAN

class PelayananController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        // [KONSISTENSI] Mengambil URL API dari config/services.php
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    /**
     * [MODIFIKASI]
     * Menampilkan daftar layanan dengan paginasi manual (client-side).
     */
    public function index(Request $request)
    {
        $perPage = 10; // Tetap 10 per halaman
        $currentPage = $request->input('page', 1); // Ambil ?page= dari URL

        try {
            $token = Session::get('token');

            // 1. Ambil SEMUA data dari API (tanpa parameter paginasi)
            $response = Http::withToken($token)->get($this->apiBaseUrl . '/pelayanan');

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            $allItems = [];
            if ($response->successful()) {
                // Asumsi API mengembalikan { data: [...] } berisi SEMUA item
                $allItems = $response->json('data', []);
            } else {
                 Log::warning('Gagal mengambil data pelayanan dari API: '. $response->status());
            }

            // 2. Buat Paginator secara manual dari array yang sudah lengkap

            // Ubah array biasa menjadi Laravel Collection
            $allDataCollection = collect($allItems);

            // "Slice" (potong) collection untuk halaman saat ini
            $itemsForCurrentPage = $allDataCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

            // Hitung total item
            $total = $allDataCollection->count();

            // Buat objek Paginator
            $pelayanan = new LengthAwarePaginator(
                $itemsForCurrentPage,    // Data yang sudah dipotong (maks 10 item)
                $total,                  // Total semua data (misal: 38)
                $perPage,                // Item per halaman (10)
                $currentPage,            // Halaman saat ini
                [
                    'path' => $request->url(), // URL dasar
                    'query' => $request->query(), // Pertahankan query string lain
                ]
            );

            return view('pelayanan.index', compact('pelayanan'));

        } catch (\Exception $e) {
            Log::error('Exception di PelayananController@index: ' . $e->getMessage());

            // Buat paginator kosong saat exception
            $pelayanan = new LengthAwarePaginator([], 0, $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query()
            ]);

            return view('pelayanan.index', compact('pelayanan'))->with('error', 'Terjadi kesalahan saat memuat data pelayanan.');
        }
    }

    public function create()
    {
        $departemens = $this->_getDepartemens(Session::get('token'));
        return view('pelayanan.create', compact('departemens'));
    }

    public function store(Request $request)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge([
            'nama_layanan' => strip_tags($request->input('nama_layanan')),
            'keterangan' => strip_tags($request->input('keterangan')),
        ]);

        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:255',
            'id_departemen'  => 'required|numeric',
            'keterangan'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->post($this->apiBaseUrl . '/pelayanan', $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil ditambahkan.');
            }

            return back()->with('error', $response->json('message', 'Gagal menambahkan layanan.'))->withInput();
        } catch (\Exception $e) {
            Log::error('Exception di PelayananController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');

            // [PERFORMA] Menjalankan dua panggilan API secara bersamaan
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->withToken($token)->get($this->apiBaseUrl . "/pelayanan/{$id}"),
                $pool->withToken($token)->get($this->apiBaseUrl . '/departemen'),
            ]);

            $layananResponse = $responses[0];
            $departemenResponse = $responses[1];

            if ($layananResponse->unauthorized() || $departemenResponse->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($layananResponse->failed()) {
                return redirect()->route('pelayanan.index')->with('error', 'Layanan tidak ditemukan.');
            }

            $layanan = $layananResponse->json('data', []);
            $departemens = $departemenResponse->json('data', []);

            return view('pelayanan.edit', compact('layanan', 'departemens'));
        } catch (\Exception $e) {
            Log::error('Exception di PelayananController@edit: ' . $e->getMessage());
            return redirect()->route('pelayanan.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function update(Request $request, $id)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge([
            'nama_layanan' => strip_tags($request->input('nama_layanan')),
            'keterangan' => strip_tags($request->input('keterangan')),
        ]);

        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:255',
            'id_departemen'  => 'required|numeric',
            'keterangan'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->put($this->apiBaseUrl . "/pelayanan/{$id}", $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil diperbarui.');
            }

            return back()->with('error', $response->json('message', 'Gagal memperbarui layanan.'))->withInput();
        } catch (\Exception $e) {
            Log::error('Exception di PelayananController@update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete($this->apiBaseUrl . "/pelayanan/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil dihapus.');
            }

            return redirect()->route('pelayanan.index')->with('error', $response->json('message', 'Gagal menghapus layanan.'));
        } catch (\Exception $e) {
            Log::error('Exception di PelayananController@destroy: ' . $e->getMessage());
            return redirect()->route('pelayanan.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    /**
     * [DRY] Helper method privat untuk mengambil data departemen.
     */
    private function _getDepartemens(?string $token): array
    {
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)->get($this->apiBaseUrl . '/departemen');

            if ($response->unauthorized()) {
                Session::flush();
                // Kita tidak bisa redirect dari sini, jadi kembalikan array kosong saja.
                // Controller utama akan redirect jika perlu.
                return [];
            }

            if ($response->successful()) {
                return $response->json('data', []);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data departemen: ' . $e->getMessage());
        }

        return [];
    }
}