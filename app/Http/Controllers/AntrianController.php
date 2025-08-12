<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AntrianController extends Controller
{
    private $apiAntrianUrl;
    private $apiPelayananUrl;

    public function __construct()
    {
        $this->apiAntrianUrl = env('API_BASE_URL') . '/antrian';
        $this->apiPelayananUrl = env('API_BASE_URL') . '/pelayanan';
    }

    /**
     * Menampilkan halaman untuk memilih layanan.
     */
    public function pilihLayanan()
    {
        $pelayananGrouped = [];

        try {
            $response = Http::get($this->apiPelayananUrl);

            if ($response->successful() && ($response['status'] === true)) {
                $pelayananList = $response['data'];

                // Kelompokkan berdasarkan nama departemen
                foreach ($pelayananList as $layanan) {
                    $departemen = $layanan['departemen']['nama_departemen'] ?? 'Layanan Lainnya';
                    $pelayananGrouped[$departemen][] = $layanan;
                }
            } else {
                return back()->with('error', 'Gagal mengambil data layanan.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Tidak dapat terhubung ke server layanan saat ini.');
        }

        return view('antrian.pilih-layanan', compact('pelayananGrouped'));
    }

    /**
     * Menampilkan form pengisian data berdasarkan layanan yang dipilih.
     */
    public function isiData(Request $request)
    {
        $request->validate([
            'id_pelayanan' => 'required|numeric'
        ]);

        try {
            $response = Http::get("{$this->apiPelayananUrl}/{$request->id_pelayanan}");

            if ($response->successful() && ($response['status'] === true)) {
                $layanan = $response['data'];
                return view('antrian.isi-data', compact('layanan'));
            }

            return redirect()->route('antrian.pilih-layanan')->with('error', 'Layanan tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->route('antrian.pilih-layanan')->with('error', 'Gagal terhubung ke server.');
        }
    }

    /**
     * Mengirim data ke API untuk membuat tiket antrian.
     */
    public function buatTiket(Request $request)
    {
        $request->validate([
            'nama_pengunjung' => 'required|string',
            'nik' => 'required|numeric',
            'no_hp' => 'required|string',
            'jenis_kelamin' => 'required|string',
            'alamat' => 'required|string',
            'id_pelayanan' => 'required|numeric',
            'foto_ktp' => 'required|image|max:2048',
            'foto_wajah' => 'required|image|max:2048',
        ]);
    
        try {
            $fotoKtp = $request->file('foto_ktp');
            $fotoWajah = $request->file('foto_wajah');
    
            $multipartData = [
                ['name' => 'nama_pengunjung', 'contents' => $request->nama_pengunjung],
                ['name' => 'nik', 'contents' => $request->nik],
                ['name' => 'no_hp', 'contents' => $request->no_hp],
                ['name' => 'jenis_kelamin', 'contents' => $request->jenis_kelamin],
                ['name' => 'alamat', 'contents' => $request->alamat],
                ['name' => 'id_pelayanan', 'contents' => $request->id_pelayanan],
                [
                    'name' => 'foto_ktp',
                    'contents' => fopen($fotoKtp->getRealPath(), 'r'),
                    'filename' => $fotoKtp->getClientOriginalName(),
                    'headers' => ['Content-Type' => $fotoKtp->getMimeType()],
                ],
                [
                    'name' => 'foto_wajah',
                    'contents' => fopen($fotoWajah->getRealPath(), 'r'),
                    'filename' => $fotoWajah->getClientOriginalName(),
                    'headers' => ['Content-Type' => $fotoWajah->getMimeType()],
                ],
            ];
    
            $response = Http::asMultipart()->post($this->apiAntrianUrl, $multipartData);
    
            if ($response->successful() && isset($response['status']) && $response['status'] === true) {
                return redirect()->route('antrian.tiket')->with('tiket', $response['data']);
            }
    
            return back()->withErrors($response['errors'] ?? ['Terjadi kesalahan saat membuat tiket'])->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server.')->withInput();
        }
    }
    

    /**
     * Menampilkan halaman tiket yang sudah dibuat.
     */
    public function tampilTiket()
    {
        $tiket = session('tiket');

        if (!$tiket) {
            return redirect()->route('landing.page');
        }

        return view('antrian.tiket', compact('tiket'));
    }
}
