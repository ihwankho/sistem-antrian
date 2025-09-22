<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class LoginWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'nama_pengguna' => 'required|string',
                'password' => 'required|string',
            ]);

            Log::info('Attempting login for user: ' . $request->nama_pengguna);

            // Coba login lokal terlebih dahulu
            $user = User::where('nama_pengguna', $request->nama_pengguna)->first();

            
            // if ($user && Hash::check($request->password, $user->password)) {
            //     Auth::login($user);
            //     Log::info('Local login successful for user: ' . $request->nama_pengguna);
            //     // Redirect berdasarkan role
            //     return $this->redirectBasedOnRole($user);
            // }

            // Jika login lokal gagal, coba login via API
            $apiBaseUrl = env('API_BASE_URL');
            if (!$apiBaseUrl) {
                Log::error('API_BASE_URL not configured');
                return back()->with('error', 'Konfigurasi API tidak ditemukan. Silakan hubungi administrator.');
            }

            Log::info('Trying API login for user: ' . $request->nama_pengguna);

            $response = Http::timeout(30)
                ->acceptJson()
                ->contentType('application/json')
                ->post($apiBaseUrl . '/login', [
                    'nama_pengguna' => $request->nama_pengguna,
                    'password' => $request->password,
                ]);

            Log::info('API Response Status: ' . $response->status());
            Log::info('API Response Body: ' . $response->body());


            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    $data = $responseData['data'];

                    // Cek atau buat user lokal
                    $localUser = User::find($data['id']);
                    if (!$localUser) {
                        $localUser = User::create([
                            'id' => $data['id'],
                            'nama' => $data['nama'],
                            'nama_pengguna' => $data['nama_pengguna'],
                            'role' => (int)$data['role'],
                            'id_loket' => $data['id_loket'] ?? null,
                            'password' => bcrypt($request->password),
                        ]);
                        Log::info('Created new local user: ' . $data['nama_pengguna']);
                    } else {
                        $localUser->update([
                            'nama' => $data['nama'],
                            'nama_pengguna' => $data['nama_pengguna'],
                            'role' => (int)$data['role'],
                            'id_loket' => $data['id_loket'] ?? null,
                            'password' => bcrypt($request->password),
                        ]);
                        Log::info('Updated local user: ' . $data['nama_pengguna']);
                    }

                    // Login ke web menggunakan session Laravel
                    Auth::login($localUser);

                    // ✅ Simpan token dari API ke session
                    if (isset($data['token'])) {
                        session(['token' => $data['token']]);
                        Log::info('Token stored in session for user: ' . $data['nama_pengguna']);
                    }
                    $token = Session::get('token');

                    
                    Log::info('API login successful for user: ' . $request->nama_pengguna);

                    // Redirect berdasarkan role
                    return $this->redirectBasedOnRole($localUser);
                } else {
                    $message = $responseData['message'] ?? 'Login gagal';
                    Log::warning('API login failed: ' . $message);
                    return back()->with('error', 'Login gagal: ' . $message);
                }
            } else {
                $statusCode = $response->status();
                $errorMessage = 'Terjadi kesalahan pada server (HTTP ' . $statusCode . ')';
                
                if ($response->json() && isset($response->json()['message'])) {
                    $errorMessage = $response->json()['message'];
                } elseif ($statusCode == 401) {
                    $errorMessage = 'Nama pengguna atau password salah';
                } elseif ($statusCode == 500) {
                    $errorMessage = 'Terjadi kesalahan pada server';
                }
                
                Log::error('API request failed with status ' . $statusCode . ': ' . $response->body());
                return back()->with('error', 'Login gagal: ' . $errorMessage);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengirim permintaan ke server.');
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.');
        }
    }

    /**
     * Redirect pengguna berdasarkan role
     * 1 = Admin, 2 = Petugas
     */
    private function redirectBasedOnRole(User $user)
    {
        if ($user->role === 1) { // Admin
            return redirect('/dashboard');
        } elseif ($user->role === 2) { // Petugas
            return redirect('/panggilan/admin');
        } else {
            Auth::logout();
            return redirect('/login')->with('error', 'Role tidak dikenali. Silakan hubungi administrator.');
        }
    }

    public function logout(Request $request)
    {
        $token = session('token');
        $apiBaseUrl = env('API_BASE_URL');

        // Panggil API logout jika ada token
        if ($token && $apiBaseUrl) {
            try {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])->post($apiBaseUrl . '/logout');
                Log::info('API logout request sent for token: ' . $token);
            } catch (\Exception $e) {
                Log::error('API logout failed: ' . $e->getMessage());
            }
        }

        // Hapus session & logout
        $request->session()->forget('token');
        Auth::logout();

        return redirect('/login');
    }
}
