<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoketController extends Controller
{
    public function index()
    {
        try {
            $token = Session::get('token');
            Log::info('Mengambil data loket dari: http://localhost:8001/api/lokets');
            
            // Menggunakan hardcode URL untuk mengambil data loket
            $loketResponse = Http::withToken($token)->get('http://localhost:8001/api/lokets');
            $lokets = [];

            if ($loketResponse->successful()) {
                $responseData = $loketResponse->json();
                Log::info('Response API Loket: ', $responseData ?: []);
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $lokets = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $lokets = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data loket. Status: ' . $loketResponse->status());
                if ($loketResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Menggunakan hardcode URL untuk mengambil data departemen-loket
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen-loket');
            $departemens = [];
            
            if ($departemenResponse->successful()) {
                $responseData = $departemenResponse->json();
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $departemens = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $departemens = $responseData;
                }
            }

            // Menggunakan hardcode URL untuk mengambil data users-loket
            $usersResponse = Http::get('http://localhost:8001/api/users-loket');
            $users = [];
            
            if ($usersResponse->successful()) {
                $responseData = $usersResponse->json();
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $users = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $users = $responseData;
                }
            }

            return view('loket.index', compact('lokets', 'departemens', 'users'));
        } catch (\Exception $e) {
            Log::error('Exception dalam LoketController@index: ' . $e->getMessage());
            return view('loket.index', ['lokets' => [], 'departemens' => [], 'users' => []])
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $token = Session::get('token');
            // Menggunakan hardcode URL untuk mengambil data departemen
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen-loket');
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

            return view('loket.create', compact('departemens'));
        } catch (\Exception $e) {
            return redirect()->route('loket.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $token = Session::get('token');
            Log::info('Mengirim data ke API: http://localhost:8001/api/lokets');
            
            $response = Http::withToken($token)->post('http://localhost:8001/api/lokets', $request->all());
            
            Log::info('Response status: ' . $response->status());
            Log::info('Response body: ' . $response->body());

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil ditambahkan.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan saat menyimpan data';
            
            if (isset($errorData['errors'])) {
                return redirect()->back()->withErrors($errorData['errors'])->withInput();
            } else {
                return redirect()->back()->withErrors(['error' => $errorMessage])->withInput();
            }
            
        } catch (\Exception $e) {
            Log::error('Exception dalam store: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            // Menggunakan hardcode URL untuk mengambil data loket
            $response = Http::withToken($token)->get("http://localhost:8001/api/lokets/{$id}");
            
            // Menggunakan hardcode URL untuk mengambil data departemen
            $departemenResponse = Http::withToken($token)->get('http://localhost:8001/api/departemen-loket');
            
            if ($response->failed()) {
                return redirect()->route('loket.index')->with('error', 'Loket tidak ditemukan.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $responseData = $response->json();
            $loket = [];
            if (isset($responseData['data'])) {
                $loket = $responseData['data'];
            } elseif (is_array($responseData)) {
                $loket = $responseData;
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
            
            return view('loket.edit', compact('loket', 'departemens'));
        } catch (\Exception $e) {
            return redirect()->route('loket.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $token = Session::get('token');
            // Menggunakan hardcode URL untuk update data loket
            $response = Http::withToken($token)->put("http://localhost:8001/api/lokets/{$id}", $request->all());

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil diperbarui.');
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
            // Menggunakan hardcode URL untuk menghapus data loket
            $response = Http::withToken($token)->delete("http://localhost:8001/api/lokets/{$id}");

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil dihapus.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            return redirect()->route('loket.index')->with('error', 'Gagal menghapus loket.');
        } catch (\Exception $e) {
            return redirect()->route('loket.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}