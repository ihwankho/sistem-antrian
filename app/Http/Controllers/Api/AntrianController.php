<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Antrian;
use App\Models\Pengunjung;
use App\Models\Pelayanan;
use App\Models\Loket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast;


class AntrianController extends Controller
{
    //Menambah antrian
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Validasi input (tambahkan validasi foto)
            $validated = $request->validate([
                'nama_pengunjung' => 'required|string|max:255',
                'nik' => 'required|string|max:16',
                'no_hp' => 'nullable|string|max:15',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'alamat' => 'nullable|string',
                'id_pelayanan' => 'required|exists:pelayanans,id',
                'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'foto_wajah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // 2. Simpan file jika ada
            $fotoKtpPath = null;
            $fotoWajahPath = null;

            if ($request->hasFile('foto_ktp')) {
                $fotoKtpPath = $request->file('foto_ktp')->store('ktp', 'public');
            }
            if ($request->hasFile('foto_wajah')) {
                $fotoWajahPath = $request->file('foto_wajah')->store('wajah', 'public');
            }

            // 3. Simpan data pengunjung
            $pengunjung = Pengunjung::create([
                'nama_pengunjung' => $validated['nama_pengunjung'],
                'nik' => $validated['nik'],
                'no_hp' => $validated['no_hp'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'foto_ktp' => $fotoKtpPath,
                'foto_wajah' => $fotoWajahPath
            ]);

            // 4. Ambil pelayanan + relasinya
            $pelayanan = Pelayanan::with('departemen.loket')->findOrFail($validated['id_pelayanan']);
            $idLoket = $pelayanan->departemen->loket->id;

            // 5. Tentukan nomor urut terakhir untuk loket ini (per hari)
            $lastQueue = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->whereDate('antrians.created_at', now()->toDateString())
                ->max('nomor_antrian');

            $nomorAntrian = $lastQueue ? $lastQueue + 1 : 1;

            // 6. Simpan data antrian (angka murni)
            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'status_antrian' => 1, // 1 = menunggu
                'id_pengunjung' => $pengunjung->id,
                'id_pelayanan' => $pelayanan->id
            ]);

            // 7. Tentukan kode huruf loket (berdasarkan urutan id ASC)
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets); // posisi loket
            $kodeHuruf = chr(65 + $loketIndex); // A=65, B=66, dst

