<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoketController extends Controller
{
    // Tentukan base URL untuk API Anda
    protected $apiUrl = 'http://127.0.0.1:8001/api/lokets';

    public function index()
    {
        // Panggil API GET /lokets
        $response = Http::get($this->apiUrl);
        $lokets = [];

        if ($response->successful()) {
            $lokets = $response->json()['data']; // Ambil data dari response JSON
        }

        return view('loket.index', compact('lokets'));
    }

    public function create()
    {
        return view('loket.create');
    }

    public function store(Request $request)
    {
        // Panggil API POST /lokets
        $response = Http::post($this->apiUrl, [
            'nama_loket' => $request->nama_loket,
        ]);

        if ($response->successful()) {
            return redirect()->route('loket.index')->with('success', 'Loket berhasil ditambahkan.');
        } else {
            // Ambil pesan error dari API jika validasi gagal
            $errors = $response->json()['errors'] ?? ['nama_loket' => ['Gagal menambahkan loket.']];
            return redirect()->back()->withErrors($errors)->withInput();
        }
    }

    public function edit($id)
    {
        // Panggil API GET /lokets/{id}
        $response = Http::get("{$this->apiUrl}/{$id}");
        
        if ($response->successful()) {
            $loket = $response->json()['data'];
            return view('loket.edit', compact('loket'));
        } else {
            return redirect()->route('loket.index')->with('error', 'Loket tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        // Panggil API PUT /lokets/{id}
        $response = Http::put("{$this->apiUrl}/{$id}", [
            'nama_loket' => $request->nama_loket,
        ]);

        if ($response->successful()) {
            return redirect()->route('loket.index')->with('success', 'Loket berhasil diperbarui.');
        } else {
            $errors = $response->json()['errors'] ?? ['nama_loket' => ['Gagal memperbarui loket.']];
            return redirect()->back()->withErrors($errors)->withInput();
        }
    }

    public function destroy($id)
    {
        // Panggil API DELETE /lokets/{id}
        $response = Http::delete("{$this->apiUrl}/{$id}");

        if ($response->successful()) {
            return redirect()->route('loket.index')->with('success', 'Loket berhasil dihapus.');
        } else {
            return redirect()->route('loket.index')->with('error', 'Gagal menghapus loket.');
        }
    }
}