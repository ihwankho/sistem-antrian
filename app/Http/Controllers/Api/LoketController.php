<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class LoketController extends Controller
{
    // GET /lokets
    public function index()
    {
        try {
            $lokets = Loket::all();

            return response()->json([
                'status' => true,
                'message' => 'Data loket berhasil diambil',
                'data' => $lokets
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /lokets
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_loket' => 'required|string|max:255|unique:lokets,nama_loket',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $loket = Loket::create([
                'nama_loket' => $request->nama_loket
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Loket berhasil ditambahkan',
                'data' => $loket
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan loket.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /lokets/{id}
    public function show($id)
    {
        try {
            $loket = Loket::with([
                'departemens:id,id_loket,nama_departemen',
                'pelayanans' => function ($query) {
                    $query->select('pelayanans.id', 'pelayanans.nama_layanan', 'pelayanans.id_departemen');
                }
            ])->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Data loket berhasil diambil',
                'data' => $loket
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Loket tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    // PUT /lokets/{id}
    public function update(Request $request, $id)
    {
        try {
            $loket = Loket::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_loket' => 'required|string|max:255|unique:lokets,nama_loket,' . $loket->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $loket->nama_loket = $request->nama_loket;
            $loket->save();

            return response()->json([
                'status' => true,
                'message' => 'Loket berhasil diperbarui',
                'data' => $loket
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui loket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /lokets/{id}
    public function destroy($id)
    {
        try {
            $loket = Loket::findOrFail($id);
            $loket->delete();

            return response()->json([
                'status' => true,
                'message' => 'Loket berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus loket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
