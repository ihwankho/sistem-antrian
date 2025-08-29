<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DepartemenWebController extends Controller
{
    private $apiUrl = 'http://127.0.0.1:8001/api/departemen';
    private $loketApiUrl = 'http://127.0.0.1:8001/api/lokets';

    public function index()
    {
        // Panggilan 1: Ambil semua data departemen
        $departemenResponse = Http::get($this->apiUrl);
        $departemens = $departemenResponse->successful() ? $departemenResponse->json()['data'] : [];

        // Panggilan 2: Ambil semua data loket untuk pencocokan nama
        $loketResponse = Http::get($this->loketApiUrl);
        $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];

        // Kirim kedua variabel ke view
        return view('departemen.index', compact('departemens', 'lokets'));
    }

    public function create()
    {
        $loketResponse = Http::get($this->loketApiUrl);
        $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];
        return view('departemen.create', compact('lokets'));
    }

    public function store(Request $request)
    {
        $response = Http::post($this->apiUrl, $request->all());
        if ($response->successful()) {
            return redirect()->route('departemen.index')->with('success', 'Departemen berhasil ditambahkan');
        }
        return back()->withErrors($response->json()['errors'] ?? ['Terjadi kesalahan'])->withInput();
    }

    public function edit($id)
    {
        $response = Http::get("{$this->apiUrl}/{$id}");
        $loketResponse = Http::get($this->loketApiUrl);

        if ($response->failed()) {
            return redirect()->route('departemen.index')->with('error', 'Departemen tidak ditemukan.');
        }

        $departemen = $response->json()['data'];
        $lokets = $loketResponse->successful() ? $loketResponse->json()['data'] : [];
        return view('departemen.edit', compact('departemen', 'lokets'));
    }

    public function update(Request $request, $id)
    {
        $response = Http::put("{$this->apiUrl}/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('departemen.index')->with('success', 'Departemen berhasil diperbarui');
        }
        return back()->withErrors($response->json()['errors'] ?? ['Gagal update'])->withInput();
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiUrl}/{$id}");
        if ($response->successful()) {
            return redirect()->route('departemen.index')->with('success', 'Departemen berhasil dihapus');
        }
        return redirect()->route('departemen.index')->with('error', 'Gagal menghapus departemen.');
    }
}