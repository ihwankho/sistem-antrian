<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PelayananController extends Controller
{
    private $apiLayananUrl = 'http://127.0.0.1:8001/api/pelayanan'; // Sesuaikan port jika perlu
    private $apiDepartemenUrl = 'http://127.0.0.1:8001/api/departemen';

    public function index()
    {
        $response = Http::get($this->apiLayananUrl);
        $pelayanan = $response->successful() ? $response->json()['data'] : [];
        return view('pelayanan.index', compact('pelayanan'));
    }

    public function create()
    {
        $departemenResponse = Http::get($this->apiDepartemenUrl);
        $departemens = $departemenResponse->successful() ? $departemenResponse->json()['data'] : [];
        return view('pelayanan.create', compact('departemens'));
    }

    public function store(Request $request)
    {
        $response = Http::post($this->apiLayananUrl, $request->all());
        if ($response->successful()) {
            return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil ditambahkan.');
        }
        return back()->withErrors($response->json()['errors'] ?? ['Terjadi kesalahan'])->withInput();
    }

    public function edit($id)
    {
        $layananResponse = Http::get("{$this->apiLayananUrl}/{$id}");
        $departemenResponse = Http::get($this->apiDepartemenUrl);

        if ($layananResponse->failed()) {
            return redirect()->route('pelayanan.index')->with('error', 'Layanan tidak ditemukan.');
        }

        $layanan = $layananResponse->json()['data'];
        $departemens = $departemenResponse->successful() ? $departemenResponse->json()['data'] : [];

        return view('pelayanan.edit', compact('layanan', 'departemens'));
    }

    public function update(Request $request, $id)
    {
        $response = Http::put("{$this->apiLayananUrl}/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil diperbarui.');
        }
        return back()->withErrors($response->json()['errors'] ?? ['Terjadi kesalahan'])->withInput();
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiLayananUrl}/{$id}");
        if ($response->successful()) {
            return redirect()->route('pelayanan.index')->with('success', 'Layanan berhasil dihapus.');
        }
        return redirect()->route('pelayanan.index')->with('error', 'Gagal menghapus layanan.');
    }
}