<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\LoginWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartemenWebController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\PanggilanController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ReportController;

// LandingPage
Route::get('/', [LandingPageController::class, 'index'])->name('landing.page');

// Autentikasi
Route::get('/login', [LoginWebController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginWebController::class, 'login'])->name('login.post');

// ======================
// RUTE UNTUK PENGUNJUNG (TANPA LOGIN)
// ======================

// Antrian - untuk pengunjung (tanpa login)
Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/pilih-layanan', [AntrianController::class, 'pilihLayanan'])->name('pilih-layanan');
    Route::get('/isi-data', [AntrianController::class, 'isiData'])->name('isi-data');
    Route::post('/buat-tiket', [AntrianController::class, 'buatTiket'])->name('buat-tiket');
    Route::get('/tiket/{id}', [AntrianController::class, 'tampilTiket'])->name('antrian.tiket');
    Route::get('/tiket/{uuid}', [AntrianController::class, 'tampilTiket'])->name('tiket');
    Route::post('/tiket/cari', [AntrianController::class, 'cariTiket'])->name('cari');
    Route::get('/tiket/detail/{uuid}', [AntrianController::class, 'detailTiket'])->name('tiket.detail');
    Route::post('/api/cari-by-nik', [AntrianController::class, 'cariTiketJson'])->name('api.cari');

});


// Display Antrian - Tampilan Public (tanpa auth)
Route::prefix('display')->name('display.')->group(function() {
    Route::get('/', [DisplayController::class, 'index'])->name('index');
    Route::get('/queue-data', [DisplayController::class, 'getQueueData'])->name('queue-data');
    Route::get('/current-calling', [DisplayController::class, 'getCurrentCallingOnly'])->name('current-calling');
    Route::get('/loket/{id}', [DisplayController::class, 'getQueueByLoket'])->name('loket');
    Route::get('/daily-summary', [DisplayController::class, 'getDailySummary'])->name('daily-summary');
    Route::get('/ping', [DisplayController::class, 'ping'])->name('ping');
});

// ======================
// RUTE YANG MEMERLUKAN LOGIN
// ======================

// Rute baru untuk dashboard
Route::get('/dashboard', [DashboardController::class, 'showDashboard'])
    ->name('dashboard')
    ->middleware(['web.auth', 'role:1,2']); // Izinkan role 1 (admin) dan 2 (petugas)

// Pengguna - hanya untuk admin (role 1)
Route::prefix('pengguna')->name('pengguna.')->middleware(['web.auth', 'role:1'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
});

