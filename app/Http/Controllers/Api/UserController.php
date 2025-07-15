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
    public function index()
    {
        try {
            $users = User::all();

            return response()->json([
                'status' => true,
                'message' => 'Data pengguna berhasil diambil',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
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
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama'           => 'sometimes|required|string|max:255',
                'nama_pengguna'  => 'sometimes|required|string|max:255|unique:users,nama_pengguna,' . $user->id,
                'password'       => 'nullable|string|min:6',
                'role'           => 'sometimes|required|in:admin,petugas',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update hanya jika ada
            if ($request->has('nama')) $user->nama = $request->nama;
            if ($request->has('nama_pengguna')) $user->nama_pengguna = $request->nama_pengguna;
            if ($request->filled('password')) $user->password = Hash::make($request->password);
            if ($request->has('role')) $user->role = $request->role;

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Akun berhasil diperbarui',
                'data' => $user
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui akun.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete user
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Akun berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menghapus akun.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
