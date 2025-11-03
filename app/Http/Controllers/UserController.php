<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;

class UserController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = rtrim(config('services.api.base_url'), '/');
    }

    public function index()
    {
        try {
            $token = Session::get('token');

            $responses = Http::pool(fn (Pool $pool) => [
                $pool->withToken($token)->get($this->apiBaseUrl . '/users-loket'),
                $pool->withToken($token)->get($this->apiBaseUrl . '/lokets'),
            ]);

            foreach ($responses as $response) {
                if ($response->unauthorized()) {
                    Session::flush();
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            $users = $responses[0]->successful() ? $responses[0]->json('data', []) : [];
            $lokets = $responses[1]->successful() ? $responses[1]->json('data', []) : [];

            return view('user.index', compact('users', 'lokets'));
        } catch (\Exception $e) {
            Log::error('Exception di UserController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data pengguna.');
        }
    }

    public function create()
    {
        $lokets = $this->_getLokets(Session::get('token'));
        return view('user.create', compact('lokets'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nama'          => strip_tags($request->input('nama')),
            'nama_pengguna' => strip_tags($request->input('nama_pengguna')),
        ]);

        // --- PERUBAHAN: Validasi 'nama_pengguna' ditambahkan rule 'email' ---
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'nama_pengguna' => 'required|string|email|max:255|unique:users,nama_pengguna', // <-- PERUBAHAN
            'password'      => 'required|string|min:6|confirmed',
            'role'          => 'required|in:1,2',
            'id_loket'      => 'required_if:role,2|nullable|numeric',
            'foto'          => 'nullable|file|max:2048', 
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $validatedData = $validator->validated();
            $fotoFile = $request->hasFile('foto') ? $request->file('foto') : null;

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

            $response = $this->_sendApiRequest('post', '/users', $validatedData, $fotoFile);

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
            }

            Log::error('Gagal menambahkan pengguna', ['response' => $response->body()]);
            return back()->withErrors(['error' => 'Gagal menambahkan pengguna.'])->withInput();

        } catch (\Exception $e) {
            Log::error('Exception di UserController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $token = Session::get('token');

            $responses = Http::pool(fn (Pool $pool) => [
                $pool->withToken($token)->get($this->apiBaseUrl . "/users/{$id}"),
                $pool->withToken($token)->get($this->apiBaseUrl . '/lokets'),
            ]);

            foreach ($responses as $response) {
                if ($response->unauthorized()) {
                    Session::flush();
                    return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
                }
            }

            if ($responses[0]->failed()) {
                return redirect()->route('pengguna.index')->with('error', 'Pengguna tidak ditemukan.');
            }

            $user = $responses[0]->json('data', []);
            $lokets = $responses[1]->json('data', []);

            return view('user.edit', compact('user', 'lokets'));
        } catch (\Exception $e) {
            Log::error('Exception di UserController@edit: ' . $e->getMessage());
            return redirect()->route('pengguna.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'nama'          => strip_tags($request->input('nama')),
            'nama_pengguna' => strip_tags($request->input('nama_pengguna')),
        ]);
        
        // --- PERUBAHAN: Validasi 'nama_pengguna' ditambahkan rule 'email' ---
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'nama_pengguna' => 'required|string|email|max:255|unique:users,nama_pengguna,' . $id, // <-- PERUBAHAN
            'password'      => 'nullable|string|min:6|confirmed',
            'role'          => 'required|in:1,2',
            'id_loket'      => 'required_if:role,2|nullable|numeric',
            'foto'          => 'nullable|file|max:2048', 
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $validatedData = $validator->validated();
            if (empty($validatedData['password'])) {
                unset($validatedData['password']);
                if(isset($validatedData['password_confirmation'])) {
                     unset($validatedData['password_confirmation']);
                }
            }

            $fotoFile = $request->hasFile('foto') ? $request->file('foto') : null;

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

            $validatedData['_method'] = 'PUT';
            $response = $this->_sendApiRequest('post', "/users/{$id}", $validatedData, $fotoFile);

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
            }

            return back()->withErrors($response->json('errors', ['error' => 'Gagal memperbarui pengguna.']))->withInput();
        } catch (\Exception $e) {
            Log::error('Exception di UserController@update: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $token = Session::get('token');
            $response = Http::withToken($token)->delete($this->apiBaseUrl . "/users/{$id}");

            if ($response->unauthorized()) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($response->successful()) {
                return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
            }

            return redirect()->route('pengguna.index')->with('error', 'Gagal menghapus pengguna.');
        } catch (\Exception $e) {
            Log::error('Exception di UserController@destroy: ' . $e->getMessage());
            return redirect()->route('pengguna.index')->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    private function _getLokets(?string $token): array
    {
        if (!$token) return [];
        try {
            $response = Http::withToken($token)->get($this->apiBaseUrl . '/lokets');
            if ($response->successful()) {
                return $response->json('data', []);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data loket: ' . $e->getMessage());
        }
        return [];
    }

    private function _sendApiRequest(string $method, string $endpoint, array $data, ?UploadedFile $file): Response
    {
        $token = Session::get('token');
        $http = Http::withToken($token);

        if ($file) {
            $http->attach('foto', $file->get(), $file->getClientOriginalName());
        }

        if (strtolower($method) === 'post') {
            return $http->post($this->apiBaseUrl . $endpoint, $data);
        }
        if (strtolower($method) === 'put') {
            if (!$file) {
                return $http->put($this->apiBaseUrl . $endpoint, $data);
            }
        }

        return Http::withToken($token)->{strtolower($method)}($this->apiBaseUrl . $endpoint, $data);
    }
}