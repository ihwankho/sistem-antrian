<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Pool;

class LoketController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        // [KONSISTENSI] Mengambil URL API dari config/services.php
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    public function index()
    {
        try {
            $token = Session::get('token');

            // [PERFORMA] Menjalankan tiga panggilan API secara bersamaan
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->withToken($token)->get($this->apiBaseUrl . '/lokets'),
                $pool->withToken($token)->get($this->apiBaseUrl . '/departemen-loket'),
                $pool->withToken($token)->get($this->apiBaseUrl . '/users-loket'), // Token ditambahkan untuk konsistensi
            ]);

            // Cek jika ada respons yang tidak terotorisasi (sesi berakhir)
            foreach ($responses as $response) {
                if ($response->unauthorized()) {
                    Session::flush();
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            // Ekstrak data dari respons yang berhasil
            $lokets = $responses[0]->successful() ? $responses[0]->json('data', []) : [];
            $departemens = $responses[1]->successful() ? $responses[1]->json('data', []) : [];
            $users = $responses[2]->successful() ? $responses[2]->json('data', []) : [];

            return view('loket.index', compact('lokets', 'departemens', 'users'));
        } catch (\Exception $e) {
            Log::error('Exception di LoketController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data loket.');
        }
    }

    public function create()
    {
        // Form create tidak memerlukan data awal, bisa langsung tampilkan view
        return view('loket.create');
    }

    public function store(Request $request)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge(['nama_loket' => strip_tags($request->input('nama_loket'))]);

        $validator = Validator::make($request->all(), [
            'nama_loket' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->post($this->apiBaseUrl . '/lokets', $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil ditambahkan.');
            }

            return back()->with('error', $response->json('message', 'Gagal menambahkan loket.'))->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di LoketController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->get($this->apiBaseUrl . "/lokets/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->failed()) {
                return redirect()->route('loket.index')->with('error', 'Loket tidak ditemukan.');
            }

            $loket = $response->json('data', []);

            return view('loket.edit', compact('loket'));

        } catch (\Exception $e) {
            Log::error('Exception di LoketController@edit: ' . $e->getMessage());
            return redirect()->route('loket.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function update(Request $request, $id)
    {
        // [KEAMANAN] Sanitasi dan Validasi input sebelum dikirim ke API
        $request->merge(['nama_loket' => strip_tags($request->input('nama_loket'))]);

        $validator = Validator::make($request->all(), [
            'nama_loket' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->put($this->apiBaseUrl . "/lokets/{$id}", $validator->validated());

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil diperbarui.');
            }

            return back()->with('error', $response->json('message', 'Gagal memperbarui loket.'))->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di LoketController@update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete($this->apiBaseUrl . "/lokets/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('loket.index')->with('success', 'Loket berhasil dihapus.');
            }

            return redirect()->route('loket.index')->with('error', $response->json('message', 'Gagal menghapus loket.'));

        } catch (\Exception $e) {
            Log::error('Exception di LoketController@destroy: ' . $e->getMessage());
            return redirect()->route('loket.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }
}