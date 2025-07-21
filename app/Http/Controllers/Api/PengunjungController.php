<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengunjung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PengunjungController extends Controller
{
    public function index()
    {
        try {
            $data = Pengunjung::all();
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_pengunjung' => 'required|string|max:255',
                'nik' => 'required|digits:16|unique:pengunjungs',
                'no_hp' => 'required|string|max:20',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'alamat' => 'required|string',
                'foto_ktp' => 'nullable|string',
                'foto_wajah' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pengunjung = Pengunjung::create($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Data pengunjung berhasil ditambahkan',
                'data' => $pengunjung,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pengunjung = Pengunjung::find($id);

            if (!$pengunjung) {
                return response()->json(['message' => 'Pengunjung tidak ditemukan'], 404);
            }

            return response()->json($pengunjung, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pengunjung = Pengunjung::find($id);

            if (!$pengunjung) {
                return response()->json(['message' => 'Pengunjung tidak ditemukan'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nama_pengunjung' => 'sometimes|required|string|max:255',
                'nik' => 'sometimes|required|digits:16|unique:pengunjungs,nik,' . $id,
                'no_hp' => 'sometimes|required|string|max:20',
                'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
                'alamat' => 'sometimes|required|string',
                'foto_ktp' => 'nullable|string',
                'foto_wajah' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pengunjung->update($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Data pengunjung berhasil diperbarui',
                'data' => $pengunjung,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pengunjung = Pengunjung::find($id);

            if (!$pengunjung) {
                return response()->json(['message' => 'Pengunjung tidak ditemukan'], 404);
            }

            $pengunjung->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data pengunjung berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
