<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'nama'           => 'required|string|max:255',
                'nama_pengguna'  => 'required|string|max:255|unique:users,nama_pengguna',
                'password'       => 'required|string|min:6',
                'role'           => 'required|in:admin,petugas',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan ke database
            $user = User::create([
                'nama'          => $request->nama,
                'nama_pengguna' => $request->nama_pengguna,
                'password'      => Hash::make($request->password),
                'role'          => $request->role,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Akun berhasil dibuat',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'role' => $user->role,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat akun.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
