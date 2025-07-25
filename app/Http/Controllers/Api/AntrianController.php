<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Antrian;
use App\Models\Pengunjung;
use App\Models\Pelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AntrianController extends Controller
{
    // Daftar antrian
    public function index()
    {
        $antrian = Antrian::with([
            'pengunjung:id,nama_pengunjung,nik,no_hp',
            'pelayanan:id,nama_layanan,id_departemen',
            'pelayanan.departemen:id,nama_departemen,id_loket',
            'pelayanan.departemen.loket:id,nama_loket'
        ])->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar antrian',
            'data' => $antrian
        ]);
    }

    //Menambah antrian
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Validasi input
            $validated = $request->validate([
                'nama_pengunjung' => 'required|string|max:255',
                'nik' => 'required|string|max:16',
                'no_hp' => 'nullable|string|max:15',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'alamat' => 'nullable|string',
                'id_pelayanan' => 'required|exists:pelayanans,id',
            ]);

            // 2. Simpan data pengunjung
            $pengunjung = Pengunjung::create([
                'nama_pengunjung' => $validated['nama_pengunjung'],
                'nik' => $validated['nik'],
                'no_hp' => $validated['no_hp'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null
            ]);

            // 3. Ambil pelayanan + relasinya
            $pelayanan = Pelayanan::with('departemen.loket')->findOrFail($validated['id_pelayanan']);

            // 4. Tentukan nomor antrian baru
            $lastQueue = Antrian::where('id_pelayanan', $pelayanan->id)->max('nomor_antrian');
            $nomorAntrian = $lastQueue ? $lastQueue + 1 : 1;

            // 5. Simpan data antrian
            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'status_antrian' => 1, // 1 = menunggu
                'id_pengunjung' => $pengunjung->id,
                'id_pelayanan' => $pelayanan->id
            ]);

            DB::commit();

            // 6. Buat tiket
            $tiket = [
                'nomor_antrian' => $antrian->nomor_antrian,
                'nama_departemen' => $pelayanan->departemen->nama_departemen ?? null,
                'nama_loket' => $pelayanan->departemen->loket->nama_loket ?? null
            ];

            return response()->json([
                'status' => true,
                'message' => 'Tiket antrian berhasil dibuat',
                'data' => $tiket
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat tiket antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Daftar antrian berdasarkan loket
    public function getByLoket($id_loket)
    {
        $antrian = Antrian::with([
            'pelayanan.departemen.loket',
            'pengunjung'
        ])
            ->whereHas('pelayanan.departemen.loket', function ($q) use ($id_loket) {
                $q->where('id', $id_loket);
            })
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        return response()->json([
            'loket' => $antrian->first()?->pelayanan->departemen->loket->nama_loket,
            'antrian' => $antrian->map(function ($a) {
                return [
                    'id' => $a->id,
                    'nomor' => $a->nomor_antrian,
                    'nama_pengunjung' => $a->pengunjung->nama_pengunjung,
                    'status' => $a->status,
                    'waktu_ambil' => $a->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    //semua antrian dari semua loket
    public function getAllAntrian()
    {
        try {
            $data = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select(
                    'antrians.id',
                    'antrians.nomor_antrian',
                    'departemens.nama_departemen',
                    'lokets.nama_loket',
                    'antrians.status_antrian',
                    'antrians.created_at'
                )
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data antrian berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
