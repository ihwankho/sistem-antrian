<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoketService;
use Illuminate\Support\Facades\Http;

class AuthWebController extends Controller
{
    protected $loketService;

    public function __construct(LoketService $loketService)
    {
        $this->loketService = $loketService;
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nama_pengguna' => 'required',
            'password' => 'required',
        ]);

        $response = Http::post('http://localhost:8000/api/login', [
            'nama_pengguna' => $request->nama_pengguna,
            'password' => $request->password,
        ]);

        if ($response->ok() && $response['status'] === true) {
            $data = $response['data'];
            session([
                'user' => $data,
                'token' => $data['token'],
            ]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['msg' => $response['message'] ?? 'Login gagal']);
    }

    public function dashboard()
    {
        $user = session('user');
        $token = session('token');

        $lokets = [];
        $tokenExpired = false;

        if ($token) {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get('http://localhost:8000/api/lokets');

            // Cek jika token tidak valid
            if ($res->ok()) {
                $body = $res->json();
                if ($body['status'] === true) {
                    $lokets = $body['data'];
                } else {
                    $tokenExpired = true;
                }
            } else {
                $tokenExpired = true;
            }
        } else {
            $tokenExpired = true;
        }

        return view('home', compact('user', 'lokets', 'tokenExpired'));
    }


    public function logout(Request $request)
    {
        $token = session('token');

        if ($token) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->post('http://localhost:8000/api/logout');
        }

        $request->session()->flush();
        return redirect()->route('login');
    }
}
