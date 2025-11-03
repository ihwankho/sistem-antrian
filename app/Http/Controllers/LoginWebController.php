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
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // [KEAMANAN] Sanitasi input nama pengguna
            // Walaupun tipenya email, field name-nya tetap 'nama_pengguna'
            $request->merge(['nama_pengguna' => strip_tags($request->input('nama_pengguna'))]);

            // --- PERUBAHAN: Validasi 'nama_pengguna' ditambahkan rule 'email' ---
            $credentialsAndCaptcha = $request->validate([
                'nama_pengguna'        => 'required|string|email|max:255', // <-- PERUBAHAN
                'password'             => 'required|string',
                'g-recaptcha-response' => 'required|string',
            ]);

            // --- VALIDASI CAPTCHA GOOGLE ---
            $recaptchaToken = $credentialsAndCaptcha['g-recaptcha-response'];
            $secretKey = config('services.recaptcha.secret_key');

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secretKey,
                'response' => $recaptchaToken,
                'remoteip' => $request->ip(),
            ]);

            $responseData = $response->json();

            if (!$responseData || !isset($responseData['success']) || $responseData['success'] !== true) {
                Log::warning('reCAPTCHA verification failed.', $responseData);
                return back()
                    ->withErrors(['captcha' => 'Verifikasi "I\'m not a robot" gagal. Silakan coba lagi.'])
                    ->withInput($request->except('password', 'g-recaptcha-response'));
            }
            // --- AKHIR VALIDASI CAPTCHA GOOGLE ---

            $credentials = [
                'nama_pengguna' => $credentialsAndCaptcha['nama_pengguna'],
                'password'      => $credentialsAndCaptcha['password'],
            ];

            $apiResponse = $this->_authenticateWithApi($credentials);

            if ($apiResponse->failed()) {
                // [PERBAIKAN] Panggil helper _handleApiError
                return $this->_handleApiError($apiResponse);
            }

            $apiData = $apiResponse->json('data');
            $localUser = $this->_syncLocalUser($apiData, $credentials['password']);

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
            Log::error('General error during login: '. $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan yang tidak terduga.');
        }
    }

    public function logout(Request $request)
    {
        $token = session('token');

        if ($token) {
            try {
                Http::withToken($token)->post($this->apiBaseUrl . '/logout');
                Log::info('API logout request sent.');
            } catch (\Exception $e) {
                Log::error('API logout failed: '. $e->getMessage());
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }


    /**
     * [HELPER] Mengirim kredensial ke API untuk otentikasi.
     */
    private function _authenticateWithApi(array $credentials): Response
    {
        Log::info('Attempting API login for user: ' . $credentials['nama_pengguna']);
        return Http::timeout(30)->post($this->apiBaseUrl . '/login', $credentials);
    }

    /**
     * [HELPER] Sinkronisasi data pengguna dari API ke database lokal.
     */
    private function _syncLocalUser(array $apiUserData, string $password): User
    {
        return User::updateOrCreate(
            ['id' => $apiUserData['id']], 
            [
                'nama' => $apiUserData['nama'],
                'nama_pengguna' => $apiUserData['nama_pengguna'],
                'role' => (int) $apiUserData['role'],
                'id_loket' => $apiUserData['id_loket'] ?? null,
                'password' => bcrypt($password), 
            ]
        );
    }

    /**
     * [HELPER] Menangani berbagai jenis error dari respons API.
     */
    private function _handleApiError(Response $response)
    {
        $statusCode = $response->status();
        $responseData = $response->json();
        $errorMessage = $responseData['message'] ?? 'Terjadi kesalahan pada server.';

        // --- PERUBAHAN: Pesan error diubah menjadi "Email" ---
        if ($statusCode == 401) {
            $errorMessage = 'Email atau password salah.'; // <-- PERUBAHAN
        }

        Log::error("API login failed with status {$statusCode}: " . $response->body());
        return back()->with('error', $errorMessage);
    }

    /**
     * [HELPER] Redirect pengguna berdasarkan peran.
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