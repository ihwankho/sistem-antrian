<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PelayananController extends Controller
{
    public function index()
    {
        try {
            $data = Pelayanan::with('departemen')->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pelayanan berhasil diambil',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_layanan' => 'required|string|max:255',
                'id_departemen' => 'required|exists:departemens,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pelayanan = Pelayanan::create([
                'nama_layanan' => $request->nama_layanan,
                'id_departemen' => $request->id_departemen
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pelayanan berhasil ditambahkan',
                'data' => $pelayanan
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan pelayanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pelayanan = Pelayanan::with('departemen')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Data ditemukan',
                'data' => $pelayanan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pelayanan = Pelayanan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_layanan' => 'sometimes|required|string|max:255',
                'id_departemen' => 'sometimes|required|exists:departemens,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('nama_layanan')) {
                $pelayanan->nama_layanan = $request->nama_layanan;
            }
            if ($request->has('id_departemen')) {
                $pelayanan->id_departemen = $request->id_departemen;
            }

            $pelayanan->save();

            return response()->json([
                'status' => true,
                'message' => 'Pelayanan berhasil diperbarui',
                'data' => $pelayanan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pelayanan = Pelayanan::findOrFail($id);
            $pelayanan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pelayanan berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPelaDep()
    {
        try {
            $pelayanan = Pelayanan::with('departemen:id,nama_departemen')->get();

            $data = $pelayanan->map(function ($pelay) {
                return [
                    'id' => $pelay->id,
                    'nama_layanan' => $pelay->nama_layanan,
                    'nama_departemen' => $pelay->departemen->nama_departemen ?? null
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'Daftar pelayanan dengan nama departemen berhasil ditemukan',
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
