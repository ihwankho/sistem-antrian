<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();

            $users->each(function($user) {
                $user->foto_url = $user->foto ? asset($user->foto) : null;
            });

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
            $user = User::findOrFail($id); 
            $user->foto_url = $user->foto ? asset($user->foto) : null;

            return response()->json([
                'status' => true,
                'message' => 'Data pengguna berhasil diambil',
                'data' => $user
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
            // --- PERUBAHAN: Validasi 'nama_pengguna' ditambahkan rule 'email' ---
            $validator = Validator::make($request->all(), [
                'nama'           => 'required|string|max:255',
                'nama_pengguna'  => 'required|string|email|max:255|unique:users,nama_pengguna', // <-- PERUBAHAN
                'password'       => 'required|string|min:6',
                'role'           => 'required|in:1,2',
                'id_loket'       => 'nullable|exists:lokets,id',
                'foto'           => 'nullable|file|max:2048', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                
                $allowedExt = ['jpg', 'jpeg', 'png'];
                $ext = strtolower($foto->getClientOriginalExtension());
                if (!in_array($ext, $allowedExt)) {
                     throw new \Exception('Ekstensi file tidak diizinkan. Hanya JPG atau PNG.');
                }
                if (!@getimagesize($foto->getRealPath())) {
                    throw new \Exception('File yang diunggah bukan gambar yang valid.');
                }
                
                $folder = 'images';
                $filename = 'user_' . time() . '_' . Str::random(5) . '.' . $ext;
                $foto->move(public_path($folder), $filename);
                $fotoPath = $folder . '/' . $filename; 
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
                    'foto' => $user->foto ? asset($user->foto) : null,
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

            // --- PERUBAHAN: Validasi 'nama_pengguna' ditambahkan rule 'email' ---
            $validator = Validator::make($request->all(), [
                'nama'           => 'sometimes|required|string|max:255',
                'nama_pengguna'  => 'sometimes|required|string|email|max:255|unique:users,nama_pengguna,' . $user->id, // <-- PERUBAHAN
                'password'       => 'nullable|string|min:6',
                'role'           => 'sometimes|required|in:1,2',
                'id_loket'       => 'sometimes|required|exists:lokets,id',
                'foto'           => 'nullable|file|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->nama = $request->input('nama', $user->nama);
            $user->nama_pengguna = $request->input('nama_pengguna', $user->nama_pengguna);
            if ($request->filled('password')) $user->password = Hash::make($request->password);
            $user->role = $request->input('role', $user->role);
            $user->id_loket = $request->input('id_loket', $user->id_loket);

            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                
                $allowedExt = ['jpg', 'jpeg', 'png'];
                $ext = strtolower($foto->getClientOriginalExtension());
                if (!in_array($ext, $allowedExt)) {
                     throw new \Exception('Ekstensi file tidak diizinkan. Hanya JPG atau PNG.');
                }
                if (!@getimagesize($foto->getRealPath())) {
                    throw new \Exception('File yang diunggah bukan gambar yang valid.');
                }

                if ($user->foto && file_exists(public_path($user->foto))) {
                    @unlink(public_path($user->foto));
                }
                
                $folder = 'images'; 
                $filename = 'user_' . time() . '_' . Str::random(5) . '.' . $ext;
                $foto->move(public_path($folder), $filename);
                $user->foto = $folder . '/' . $filename;
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
                    'foto' => $user->foto ? asset($user->foto) : null,
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


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->foto && file_exists(public_path($user->foto))) {
                @unlink(public_path($user->foto));
            }
            
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
                    'foto' => $user->foto ? asset($user->foto) : null,
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