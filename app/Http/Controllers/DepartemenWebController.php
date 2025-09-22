<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DepartemenWebController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001') . '/api/departemen';
    }

    public function index()
    {
        try {
            $token = Session::get('token');
            
            // Mengambil data departemen dengan informasi loket
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen-loket');
            $departemens = [];
            
            if ($departemenResponse->successful()) {
                $responseData = $departemenResponse->json();
                
                // Debug struktur response
                Log::info('Struktur response departemen-loket:', $responseData);
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $departemens = $responseData['data'];
                } elseif (is_array($responseData) && isset($responseData[0]['nama_departemen'])) {
                    $departemens = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data departemen. Status: ' . $departemenResponse->status());
                if ($departemenResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Mengambil data loket untuk dropdown (jika diperlukan)
            $loketResponse = Http::withToken($token)->get('http://localhost:8001/api/lokets');
            $lokets = [];
            
            if ($loketResponse->successful()) {
                $responseData = $loketResponse->json();
                if (isset($responseData['data'])) {
                    $lokets = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $lokets = $responseData;
                }
            }

            return view('departemen.index', compact('departemens', 'lokets'));
        } catch (\Exception $e) {
            Log::error('Exception dalam DepartemenWebController@index: ' . $e->getMessage());
            return view('departemen.index', ['departemens' => [], 'lokets' => []])
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $token = Session::get('token');
            // Menggunakan hardcode URL untuk mengambil data loket
            $loketResponse = Http::withToken($token)->get('http://localhost:8001/api/lokets');
            $lokets = [];
            
            if ($loketResponse->successful()) {
                $responseData = $loketResponse->json();
                if (isset($responseData['data'])) {
                    $lokets = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $lokets = $responseData;
                }
            } else {
                if ($loketResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
                // Jika gagal mengambil loket, tetap tampilkan form dengan loket kosong
                $lokets = [];
            }
            
            return view('departemen.create', compact('lokets'));
        } catch (\Exception $e) {
            // Jika terjadi exception, tetap tampilkan form dengan loket kosong
            $lokets = [];
            return view('departemen.create', compact('lokets'))
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $token = Session::get('token');
            
            // Debug data yang dikirim
            Log::info('Data yang dikirim ke API:', $request->all());
            
            $response = Http::withToken($token)->post('http://localhost:8001/api/departemen', [
                'nama_departemen' => $request->nama_departemen,
                'id_loket' => $request->id_loket
            ]);
            
            // Debug response dari API
            Log::info('Response dari API:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil ditambahkan');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan';
            
            // Jika ada error validasi, tampilkan pesan error
            if (isset($errorData['errors'])) {
                return back()->withErrors($errorData['errors'])->withInput();
            }
            
            return back()->with('error', $errorMessage)->withInput();
        } catch (\Exception $e) {
            Log::error('Exception dalam DepartemenWebController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->get("http://localhost:8001/api/departemen/{$id}");
            $loketResponse = Http::withToken($token)->get('http://localhost:8001/api/lokets');

            if ($response->failed()) {
                return redirect()->route('departemen.index')->with('error', 'Departemen tidak ditemukan.');
            }

            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            $responseData = $response->json();
            $departemen = [];
            if (isset($responseData['data'])) {
                $departemen = $responseData['data'];
            } elseif (is_array($responseData)) {
                $departemen = $responseData;
            }
            
            $lokets = [];
            if ($loketResponse->successful()) {
                $loketData = $loketResponse->json();
                if (isset($loketData['data'])) {
                    $lokets = $loketData['data'];
                } elseif (is_array($loketData)) {
                    $lokets = $loketData;
                }
            } else {
                if ($loketResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }
            
            return view('departemen.edit', compact('departemen', 'lokets'));
        } catch (\Exception $e) {
            return redirect()->route('departemen.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->put("http://localhost:8001/api/departemen/{$id}", $request->all());
            
            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil diperbarui');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Gagal update';
            $errors = isset($errorData['errors']) ? $errorData['errors'] : [$errorMessage];
            
            return back()->withErrors($errors)->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete("http://localhost:8001/api/departemen/{$id}");
            
            if ($response->successful()) {
                return redirect()->route('departemen.index')->with('success', 'Departemen berhasil dihapus');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            return redirect()->route('departemen.index')->with('error', 'Gagal menghapus departemen.');
        } catch (\Exception $e) {
            return redirect()->route('departemen.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}