<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'nama_pengguna' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('nama_pengguna', $credentials['nama_pengguna'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login gagal. Nama pengguna atau password salah.'
                ], 401);
            }

            // Hapus semua token lama
            $user->tokens()->delete();

            // Buat token baru untuk Sanctum
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'role' => $user->role,
                    'token' => $token
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout berhasil'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
