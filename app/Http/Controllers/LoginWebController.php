<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // Kirim request ke API
            $response = Http::post(env('API_BASE_URL') . '/login', [
                'nama_pengguna' => $request->nama_pengguna,
                'password' => $request->password,
            ]);

            if ($response->successful() && $response->json('status') === true) {
                $data = $response->json('data');

                // Cek user lokal berdasarkan ID yang dikirim API
                $user = User::find($data['id']);

                if (!$user) {
                    return back()->with('error', 'User tidak ditemukan di sistem lokal.');
                }

                // Login ke web menggunakan session Laravel
                Auth::login($user);

                return redirect('/dashboard');
            }

            return back()->with('error', 'Login gagal: ' . $response->json('message'));
        } catch (\Exception $e) {
            return back()->with('error', 'Kesalahan server: ' . $e->getMessage());
        }
    }
}
