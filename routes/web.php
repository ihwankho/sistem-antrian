<?php

use Illuminate\Support\Facades\Route;
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

// LandingPage
Route::get('/', [LandingPageController::class, 'index'])->name('landing.page');

// Autentikasi
Route::get('/login', [LoginWebController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginWebController::class, 'login'])->name('login.post');

// Dashboard - hanya untuk admin (role 1)
Route::get('/dashboard', [DashboardController::class, 'showDashboard'])
    ->name('dashboard')
    ->middleware(['web.auth', 'role:1']);

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
    Route::get('/{loket}/edit', [LoketController::class, 'edit'])->name('edit');
    Route::put('/{loket}', [LoketController::class, 'update'])->name('update');
    Route::delete('/{loket}', [LoketController::class, 'destroy'])->name('destroy');
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

// Antrian - untuk admin (role 1) dan petugas (role 2)
Route::prefix('antrian')->name('antrian.')->middleware(['web.auth', 'role:1,2'])->group(function () {
    Route::get('/pilih-layanan', [AntrianController::class, 'pilihLayanan'])->name('pilih-layanan');
    Route::get('/isi-data', [AntrianController::class, 'isiData'])->name('isi-data');
    Route::post('/buat-tiket', [AntrianController::class, 'buatTiket'])->name('buat-tiket');
    Route::get('/tiket', [AntrianController::class, 'tampilTiket'])->name('tiket');
    Route::get('/antrian_all', [AntrianController::class, 'all']);
});

// Panggilan Antrian - untuk admin (role 1) dan petugas (role 2)
Route::prefix('panggilan')->name('panggilan.')->middleware(['web.auth', 'role:1,2'])->group(function() {
    // Halaman utama - admin bisa pilih loket, petugas otomatis ke loket yang ditugaskan
    Route::get('/admin', [PanggilanController::class, 'admin'])->name('admin');
    Route::get('/petugas', [PanggilanController::class, 'petugas'])->name('petugas');
    
    // Actions - dapat digunakan oleh admin dan petugas
    Route::post('/next', [PanggilanController::class, 'next'])->name('next');
    Route::post('/recall', [PanggilanController::class, 'recall'])->name('recall');
    Route::post('/finish', [PanggilanController::class, 'finish'])->name('finish');
    Route::post('/skip', [PanggilanController::class, 'skip'])->name('skip');
    
    // Statistik untuk petugas
    Route::get('/stats', [PanggilanController::class, 'getStats'])->name('stats');
    
    // Route khusus untuk admin (backward compatibility)
    Route::post('/admin/next', [PanggilanController::class, 'next'])->name('admin.next');
    Route::post('/admin/recall', [PanggilanController::class, 'recall'])->name('admin.recall');
    Route::post('/admin/finish', [PanggilanController::class, 'finish'])->name('admin.finish');
    Route::post('/admin/skip', [PanggilanController::class, 'skip'])->name('admin.skip');
    
    // Route khusus untuk petugas
    Route::post('/petugas/next', [PanggilanController::class, 'next'])->name('petugas.next');
    Route::post('/petugas/recall', [PanggilanController::class, 'recall'])->name('petugas.recall');
    Route::post('/petugas/finish', [PanggilanController::class, 'finish'])->name('petugas.finish');
    Route::post('/petugas/skip', [PanggilanController::class, 'skip'])->name('petugas.skip');
    Route::get('/petugas/stats', [PanggilanController::class, 'getStats'])->name('petugas.stats');
});

// Display Antrian - Tampilan Public (tanpa auth)
Route::prefix('display')->name('display.')->group(function() {
    Route::get('/', [DisplayController::class, 'index'])->name('index');
    Route::get('/queue-data', [DisplayController::class, 'getQueueData'])->name('queue-data');
    Route::get('/current-calling', [DisplayController::class, 'getCurrentCallingOnly'])->name('current-calling');
    Route::get('/loket/{loketId}', [DisplayController::class, 'getQueueByLoket'])->name('loket');
    Route::get('/daily-summary', [DisplayController::class, 'getDailySummary'])->name('daily-summary');
    Route::get('/ping', [DisplayController::class, 'ping'])->name('ping');
});

// Logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// Fallback route dengan perbaikan untuk mengarahkan petugas ke halaman yang tepat
Route::fallback(function () {
    if (Auth::check()) {
        // Redirect ke halaman yang sesuai berdasarkan role
        if (Auth::user()->role === 1) {
            return redirect('/dashboard');
        } elseif (Auth::user()->role === 2) {
            return redirect('/panggilan/petugas');
        } else {
            return redirect('/panggilan/petugas');
        }
    } else {
        return redirect('/login')->with('error', 'Halaman tidak ditemukan.');
    }
});

// Test API connection route
Route::get('/test-api-connection', function () {
    try {
        $apiUrl = env('API_BASE_URL', 'http://127.0.0.1:8001');
        
        // Test connection to departemen API
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
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});