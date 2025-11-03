<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    /**
     * Menampilkan halaman edit profil untuk petugas yang sedang login.
     */
    public function edit()
    {
        try {
            $token = Session::get('token');
            $id = Auth::id();

            $response = Http::withToken($token)->get($this->apiBaseUrl . "/users/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->failed()) {
                Log::error("Gagal mengambil data user $id: " . $response->body());
                return redirect()->route('dashboard')->with('error', 'Gagal memuat data profil.');
            }

            $user = $response->json('data', []);
            return view('profile.edit', compact('user'));

        } catch (\Exception $e) {
            Log::error('Exception di ProfileController@edit: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    /**
     * Memperbarui profil petugas yang sedang login.
     */
    public function update(Request $request)
    {
        $token = Session::get('token');
        $id = Auth::id();

        // 1. Validasi Input (termasuk validasi manual 'tanpa fileinfo')
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nama_pengguna' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'nama_pengguna')->ignore($id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'foto' => 'nullable|file|max:2048', // Validasi ukuran file
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $validatedData = $validator->validated();
            $fotoFile = $request->hasFile('foto') ? $request->file('foto') : null;

            // 2. Validasi Ekstensi & Tipe Gambar (manual tanpa fileinfo)
            if ($fotoFile) {
                $allowedExt = ['jpg', 'jpeg', 'png'];
                $ext = strtolower($fotoFile->getClientOriginalExtension());

                if (!in_array($ext, $allowedExt)) {
                    return back()->withErrors(['foto' => 'Ekstensi file tidak diizinkan. Hanya JPG, JPEG, atau PNG.'])->withInput();
                }

                if (!@getimagesize($fotoFile->getRealPath())) {
                    return back()->withErrors(['foto' => 'File bukan gambar yang valid.'])->withInput();
                }
            }

            // 3. Menyiapkan Data untuk API
            if (empty($validatedData['password'])) {
                unset($validatedData['password']);
                if (isset($validatedData['password_confirmation'])) {
                    unset($validatedData['password_confirmation']);
                }
            }
            $validatedData['_method'] = 'PUT';

            // 4. Mengirim data ke API (termasuk file jika ada)
            $http = Http::withToken($token);

            if ($fotoFile) {
                $http->attach('foto', $fotoFile->get(), $fotoFile->getClientOriginalName());
            }

            $response = $http->post($this->apiBaseUrl . "/users/{$id}", $validatedData);

            // 5. Menangani Respons API
            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
            }

            if ($response->successful()) {
                // Jika berhasil, perbarui data user di sesi lokal
                $apiUser = $response->json('data');
                $localUser = Auth::user();
                $localUser->nama = $apiUser['nama'] ?? $localUser->nama;
                $localUser->nama_pengguna = $apiUser['nama_pengguna'] ?? $localUser->nama_pengguna;
                $localUser->foto = $apiUser['foto_path'] ?? $localUser->foto; // Asumsi API mengembalikan 'foto_path'
                $localUser->save();

                return redirect()->route('profil.edit')->with('success', 'Profil berhasil diperbarui.');
            }

            // Jika validasi dari API gagal
            Log::error('Gagal update profil (API): ' . $response->body());
            return back()->withErrors($response->json('errors', ['error' => 'Gagal memperbarui profil.']))->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di ProfileController@update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }
}