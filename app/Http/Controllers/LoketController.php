<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoketController extends Controller
{
    // Tentukan base URL untuk API Anda
    protected $apiUrl;
    protected $departemenApiUrl;
    protected $usersApiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001') . '/api/lokets';
        $this->departemenApiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001') . '/api/departemen-loket';
        $this->usersApiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001') . '/api/users-loket';
    }

    /**
     * Helper method to add token to HTTP client
     */
    private function withToken($http)
    {
        $token = Session::get('token'); // Menggunakan 'token' seperti di DepartemenWebController
        if (!$token) {
            Log::error('Token tidak ditemukan di session');
            abort(redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.'));
        }
        return $http->withToken($token);
    }

    public function index()
    {
        try {
            Log::info('Mengambil data loket dari: ' . $this->apiUrl);
            $token = Session::get('token');
            
            // Panggilan 1: Ambil semua data loket dengan token
            $loketResponse = Http::withToken($token)->get($this->apiUrl);
            $lokets = [];

            if ($loketResponse->successful()) {
                $responseData = $loketResponse->json();
                Log::info('Response API Loket: ', $responseData ?: []);
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $lokets = $responseData['data'];
                } elseif (is_array($responseData)) {
                    // Cek apakah array tidak kosong dan memiliki struktur loket
                    $lokets = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data loket. Status: ' . $loketResponse->status());
                Log::error('Response: ' . $loketResponse->body());
                if ($loketResponse->status() == 401) {
                    Session::forget('api_token');
                    Session::forget('user');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Panggilan 2: Ambil data departemen-loket untuk pencocokan
            Log::info('Mengambil data departemen-loket dari: ' . $this->departemenApiUrl);
            $departemenResponse = Http::withToken($token)->timeout(30)->get($this->departemenApiUrl);
            $departemens = [];
            
            if ($departemenResponse->successful()) {
                $responseData = $departemenResponse->json();
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $departemens = $responseData['data'];
                } elseif (is_array($responseData)) {
                    // Cek apakah array tidak kosong dan memiliki struktur departemen
                    $departemens = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data departemen. Status: ' . $departemenResponse->status());
                if ($departemenResponse->status() == 401) {
                    Session::forget('api_token');
                    Session::forget('user');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Panggilan 3: Ambil data users-loket untuk pencocokan
            Log::info('Mengambil data users-loket dari: ' . $this->usersApiUrl);
            $usersResponse = Http::get($this->usersApiUrl); // Endpoint ini tidak perlu token berdasarkan route
            $users = [];
            
            if ($usersResponse->successful()) {
                $responseData = $usersResponse->json();
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $users = $responseData['data'];
                } elseif (is_array($responseData)) {
                    // Cek apakah array tidak kosong dan memiliki struktur user
                    $users = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data users. Status: ' . $usersResponse->status());
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
            // Ambil data departemen untuk dropdown
            $departemenResponse = Http::withToken(Session::get('token'))->get($this->departemenApiUrl);
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
                    Session::forget('api_token');
                    Session::forget('user');
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
            Log::info('Data request store loket: ', $request->all());
            
            // Validasi input di sisi frontend
            $request->validate([
                'nama_loket' => 'required|string|max:255',
            ]);

            // Panggil API POST /lokets dengan token
            $token = Session::get('token');
            
            Log::info('Mengirim data ke API: ' . $this->apiUrl);
            Log::info('Data yang dikirim: ', $request->all());
            
            $response = Http::withToken($token)->post($this->apiUrl, $request->all());
            
            Log::info('Response status: ' . $response->status());
            Log::info('Response body: ' . $response->body());

            if ($response->successful()) {
                Log::info('Loket berhasil disimpan');
                return redirect()->route('loket.index')->with('success', 'Loket berhasil ditambahkan.');
            }

            if ($response->status() == 401) {
                Session::forget('api_token');
                Session::forget('user');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            // Ambil pesan error dari API jika validasi gagal
            $errorData = $response->json();
            Log::error('Error data dari API: ', $errorData ?: []);
            
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan saat menyimpan data';
            
            if (isset($errorData['errors'])) {
                return redirect()->back()->withErrors($errorData['errors'])->withInput();
            } else {
                return redirect()->back()->withErrors(['error' => $errorMessage])->withInput();
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
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
            // Panggil API GET /lokets/{id} dengan token
            $token = Session::get('token');
            $response = Http::withToken($token)->get("{$this->apiUrl}/{$id}");
            $departemenResponse = Http::withToken($token)->get($this->departemenApiUrl);
            
            if ($response->failed()) {
                return redirect()->route('loket.index')->with('error', 'Loket tidak ditemukan.');
            }
            
            if ($response->status() == 401) {
                Session::forget('api_token');
                Session::forget('user');
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
            } else {
                if ($departemenResponse->status() == 401) {
                    Session::forget('api_token');
                    Session::forget('user');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
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
            // Panggil API PUT /lokets/{id} dengan token
            $token = Session::get('token');
            $response = Http::withToken($token)->put("{$this->apiUrl}/{$id}", $request->all());

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil diperbarui.');
            }

            if ($response->status() == 401) {
                Session::forget('api_token');
                Session::forget('user');
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
            // Panggil API DELETE /lokets/{id} dengan token
            $token = Session::get('token');
            $response = Http::withToken($token)->delete("{$this->apiUrl}/{$id}");

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil dihapus.');
            }
            
            if ($response->status() == 401) {
                Session::forget('api_token');
                Session::forget('user');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            return redirect()->route('loket.index')->with('error', 'Gagal menghapus loket.');
        } catch (\Exception $e) {
            return redirect()->route('loket.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}