<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departemen;
use Exception;
use Illuminate\Support\Facades\Validator;

class DepartemenController extends Controller
{
    public function index()
    {
        try {
            $data = Departemen::all();
            return response()->json([
                'status' => true,
                'message' => 'Data departemen berhasil diambil',
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
        $validator = Validator::make($request->all(), [
            'nama_departemen' => 'required|string|unique:departemens,nama_departemen',
            'id_loket' => 'required|exists:lokets,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $departemen = Departemen::create([
            'nama_departemen' => $request->nama_departemen,
            'id_loket' => $request->id_loket,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Departemen berhasil ditambahkan',
            'data' => $departemen
        ], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $departemen = Departemen::findOrFail($id);

            // Validasi fleksibel: hanya jika field dikirim
            $validated = $request->validate([
                'nama_departemen' => 'sometimes|string|unique:departemens,nama_departemen,' . $id . ',id',
                'id_loket' => 'sometimes|exists:lokets,id'
            ]);

            // Update hanya field yang dikirim
            $departemen->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Departemen berhasil diperbarui',
                'data' => $departemen
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat update',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $departemen = Departemen::findOrFail($id);
            $departemen->delete();

            return response()->json([
                'status' => true,
                'message' => 'Departemen berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $departemen = Departemen::findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Data departemen ditemukan',
                'data' => $departemen
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Departemen tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function getDepLok()
    {
        try {
            $departemen = Departemen::with('loket:id,nama_loket')->get();
            $data = $departemen->map(function ($depart) {
                return [
                    'id' => $depart->id,
                    'nama_departemen' => $depart->nama_departemen,
                    'nama_loket' => $depart->loket->nama_loket ?? null
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'data berhasil ditemukan',
                'data' => $data

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'data tidak ditemukan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
