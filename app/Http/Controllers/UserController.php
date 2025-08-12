<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Tetap gunakan kedua URL API ini
    private $apiUserUrl = 'http://127.0.0.1:8001/api/users'; 
    private $apiLoketUrl = 'http://127.0.0.1:8001/api/lokets';

    public function index()
    {
        try {
            // Gunakan endpoint users-loket untuk mendapatkan data user dengan info loket
            $userResponse = Http::get('http://127.0.0.1:8001/api/users-loket');
            
            $users = [];
            if ($userResponse->successful()) {
                $responseData = $userResponse->json();
                $users = $responseData['data'] ?? [];
            }

            // Untuk dropdown form, kita tetap ambil dari API loket langsung
            $loketResponse = Http::get($this->apiLoketUrl);
            $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];

        } catch (\Exception $e) {
            $users = [];
            $lokets = [];
        }

        return view('user.index', compact('users', 'lokets'));
    }

    public function create()
    {
        // Ambil data loket langsung dari API loket
        $loketResponse = Http::get($this->apiLoketUrl);
        $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];
        
        return view('user.create', compact('lokets'));
    }

    public function store(Request $request)
    {
        // Validasi input termasuk foto
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:1,2',
            'id_loket' => 'required_if:role,2|exists:lokets,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Siapkan data untuk dikirim ke API
        $data = $request->only(['nama', 'nama_pengguna', 'password', 'password_confirmation', 'role', 'id_loket']);

        // Jika ada foto, siapkan untuk multipart upload
        if ($request->hasFile('foto')) {
            $response = Http::attach('foto', $request->file('foto')->get(), $request->file('foto')->getClientOriginalName())
                           ->post($this->apiUserUrl, $data);
        } else {
            $response = Http::post($this->apiUserUrl, $data);
        }

        if ($response->successful()) {
            return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
        }
        
        return back()->withErrors($response->json()['errors'] ?? ['Terjadi kesalahan'])->withInput();
    }

    public function edit($id)
    {
        // Ambil data user spesifik
        $userResponse = Http::get("{$this->apiUserUrl}/{$id}"); 
        if ($userResponse->failed()) {
            return redirect()->route('pengguna.index')->with('error', 'Pengguna tidak ditemukan.');
        }
        
        // Ambil data loket langsung dari API loket
        $loketResponse = Http::get($this->apiLoketUrl);
        $user = $userResponse->json()['data'];
        $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];

        return view('user.edit', compact('user', 'lokets'));
    }

    public function update(Request $request, $id)
    {
        // Validasi input termasuk foto
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:1,2',
            'id_loket' => 'required_if:role,2',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Siapkan data untuk dikirim ke API
        $data = $request->only(['nama', 'nama_pengguna', 'role', 'id_loket']);
        
        // Hanya kirim password jika diisi
        if ($request->filled('password')) {
            $data['password'] = $request->password;
            $data['password_confirmation'] = $request->password_confirmation;
        }

        // Jika ada foto, siapkan untuk multipart upload
        if ($request->hasFile('foto')) {
            $response = Http::attach('foto', $request->file('foto')->get(), $request->file('foto')->getClientOriginalName())
                           ->put("{$this->apiUserUrl}/{$id}", $data);
        } else {
            $response = Http::put("{$this->apiUserUrl}/{$id}", $data);
        }

        if ($response->successful()) {
            return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
        }
        
        return back()->withErrors($response->json()['errors'] ?? ['Terjadi kesalahan'])->withInput();
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiUserUrl}/{$id}");
        if ($response->successful()) {
            return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
        }
        return redirect()->route('pengguna.index')->with('error', 'Gagal menghapus pengguna.');
    }
}