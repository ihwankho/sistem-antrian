<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PelayananController extends Controller
{
    public function index()
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk mengambil data pelayanan
            $response = Http::withToken($token)->get('http://localhost:8001/api/pelayanan');
            
            $pelayanan = [];
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $pelayanan = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $pelayanan = $responseData;
                }
            } else {
                if ($response->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
                Log::error('Gagal mengambil data pelayanan. Status: ' . $response->status());
            }
            
            return view('pelayanan.index', compact('pelayanan'));
        } catch (\Exception $e) {
            Log::error('Exception dalam PelayananController@index: ' . $e->getMessage());
            return view('pelayanan.index', ['pelayanan' => []])
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk mengambil data departemen
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen');
            
            $departemens = [];
            if ($departemenResponse->successful()) {
                $responseData = $departemenResponse->json();
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $departemens = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $departemens = $responseData;
                }
            } else {
                if ($departemenResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }
            
            return view('pelayanan.create', compact('departemens'));
        } catch (\Exception $e) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk menyimpan data pelayanan
            $response = Http::withToken($token)->post('http://localhost:8001/api/pelayanan', $request->all());
            
            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil ditambahkan.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan';
            
            if (isset($errorData['errors'])) {
                return back()->withErrors($errorData['errors'])->withInput();
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Exception dalam PelayananController@store: ' . $e->getMessage());
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk mengambil data pelayanan spesifik
            $layananResponse = Http::withToken($token)->get("http://localhost:8001/api/pelayanan/{$id}");
            
            // Menggunakan hardcode URL untuk mengambil data departemen
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen');

            if ($layananResponse->failed()) {
                return redirect()->route('pelayanan.index')->with('error', 'Layanan tidak ditemukan.');
            }
            
            if ($layananResponse->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            $responseData = $layananResponse->json();
            $layanan = [];
            if (isset($responseData['data'])) {
                $layanan = $responseData['data'];
            } elseif (is_array($responseData)) {
                $layanan = $responseData;
            }

            $departemens = [];
            if ($departemenResponse->successful()) {
                $departemenData = $departemenResponse->json();
                if (isset($departemenData['data']) && is_array($departemenData['data'])) {
                    $departemens = $departemenData['data'];
                } elseif (is_array($departemenData)) {
                    $departemens = $departemenData;
                }
            }
            
            return view('pelayanan.edit', compact('layanan', 'departemens'));
        } catch (\Exception $e) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk memperbarui data pelayanan
            $response = Http::withToken($token)->put("http://localhost:8001/api/pelayanan/{$id}", $request->all());
            
            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil diperbarui.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Gagal update';
            
            if (isset($errorData['errors'])) {
                return back()->withErrors($errorData['errors'])->withInput();
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Exception dalam PelayananController@update: ' . $e->getMessage());
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            
            // Menggunakan hardcode URL untuk menghapus data pelayanan
            $response = Http::withToken($token)->delete("http://localhost:8001/api/pelayanan/{$id}");
            
            if ($response->successful()) {
                return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil dihapus.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            return redirect()->route('pelayanan.index')->with('error', 'Gagal menghapus layanan.');
        } catch (\Exception $e) {
            Log::error('Exception dalam PelayananController@destroy: ' . $e->getMessage());
            return redirect()->route('pelayanan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}