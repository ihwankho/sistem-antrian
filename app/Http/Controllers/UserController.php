<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        try {
            $token = Session::get('token');
            
            // Mengambil data user dengan informasi loket
            $userResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/users-loket');
            
            $users = [];
            if ($userResponse->successful()) {
                $responseData = $userResponse->json();
                
                // Debug struktur response
                Log::info('Struktur response users-loket:', $responseData);
                
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $users = $responseData['data'];
                } elseif (is_array($responseData) && isset($responseData[0]['nama'])) {
                    $users = $responseData;
                }
            } else {
                Log::error('Gagal mengambil data user. Status: ' . $userResponse->status());
                if ($userResponse->status() == 401) {
                    Session::forget('token');
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Mengambil data loket untuk dropdown (jika diperlukan)
            $loketResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/lokets');
            $lokets = [];
            
            if ($loketResponse->successful()) {
                $responseData = $loketResponse->json();
                if (isset($responseData['data'])) {
                    $lokets = $responseData['data'];
                } elseif (is_array($responseData)) {
                    $lokets = $responseData;
                }
            }

            return view('user.index', compact('users', 'lokets'));
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@index: ' . $e->getMessage());
            return view('user.index', ['users' => [], 'lokets' => []])
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $token = Session::get('token');
            
            // Mengambil data loket untuk dropdown
            $loketResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/lokets');
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
            
            return view('user.create', compact('lokets'));
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@create: ' . $e->getMessage());
            // Jika terjadi exception, tetap tampilkan form dengan loket kosong
            $lokets = [];
            return view('user.create', compact('lokets'))
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $token = Session::get('token');
            
            // Validasi input termasuk foto
            $request->validate([
                'nama' => 'required|string|max:255',
                'nama_pengguna' => 'required|string|max:255',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:1,2',
                'id_loket' => 'required_if:role,2',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Debug data yang dikirim
            Log::info('Data yang dikirim ke API:', $request->all());
            
            // Siapkan data untuk dikirim ke API
            $data = $request->only(['nama', 'nama_pengguna', 'password', 'password_confirmation', 'role', 'id_loket']);

            // Jika ada foto, siapkan untuk multipart upload
            if ($request->hasFile('foto')) {
                $response = Http::withToken($token)
                    ->attach('foto', $request->file('foto')->get(), $request->file('foto')->getClientOriginalName())
                    ->post('http://127.0.0.1:8001/api/users', $data);
            } else {
                $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/users', $data);
            }
            
            // Debug response dari API
            Log::info('Response dari API:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan';
            $errors = isset($errorData['errors']) ? $errorData['errors'] : [$errorMessage];
            
            return back()->withErrors($errors)->withInput();
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@store: ' . $e->getMessage());
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            
            // Ambil data user spesifik
            $userResponse = Http::withToken($token)->get("http://127.0.0.1:8001/api/users/{$id}"); 
            
            if ($userResponse->failed()) {
                return redirect()->route('pengguna.index')->with('error', 'Pengguna tidak ditemukan.');
            }
            
            if ($userResponse->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            // Ambil data loket untuk dropdown
            $loketResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/lokets');
            
            $responseData = $userResponse->json();
            $user = [];
            if (isset($responseData['data'])) {
                $user = $responseData['data'];
            } elseif (is_array($responseData)) {
                $user = $responseData;
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

            return view('user.edit', compact('user', 'lokets'));
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@edit: ' . $e->getMessage());
            return redirect()->route('pengguna.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $token = Session::get('token');
            
            // Validasi input termasuk foto
            $request->validate([
                'nama' => 'required|string|max:255',
                'nama_pengguna' => 'required|string|max:255',
                'password' => 'nullable|string|min:6|confirmed',
                'role' => 'required|in:1,2',
                'id_loket' => 'required_if:role,2',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Siapkan data untuk dikirim ke API
            $data = $request->only(['nama', 'nama_pengguna', 'role', 'id_loket']);
            
            // Hanya kirim password jika diisi
            if ($request->filled('password')) {
                $data['password'] = $request->password;
                $data['password_confirmation'] = $request->password_confirmation;
            }

            // Debug data yang dikirim
            Log::info('Data yang dikirim ke API untuk update:', $data);
            
            // Jika ada foto, siapkan untuk multipart upload
            if ($request->hasFile('foto')) {
                $response = Http::withToken($token)
                    ->attach('foto', $request->file('foto')->get(), $request->file('foto')->getClientOriginalName())
                    ->put("http://127.0.0.1:8001/api/users/{$id}", $data);
            } else {
                $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/users/{$id}", $data);
            }

            // Debug response dari API
            Log::info('Response dari API untuk update:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Terjadi kesalahan';
            $errors = isset($errorData['errors']) ? $errorData['errors'] : [$errorMessage];
            
            return back()->withErrors($errors)->withInput();
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@update: ' . $e->getMessage());
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete("http://127.0.0.1:8001/api/users/{$id}");
            
            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
            }
            
            if ($response->status() == 401) {
                Session::forget('token');
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }
            
            return redirect()->route('pengguna.index')->with('error', 'Gagal menghapus pengguna.');
        } catch (\Exception $e) {
            Log::error('Exception dalam UserController@destroy: ' . $e->getMessage());
            return redirect()->route('pengguna.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}