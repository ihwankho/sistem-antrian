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
use Carbon\Carbon;
use PhpParser\Node\Expr\Cast;
use App\Models\Departemen;
use Illuminate\Support\Facades\Log;

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
                'id' => $antrian->id, // <--- TAMBAHKAN BARIS INI
                'nomor_antrian' => $kodeAntrian,
                'nama_departemen' => $pelayanan->departemen->nama_departemen ?? null,
                'nama_loket' => $pelayanan->departemen->loket->nama_loket ?? null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Tiket antrian berhasil dibuat',
                'data' => $tiket // Sekarang respons sudah membawa ID asli
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
                ->whereDate('antrians.created_at', now()->toDateString())

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

            // Optimasi query - ambil semua antrian dengan status 1 dan 2 sekaligus
            $antrianData = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->whereIn('antrians.status_antrian', [1, 2])
                ->whereDate('antrians.created_at', now()->toDateString())
                ->orderBy('antrians.status_antrian', 'desc') // status 2 dulu, baru 1
                ->orderBy('antrians.nomor_antrian', 'asc')
                ->select('antrians.*')
                ->get();

            // Cek apakah masih ada antrian yang statusnya 2 (sedang dipanggil)
            $currentCalling = $antrianData->where('status_antrian', 2)->first();
            if ($currentCalling) {
                return response()->json([
                    'status' => false,
                    'message' => 'Masih ada antrian yang sedang dipanggil di loket ini. Selesaikan dulu sebelum memanggil berikutnya.'
                ], 409);
            }

            // Ambil antrian berikutnya yang menunggu (status 1)
            $nextAntrian = $antrianData->where('status_antrian', 1)->first();
            if (!$nextAntrian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada antrian menunggu di loket ini'
                ], 404);
            }

            // Update status jadi 2 (dipanggil)
            $nextAntrian->status_antrian = 2;
            $nextAntrian->save();

            // Buat kode huruf loket dengan defensive programming
            $lokets = DB::table('lokets')->orderBy('id', 'ASC')->pluck('id')->toArray();
            $loketIndex = array_search($idLoket, $lokets);

            // Defensive programming untuk array index
            if ($loketIndex === false) {
                throw new \Exception('Loket tidak ditemukan dalam urutan');
            }

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
    
            // Cari antrian yang sedang dipanggil (status = 2) HANYA UNTUK HARI INI
            $currentAntrian = Antrian::join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->where('departemens.id_loket', $idLoket)
                ->where('antrians.status_antrian', 2)
                ->whereDate('antrians.created_at', now()->toDateString()) // <-- INI PERBAIKANNYA
                ->select('antrians.*')
                ->orderBy('antrians.updated_at', 'desc') // Opsional: pengaman tambahan
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
            $kodeAntrianSpasi = implode(' ', str_split($kodeAntrian));
            
            return response()->json([
                'status' => true,
                'message' => 'Antrian berhasil dipanggil ulang',
                'data' => [
                    'id' => $currentAntrian->id,
                    'kode_antrian' => $kodeAntrian,
                    'nomor_antrian' => $currentAntrian->nomor_antrian,
                    'status' => $currentAntrian->status_antrian,
                    'loket' => $kodeLoket,
                    'voice_text' => "Silakan antrian $kodeAntrianSpasi menuju ke loket $kodeLoket"
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

    //ambil data antrian dan pengunjung
    public function ShowPe($id)
    {
        try {
            // ================================================================= //
            // ========== INI BAGIAN YANG DIPERBAIKI (THE FIXED PART) ========== //
            // ================================================================= //
            $antrian = Antrian::with(['pengunjung', 'pelayanan.departemen.loket'])->findOrFail($id);

            return response()->json($antrian);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'data antrian tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    public function laporanBulanan(Request $request)
    {
        try {
            $bulan = $request->input('bulan', Carbon::now()->month);
            $tahun = $request->input('tahun', Carbon::now()->year);

            $data = DB::table('antrians')
                ->selectRaw('DATE(created_at) as tanggal')
                ->selectRaw('COUNT(*) as total_antrian')
                ->selectRaw('SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as selesai')
                ->selectRaw('SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as skip')
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'ASC')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Laporan bulanan berhasil diambil',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getActivityHistory(Request $request)
    {
        try {
            $query = Antrian::join('pengunjungs', 'antrians.id_pengunjung', '=', 'pengunjungs.id')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->join('lokets', 'departemens.id_loket', '=', 'lokets.id')
                ->select(
                    'antrians.created_at as waktu_daftar',
                    'antrians.nomor_antrian',
                    'antrians.status_antrian',
                    'pengunjungs.nama_pengunjung',
                    'pelayanans.nama_layanan',
                    'departemens.nama_departemen',
                    'lokets.nama_loket',
                    'lokets.id as loket_id'
                );

            // Filter tanggal (menggunakan logika asli Anda yang sudah terbukti bekerja)
            if (
                $request->has('start_date') && !empty($request->start_date) &&
                $request->has('end_date') && !empty($request->end_date)
            ) {

                $startDate = $request->start_date . ' 00:00:00';
                $endDate = $request->end_date . ' 23:59:59';

                $query->whereBetween('antrians.created_at', [$startDate, $endDate]);
            } else {
                // Default: hari ini jika tidak ada tanggal
                $today = now()->format('Y-m-d');
                $query->whereDate('antrians.created_at', $today);
            }

            // Filter-filter lainnya
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('departemens.id', $request->department_id);
            }
            if ($request->has('counter_id') && !empty($request->counter_id)) {
                $query->where('lokets.id', $request->counter_id);
            }
            if ($request->has('status') && !empty($request->status)) {
                $query->where('antrians.status_antrian', (int)$request->status);
            }

            $results = $query->orderBy('antrians.created_at', 'desc')->get();

            // Ambil data loket sekali saja untuk efisiensi
            $allLokets = Loket::orderBy('id', 'ASC')->pluck('id')->toArray();

            $formattedResults = $results->map(function ($item) use ($allLokets) {
                // --- INTI PERBAIKAN ---
                // 1. Konversi status angka ke teks
                $statusText = 'Tidak Diketahui';
                switch ((int)$item->status_antrian) {
                    case 1:
                        $statusText = 'Menunggu';
                        break;
                    case 2:
                        $statusText = 'Dipanggil';
                        break;
                    case 3:
                        $statusText = 'Selesai';
                        break;
                    case 4:
                        $statusText = 'Dilewati';
                        break;
                }

                // 2. Generate nomor antrian lengkap
                $nomorAntrianLengkap = 'N/A';
                $loketIndex = array_search($item->loket_id, $allLokets);
                if ($loketIndex !== false) {
                    $kodeHuruf = chr(65 + $loketIndex);
                    $nomorAntrianLengkap = $kodeHuruf . str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT);
                }

                // 3. Mengembalikan array dengan format yang akan dibaca JavaScript
                return [
                    'waktu_daftar' => $item->waktu_daftar,
                    'nomor_antrian_lengkap' => $nomorAntrianLengkap,
                    'nama_pengunjung' => $item->nama_pengunjung,
                    'nama_layanan' => $item->nama_layanan,
                    'nama_departemen' => $item->nama_departemen,
                    'nama_loket' => $item->nama_loket,
                    'status' => $statusText // Key 'status' sekarang berisi TEKS
                ];
            });

            return response()->json($formattedResults);
        } catch (\Exception $e) {
            Log::error('Error in getActivityHistory: ' . $e->getMessage() . ' at line ' . $e->getLine());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getPerformanceStats(Request $request)
    {
        try {
            // Validasi input tanggal
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';

            // 1. KPI: Total Antrian, Jumlah Selesai, Jumlah Dilewati
            $kpi = DB::table('antrians')
                ->select(
                    DB::raw('COUNT(*) as total_antrian'),
                    DB::raw('SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as jumlah_selesai'),
                    DB::raw('SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as jumlah_dilewati')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->first();

            // 2. Grafik Antrian per Hari
            $grafikHarian = DB::table('antrians')
                ->select(
                    DB::raw('DATE(created_at) as tanggal'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('tanggal')
                ->get();

            $labels = [];
            $data = [];
            foreach ($grafikHarian as $harian) {
                $labels[] = $harian->tanggal;
                $data[] = $harian->total;
            }

            // 3. Kinerja Departemen
            $kinerjaDepartemen = DB::table('antrians')
                ->join('pelayanans', 'antrians.id_pelayanan', '=', 'pelayanans.id')
                ->join('departemens', 'pelayanans.id_departemen', '=', 'departemens.id')
                ->select(
                    'departemens.nama_departemen',
                    DB::raw('COUNT(*) as total_antrian'),
                    DB::raw('SUM(CASE WHEN antrians.status_antrian = 3 THEN 1 ELSE 0 END) as jumlah_selesai'),
                    DB::raw('SUM(CASE WHEN antrians.status_antrian = 4 THEN 1 ELSE 0 END) as jumlah_dilewati')
                )
                ->whereBetween('antrians.created_at', [$startDate, $endDate])
                ->groupBy('departemens.id', 'departemens.nama_departemen')
                ->orderBy('departemens.nama_departemen')
                ->get();

            return response()->json([
                'kpi' => [
                    'total_antrian' => (int)$kpi->total_antrian,
                    'jumlah_selesai' => (int)$kpi->jumlah_selesai,
                    'jumlah_dilewati' => (int)$kpi->jumlah_dilewati
                ],
                'grafik_harian' => [
                    'labels' => $labels,
                    'data' => $data
                ],
                'kinerja_departemen' => $kinerjaDepartemen
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPerformanceStats: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}