// Departemen - hanya untuk admin (role 1)
Route::prefix('departemen')->name('departemen.')->middleware(['web.auth', 'role:1'])->group(function () {
    Route::get('/', [DepartemenWebController::class, 'index'])->name('index');
    Route::get('/create', [DepartemenWebController::class, 'create'])->name('create');
    Route::post('/', [DepartemenWebController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [DepartemenWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DepartemenWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [DepartemenWebController::class, 'destroy'])->name('destroy');
});

// Loket - hanya untuk admin (role 1)
Route::prefix('loket')->name('loket.')->middleware(['web.auth', 'role:1'])->group(function () {
    Route::get('/', [LoketController::class, 'index'])->name('index');
    Route::get('/create', [LoketController::class, 'create'])->name('create');
    Route::post('/', [LoketController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LoketController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LoketController::class, 'update'])->name('update');
    Route::delete('/{id}', [LoketController::class, 'destroy'])->name('destroy');
});

// Pelayanan - hanya untuk admin (role 1)
Route::prefix('pelayanan')->name('pelayanan.')->middleware(['web.auth', 'role:1'])->group(function () {
    Route::get('/', [PelayananController::class, 'index'])->name('index');
    Route::get('/create', [PelayananController::class, 'create'])->name('create');
    Route::post('/', [PelayananController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PelayananController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PelayananController::class, 'update'])->name('update');
    Route::delete('/{id}', [PelayananController::class, 'destroy'])->name('destroy');
});

// Antrian - untuk admin (role 1) dan petugas (role 2) - hanya bagian yang memerlukan auth
Route::prefix('antrian')->name('antrian.')->middleware(['web.auth', 'role:1,2'])->group(function () {
    Route::get('/antrian_all', [AntrianController::class, 'all'])->name('all');
});

// Panggilan Antrian - untuk admin (role 1) dan petugas (role 2)
Route::prefix('panggilan')->name('panggilan.')->middleware(['web.auth', 'role:1,2'])->group(function() {
    Route::get('/admin', [PanggilanController::class, 'admin'])->name('admin');
    Route::get('/petugas', [PanggilanController::class, 'petugas'])->name('petugas');
    
    // Actions
    Route::post('/next', [PanggilanController::class, 'next'])->name('next');
    Route::post('/recall', [PanggilanController::class, 'recall'])->name('recall');
    Route::post('/finish', [PanggilanController::class, 'finish'])->name('finish');
    Route::post('/skip', [PanggilanController::class, 'skip'])->name('skip');
    
    // Statistik untuk petugas
    Route::get('/stats', [PanggilanController::class, 'getStats'])->name('stats');
    
    // Route khusus admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('/next', [PanggilanController::class, 'next'])->name('next');
        Route::post('/recall', [PanggilanController::class, 'recall'])->name('recall');
        Route::post('/finish', [PanggilanController::class, 'finish'])->name('finish');
        Route::post('/skip', [PanggilanController::class, 'skip'])->name('skip');
    });
    
    // Route khusus petugas
    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::post('/next', [PanggilanController::class, 'next'])->name('next');
        Route::post('/recall', [PanggilanController::class, 'recall'])->name('recall');
        Route::post('/finish', [PanggilanController::class, 'finish'])->name('finish');
        Route::post('/skip', [PanggilanController::class, 'skip'])->name('skip');
        Route::get('/stats', [PanggilanController::class, 'getStats'])->name('stats');
    });
});

// Logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// Report
Route::get('/reports/activity', [ReportController::class, 'showActivityReport'])
    ->name('reports.activity')
    ->middleware(['web.auth', 'role:1,2']);
Route::get('/reports/activity/export', [ReportController::class, 'exportExcel'])->name('reports.activity.export');

// Test API connection
Route::get('/test-api-connection', function () {
    try {
        $apiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001');
        $departemenResponse = Http::timeout(10)->get($apiUrl . '/api/departemen');
        $loketResponse = Http::timeout(10)->get($apiUrl . '/api/lokets');
        
        return response()->json([
            'departemen_status' => $departemenResponse->status(),
            'departemen_successful' => $departemenResponse->successful(),
            'departemen_body' => $departemenResponse->json(),
            'loket_status' => $loketResponse->status(),
            'loket_successful' => $loketResponse->successful(),
            'loket_body' => $loketResponse->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Fallback route
Route::fallback(function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            1 => redirect('/dashboard'),
            2 => redirect('/panggilan/petugas'),
            default => redirect('/panggilan/petugas'),
        };
    }
    return redirect('/login')->with('error', 'Halaman tidak ditemukan.');
});
// Tambahkan ini di paling bawah routes/web.php
Route::get('/tes-koneksi-api', function () {
    try {
        $apiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001/api');
        echo "<h3>Mencoba menghubungi alamat API: " . $apiUrl . "/pelayanan</h3>";

        $response = Illuminate\Support\Facades\Http::timeout(5)->get($apiUrl . '/pelayanan');

        echo "<h1>✔️ KONEKSI BERHASIL!</h1>";
        echo "<p>Aplikasi Web Anda BISA terhubung ke Server API.</p>";
        echo "<p>Status Code dari API: " . $response->status() . "</p>";
        echo "<strong>Response Body dari API:</strong><pre>";
        print_r($response->json());
        echo "</pre>";

    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        echo "<h1>❌ KONEKSI GAGAL!</h1>";
        echo "<p>Ini membuktikan bahwa Aplikasi Web Anda (yang berjalan di port 8000) TIDAK BISA 'melihat' atau terhubung ke Server API Anda (yang berjalan di port 8001).</p>";
        echo "<p>Ini BUKAN masalah kode simpan data, tetapi murni masalah JARINGAN atau ENVIRONMENT.</p>";
        echo "<hr>";
        echo "<p><strong>Detail Error Teknis:</strong> " . $e->getMessage() . "</p>";
    }
});