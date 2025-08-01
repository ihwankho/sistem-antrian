<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
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
    public function show($id)
    {
        try {
            $users = User::findOrFail($id);

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
            $validator = Validator::make($request->all(), [
                'nama'           => 'required|string|max:255',
                'nama_pengguna'  => 'required|string|max:255|unique:users,nama_pengguna',
                'password'       => 'required|string|min:6',
                'role'           => 'required|in:1,2',
                'id_loket'       => 'nullable|exists:lokets,id',
                'foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan foto jika ada
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('user_foto', 'public');
            }

            $user = User::create([
                'nama'          => $request->nama,
                'nama_pengguna' => $request->nama_pengguna,
                'password'      => Hash::make($request->password),
                'role'          => $request->role,
                'id_loket'      => $request->id_loket,
                'foto'          => $fotoPath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Akun berhasil dibuat',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'role' => $user->role,
                    'id_loket' => $user->id_loket,
                    'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
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
                'role'           => 'sometimes|required|in:1,2',
                'id_loket'       => 'sometimes|required|exists:lokets,id',
                'foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update field biasa
            $user->nama = $request->input('nama', $user->nama);
            $user->nama_pengguna = $request->input('nama_pengguna', $user->nama_pengguna);
            if ($request->filled('password')) $user->password = Hash::make($request->password);
            $user->role = $request->input('role', $user->role);
            $user->id_loket = $request->input('id_loket', $user->id_loket);


            // Update foto jika ada
            if ($request->hasFile('foto')) {
                // hapus foto lama jika ada
                if ($user->foto) {
                    Storage::disk('public')->delete($user->foto);
                }
                $user->foto = $request->file('foto')->store('user_foto', 'public');
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Akun berhasil diperbarui',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'role' => $user->role,
                    'id_loket' => $user->id_loket,
                    'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
                ]
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

    public function getUsLok()
    {
        try {
            $users = User::with([
                'loket:id,nama_loket',
                'departemen' => function ($query) {
                    $query->select('departemens.id as departemen_id', 'departemens.nama_departemen', 'departemens.id_loket');
                }
            ])->get();
            $data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nama_pengguna' => $user->nama_pengguna,
                    'role' => $user->role,
                    'nama_loket' => $user->loket->nama_loket ?? null,
                    'nama_departemen' => $user->departemen->nama_departemen ?? null,
                    'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Daftar user dengan nama loket & departemen',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
