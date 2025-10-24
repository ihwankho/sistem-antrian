<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthWebController extends Controller
{
    /**
     * URL basis untuk layanan API, diambil dari konfigurasi.
     * @var string
     */
    private $apiBaseUrl; // Tambahkan properti

    protected $loketService;

    /**
     * Konstruktor untuk Controller.
     * Menginisialisasi service dan URL dasar API.
     */
    public function __construct(LoketService $loketService)
    {
        $this->loketService = $loketService;

        // Mengambil URL dasar API dari config/services.php
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Menggunakan Validator eksplisit untuk validasi
        $validator = Validator::make($request->all(), [
            'nama_pengguna' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Mengganti URL hardcoded dengan $this->apiBaseUrl
            $response = Http::post($this->apiBaseUrl . '/login', $validator->validated());

            if ($response->successful() && $response->json('status') === true) {
                $data = $response->json('data');
                session([
                    'user' => $data,
                    'token' => $data['token'],
                ]);
                // Mengganti redirect ke intended untuk praktik yang lebih baik
                return redirect()->intended('dashboard');
            }

            // Menggunakan pesan error dari API jika tersedia
            return back()->withErrors(['msg' => $response->json('message') ?? 'Login gagal'])->withInput();
        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API Login: ' . $e->getMessage());
            return back()->withErrors(['msg' => 'Tidak dapat terhubung ke server otentikasi.'])->withInput();
        }
    }

    public function dashboard()
    {
        $user = session('user');
        $token = session('token');

        $lokets = [];
        $tokenExpired = false;

        if (!$token) {
            return redirect()->route('login')->withErrors(['msg' => 'Sesi Anda telah berakhir, silakan login kembali.']);
        }

        try {
            // Mengganti URL hardcoded dengan $this->apiBaseUrl
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get($this->apiBaseUrl . '/lokets');

            // Cek jika token tidak valid (401/403)
            if ($res->unauthorized() || $res->forbidden()) {
                $tokenExpired = true;
            } elseif ($res->successful()) {
                $body = $res->json();
                if ($body['status'] === true) {
                    $lokets = $body['data'];
                } else {
                    // Anggap token bermasalah jika API sukses tapi status false (otorisasi API)
                    $tokenExpired = true;
                }
            } else {
                $tokenExpired = true; // Gagal karena status non-200 lain
            }

            if ($tokenExpired) {
                session()->flush();
                return redirect()->route('login')->withErrors(['msg' => 'Sesi Anda tidak valid atau telah berakhir.']);
            }

            return view('home', compact('user', 'lokets'));

        } catch (\Exception $e) {
            Log::error('Gagal terhubung ke API Loket: ' . $e->getMessage());
            // Tampilkan dashboard dengan pesan error API
            return view('home', ['user' => $user, 'lokets' => $lokets, 'api_error' => 'Tidak dapat terhubung ke server.']);
        }
    }


    public function logout(Request $request)
    {
        $token = session('token');

        if ($token) {
            try {
                // Mengganti URL hardcoded dengan $this->apiBaseUrl
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])->post($this->apiBaseUrl . '/logout');
            } catch (\Exception $e) {
                // Jangan halangi logout di sisi web
                Log::warning('Gagal menghubungi API logout: ' . $e->getMessage());
            }
        }

        $request->session()->flush();
        return redirect()->route('login');
    }
}