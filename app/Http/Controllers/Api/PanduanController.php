<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PanduanController extends Controller
{
    public function index()
    {
        $panduans = Panduan::all();
        return response()->json([
            'status' => true,
            'message' => 'Daftar panduan',
            'data' => $panduans
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isi_panduan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $panduan = Panduan::create($request->only('isi_panduan'));

        return response()->json([
            'status' => true,
            'message' => 'Panduan berhasil dibuat',
            'data' => $panduan
        ], 201);
    }

    public function show($id)
    {
        $panduan = Panduan::find($id);
        if (!$panduan) {
            return response()->json([
                'status' => false,
                'message' => 'Panduan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail panduan',
            'data' => $panduan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $panduan = Panduan::find($id);
        if (!$panduan) {
            return response()->json([
                'status' => false,
                'message' => 'Panduan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'isi_panduan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $panduan->update($request->only('isi_panduan'));

        return response()->json([
            'status' => true,
            'message' => 'Panduan berhasil diperbarui',
            'data' => $panduan
        ], 200);
    }

    public function destroy($id)
    {
        $panduan = Panduan::find($id);
        if (!$panduan) {
            return response()->json([
                'status' => false,
                'message' => 'Panduan tidak ditemukan',
            ], 404);
        }

        $panduan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Panduan berhasil dihapus',
        ], 200);
    }
}
