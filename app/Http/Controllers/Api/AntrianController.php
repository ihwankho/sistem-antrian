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
            $idLoket = $pelayanan->departemen->loket->id;

            // 4. Tentukan nomor urut terakhir untuk loket ini (per hari)
            $lastQueue = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->whereDate('antrians.created_at', now()->toDateString())
                ->max('nomor_antrian');

            $nomorAntrian = $lastQueue ? $lastQueue + 1 : 1;

            // 5. Simpan data antrian (angka murni)
            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'status_antrian' => 1, // 1 = menunggu
                'id_pengunjung' => $pengunjung->id,
                'id_pelayanan' => $pelayanan->id
            ]);

            // 6. Tentukan kode huruf loket (berdasarkan urutan id ASC)
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets); // posisi loket
            $kodeHuruf = chr(65 + $loketIndex); // A=65, B=66, dst

            // 7. Gabungkan jadi kode antrian final (misalnya A001)
            $kodeAntrian = $kodeHuruf . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT);

            DB::commit();

            // 8. Buat tiket (pakai kode yang sudah digenerate)
            $tiket = [
                'nomor_antrian' => $kodeAntrian,
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
        try {
            // Ambil data antrian berdasarkan loket
            $antrian = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->where('lokets.id', $id_loket)
                ->select(
                    'antrians.id',
                    'antrians.nomor_antrian',
                    'departemens.id_loket',
                    'departemens.nama_departemen',
                    'lokets.nama_loket',
                    'antrians.status_antrian',
                    'antrians.created_at'
                )
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->get();

            if ($antrian->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian untuk loket ini'
                ], 404);
            }

            // Ambil semua ID loket untuk urutan huruf
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();

            // Tambahkan kode huruf + format nomor
            $antrian = $antrian->map(function ($item) use ($lokets) {
                $loketIndex = array_search($item->id_loket, $lokets);
                $kodeHuruf = chr(65 + $loketIndex);
                $item->kode_antrian = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
                unset($item->id_loket);
                return $item;
            });

            return response()->json([
                'status' => true,
                'loket' => $antrian->first()->nama_loket,
                'data' => $antrian
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //semua antrian dari semua loket
    public function getAllAntrian()
    {
        try {
            // Ambil data dasar
            $data = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select(
                    'antrians.id',
                    'antrians.nomor_antrian',
                    'departemens.id_loket',
                    'departemens.nama_departemen',
                    'lokets.nama_loket',
                    'antrians.status_antrian',
                    'antrians.created_at'
                )
                ->orderBy('lokets.id', 'asc')       // urutkan berdasarkan loket dulu
                ->orderBy('antrians.nomor_antrian', 'asc') // lalu nomor antrian
                ->get();

            // Ambil semua ID loket untuk urutan huruf
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();

            // Proses: tambahkan kode huruf + format nomor
            $data = $data->map(function ($item) use ($lokets) {
                $loketIndex = array_search($item->id_loket, $lokets);
                $kodeHuruf = chr(65 + $loketIndex); // A, B, C, ...
                $item->kode_antrian = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
                unset($item->id_loket); // hilangkan kalau tidak mau ditampilkan
                return $item;
            });

            // Kelompokkan per loket biar rapi
            $grouped = $data->groupBy('nama_loket')->map(function ($items, $loket) {
                return [
                    'loket' => $loket,
                    'antrian' => $items->values()
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Data antrian berhasil diambil',
                'data' => $grouped
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
