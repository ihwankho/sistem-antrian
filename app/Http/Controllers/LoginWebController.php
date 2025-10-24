<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Client\Response;

class LoginWebController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        // --- PERUBAHAN DI SINI: Mengambil URL dasar dari config/services.php ---
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
        // --------------------------------------------------------------------
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // [KEAMANAN] Sanitasi input nama pengguna untuk mencegah XSS
            $request->merge(['nama_pengguna' => strip_tags($request->input('nama_pengguna'))]);

            $credentials = $request->validate([
                'nama_pengguna' => 'required|string|max:255',
                'password'      => 'required|string',
            ]);

            // Panggil API untuk otentikasi
            $response = $this->_authenticateWithApi($credentials);

            if ($response->failed()) {
                return $this->_handleApiError($response);
            }

            $apiData = $response->json('data');

            // Buat atau perbarui pengguna di database lokal
            $localUser = $this->_syncLocalUser($apiData, $credentials['password']);

            // Login ke sesi web Laravel
            Auth::login($localUser);
            session(['token' => $apiData['token'] ?? null]);

            Log::info('Login successful and user synced for: ' . $localUser->nama_pengguna);

            return $this->redirectBasedOnRole($localUser);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error during login: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server. Periksa koneksi Anda.');
        } catch (\Exception $e) {
            Log::error('General error during login: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan yang tidak terduga.');
        }
    }

    public function logout(Request $request)
    {
        $token = session('token');

        if ($token) {
            try {
                // Peringatan: HTTP tidak aman untuk produksi, gunakan HTTPS.
                Http::withToken($token)->post($this->apiBaseUrl . '/logout');
                Log::info('API logout request sent.');
            } catch (\Exception $e) {
                Log::error('API logout failed: ' . $e->getMessage());
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * [HELPER] Mengirim kredensial ke API untuk otentikasi.
     * @param array $credentials
     * @return Response
     */
    private function _authenticateWithApi(array $credentials): Response
    {
        Log::info('Attempting API login for user: ' . $credentials['nama_pengguna']);

        // Peringatan: Mengirim password via HTTP sangat tidak aman untuk produksi.
        // Harap gunakan HTTPS untuk melindungi data pengguna.
        return Http::timeout(30)->post($this->apiBaseUrl . '/login', $credentials);
    }

    /**
     * [HELPER] Sinkronisasi data pengguna dari API ke database lokal.
     * Menggunakan updateOrCreate() untuk efisiensi.
     * @param array $apiUserData
     * @param string $password
     * @return User
     */
    private function _syncLocalUser(array $apiUserData, string $password): User
    {
        return User::updateOrCreate(
            ['id' => $apiUserData['id']], // Kunci untuk mencari pengguna
            [                               // Data untuk diperbarui atau dibuat
                'nama' => $apiUserData['nama'],
                'nama_pengguna' => $apiUserData['nama_pengguna'],
                'role' => (int) $apiUserData['role'],
                'id_loket' => $apiUserData['id_loket'] ?? null,
                'password' => bcrypt($password), // Selalu update password
            ]
        );
    }

    /**
     * [HELPER] Menangani berbagai jenis error dari respons API.
     * @param Response $response
     * @return \Illuminate\Http\RedirectResponse
     */
    private function _handleApiError(Response $response)
    {
        $statusCode = $response->status();
        $responseData = $response->json();
        $errorMessage = $responseData['message'] ?? 'Terjadi kesalahan pada server.';

        if ($statusCode == 401) {
            $errorMessage = 'Nama pengguna atau password salah.';
        }

        Log::error("API login failed with status {$statusCode}: " . $response->body());
        return back()->with('error', $errorMessage);
    }

    /**
     * [HELPER] Redirect pengguna berdasarkan peran.
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectBasedOnRole(User $user)
    {
        if ($user->role === 1) { // Admin
            return redirect()->intended('dashboard');
        }

        if ($user->role === 2) { // Petugas
            return redirect()->intended('panggilan/admin');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Peran pengguna tidak valid.');
    }
}