            // 8. Gabungkan jadi kode antrian final (misalnya A001)
            $kodeAntrian = $kodeHuruf . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT);

            DB::commit();

            // 9. Buat tiket (pakai kode yang sudah digenerate)
            $tiket = [
                'nomor_antrian' => $kodeAntrian,
                'nama_departemen' => $pelayanan->departemen->nama_departemen ?? null,
                'nama_loket' => $pelayanan->departemen->loket->nama_loket ?? null,
                'foto_ktp' => $pengunjung->foto_ktp ? asset('storage/' . $pengunjung->foto_ktp) : null,
                'foto_wajah' => $pengunjung->foto_wajah ? asset('storage/' . $pengunjung->foto_wajah) : null,
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

    //Panggil Antrian
    public function callNextAntrian(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'id_loket' => 'required|exists:lokets,id',
            ]);

            $idLoket = $validated['id_loket'];

            // Ambil info loket
            $loket = Loket::findOrFail($idLoket);

            // Cek apakah masih ada antrian yang statusnya 2 (sedang dipanggil) di loket ini
            $currentCalling = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->where('antrians.status_antrian', 2)
                ->first();

            if ($currentCalling) {
                return response()->json([
                    'status' => false,
                    'message' => 'Masih ada antrian yang sedang dipanggil di loket ini. Selesaikan dulu sebelum memanggil berikutnya.'
                ], 409);
            }

            // Ambil antrian berikutnya yang menunggu (status 1)
            $nextAntrian = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->where('antrians.status_antrian', 1)
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->select('antrians.*')
                ->first();

            if (!$nextAntrian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian menunggu di loket ini'
                ], 404);
            }

            // Update status jadi 2 (dipanggil)
            $nextAntrian->status_antrian = 2;
            $nextAntrian->save();

            // Buat kode huruf loket
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets);
            $kodeHuruf = chr(65 + $loketIndex);
            $kodeAntrian = $kodeHuruf . str_pad($nextAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);
            $kodeAntrianSpasi = implode(' ', str_split($kodeAntrian));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dipanggil',
                'data' => [
                    'id' => $nextAntrian->id,
                    'kode_antrian' => $kodeAntrian,
                    'nomor_antrian' => $nextAntrian->nomor_antrian,
                    'status' => $nextAntrian->status_antrian,
                    'loket' => $loket->nama_loket, // nama loket asli
                    'voice_text' => "Silakan antrian $kodeAntrianSpasi menuju ke loket $loket->nama_loket"
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memanggil antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function finishAntrian(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validasi input
            $validated = $request->validate([
                'id_antrian' => 'required|exists:antrians,id',
            ]);

            // Cari antrian
            $antrian = Antrian::find($validated['id_antrian']);

            if (!$antrian || $antrian->status_antrian != 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Antrian tidak ditemukan atau tidak sedang dipanggil'
                ], 404);
            }

            // Update status jadi 3 (selesai)
            $antrian->status_antrian = 3;
            $antrian->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil diselesaikan',
                'data' => [
                    'id' => $antrian->id,
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'status' => $antrian->status_antrian
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyelesaikan antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //Skip Antrian
    public function SkipAntrian(request $request)
    {
        $request->validate([
            'id_antrian' => 'required|exists:antrians,id',
        ]);
        try {
            $antrian = DB::transaction(function () use ($request) {
                $antrian = Antrian::findOrFail($request->id_antrian);
                if ($antrian->status_antrian != 2) {
                    abort(404, 'antrian tidak ditemukan atau tidak sedang dipanggil');
                }
                $antrian->update([
                    'status_antrian' => 4
                ]);
                return $antrian;
            });
            return response()->json([
                'status' => true,
                'message' => 'antrian berhasil dilewati',
                'data' => [
                    'id' => $antrian->id,
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'status' => $antrian->status_antrian
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'gagal melewati antrian',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    //Panggil ulang antrian
    public function recallAntrian(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_loket' => 'required|exists:lokets,id',
            ]);

            $idLoket = $validated['id_loket'];

            // Cari antrian yang sedang dipanggil (status = 2)
            $currentAntrian = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->where('antrians.status_antrian', 2)
                ->select('antrians.*')
                ->first();

            if (!$currentAntrian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian yang sedang dipanggil untuk dipanggil ulang.'
                ], 404);
            }

            // Ambil nama loket
            $loket = Loket::find($idLoket);
            $kodeLoket = $loket->nama_loket ?? 'Loket';

            // Ambil kode huruf (A, B, dst) untuk id loket
            $lokets = Loket::orderBy('id')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets);
            $kodeHuruf = chr(65 + $loketIndex);
            $kodeAntrian = $kodeHuruf . str_pad($currentAntrian->nomor_antrian, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dipanggil ulang',
                'data' => [
                    'id' => $currentAntrian->id,
                    'kode_antrian' => $kodeAntrian,
                    'nomor_antrian' => $currentAntrian->nomor_antrian,
                    'status' => $currentAntrian->status_antrian,
                    'loket' => $kodeLoket,
                    'voice_text' => "Silakan antrian $kodeAntrian menuju $kodeLoket"
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memanggil ulang antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAntrianDipanggil()
    {
        try {
            // Ambil semua loket
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->get();

            // Ambil antrian yang sedang dipanggil (status = 2)
            $antrians = DB::table('antrians')
                ->where('antrians.status_antrian', 2)
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select(
                    'antrians.nomor_antrian',
                    'lokets.id as id_loket',
                    'lokets.nama_loket'
                )
                ->get();

            // Gabungkan data loket dan antrian yang sedang dipanggil
            $result = $lokets->map(function ($loket, $index) use ($antrians) {
                $antrian = $antrians->firstWhere('id_loket', $loket->id);

                $kodeHuruf = chr(65 + $index); // A, B, C, ...
                $kodeAntrian = $antrian
                    ? $kodeHuruf . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT)
                    : null;

                return [
                    'loket' => $loket->nama_loket,
                    'kode_antrian' => $kodeAntrian
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Data antrian yang sedang dipanggil berhasil diambil',
                'data' => $result
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data antrian yang sedang dipanggil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
