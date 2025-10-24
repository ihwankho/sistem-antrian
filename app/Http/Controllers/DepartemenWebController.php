<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Pool; // Ditambahkan untuk performa

class DepartemenWebController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        // --- PERUBAHAN DI SINI: Mengambil URL dasar dari config/services.php ---
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
        // --------------------------------------------------------------------
    }

    public function index()
    {
        try {
            $token = Session::get('token');

            // [PERFORMA] Menjalankan dua panggilan API secara bersamaan
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->withToken($token)->get($this->apiBaseUrl . '/departemen-loket'),
                $pool->withToken($token)->get($this->apiBaseUrl . '/lokets'),
            ]);

            $departemenResponse = $responses[0];
            $loketResponse = $responses[1];

            // Handle jika sesi berakhir (unauthorized)
            if ($departemenResponse->unauthorized() || $loketResponse->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            $departemens = $departemenResponse->successful() ? $departemenResponse->json('data', []) : [];
            $lokets = $loketResponse->successful() ? $loketResponse->json('data', []) : [];

            return view('departemen.index', compact('departemens', 'lokets'));

        } catch (\Exception $e) {
            Log::error('Exception di DepartemenWebController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data departemen.');
        }
    }

    public function create()
    {
        $lokets = $this->_getLokets(Session::get('token'));
        return view('departemen.create', compact('lokets'));
    }

    public function store(Request $request)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge(['nama_departemen' => strip_tags($request->input('nama_departemen'))]);

        $validator = Validator::make($request->all(), [
            'nama_departemen' => 'required|string|max:255',
            'id_loket'        => 'required|numeric|exists:lokets,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->post($this->apiBaseUrl . '/departemen', $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil ditambahkan.');
            }

            return back()->with('error', $response->json('message', 'Gagal menambahkan departemen.'))->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di DepartemenWebController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->get($this->apiBaseUrl . "/departemen/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->failed()) {
                return redirect()->route('departemen.index')->with('error', 'Departemen tidak ditemukan.');
            }

            $departemen = $response->json('data', []);
            $lokets = $this->_getLokets($token);

            return view('departemen.edit', compact('departemen', 'lokets'));

        } catch (\Exception $e) {
            Log::error('Exception di DepartemenWebController@edit: ' . $e->getMessage());
            return redirect()->route('departemen.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function update(Request $request, $id)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge(['nama_departemen' => strip_tags($request->input('nama_departemen'))]);

        $validator = Validator::make($request->all(), [
            'nama_departemen' => 'required|string|max:255',
            'id_loket'        => 'required|numeric|exists:lokets,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->put($this->apiBaseUrl . "/departemen/{$id}", $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil diperbarui.');
            }

            return back()->with('error', $response->json('message', 'Gagal memperbarui departemen.'))->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di DepartemenWebController@update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete($this->apiBaseUrl . "/departemen/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil dihapus.');
            }

            return redirect()->route('departemen.index')->with('error', 'Gagal menghapus departemen.');

        } catch (\Exception $e) {
            Log::error('Exception di DepartemenWebController@destroy: ' . $e->getMessage());
            return redirect()->route('departemen.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    /**
     * [DRY] Helper method privat untuk mengambil data loket.
     * * @param string|null $token Token otorisasi.
     * @return array
     */
    private function _getLokets(?string $token): array
    {
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)->get($this->apiBaseUrl . '/lokets');
            if ($response->successful()) {
                return $response->json('data', []);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data loket: ' . $e->getMessage());
        }

        return [];
    }
}