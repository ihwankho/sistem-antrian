<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\PelayananController;
use App\Http\Controllers\Api\PengunjungController;
use App\Http\Controllers\Api\LoketController;
use App\Http\Controllers\Api\AntrianController;
use App\Http\Controllers\Api\PanduanController;
use App\Http\Controllers\Api\ReportController; // DIUBAH: Menambahkan ReportController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute-rute ini dimuat oleh RouteServiceProvider dan semuanya
| secara otomatis akan memiliki prefix /api.
|
*/


// ===================================================================
// ROUTE PUBLIK - Tidak memerlukan login/token
// ===================================================================

Route::get('/test-api', fn() => 'API terhubung!');
Route::post('/login', [AuthController::class, 'login']);

// Endpoint untuk keperluan umum di halaman depan/display
Route::get('/pelayanan-departemen', [PelayananController::class, 'getPelaDep']);
Route::get('/users-loket', [UserController::class, 'getUsLok']);
Route::get('/antrian/dipanggil', [AntrianController::class, 'getAntrianDipanggil']); // DIUBAH: URL lebih jelas
Route::apiResource('panduan', PanduanController::class)->only(['index', 'show']);
Route::apiResource('pelayanan', PelayananController::class)->only(['index', 'show']);

// Endpoint untuk pengunjung membuat antrian baru
Route::post('/antrian', [AntrianController::class, 'store']);
Route::get('/antrian/show/{id}', [AntrianController::class, 'ShowPe']);
Route::get('/antrian_all', [AntrianController::class, 'getAllAntrian']);
// ===================================================================
// ROUTE TERPROTEKSI - Memerlukan token via Sanctum
// ===================================================================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // --- MANAJEMEN PENGGUNA & MASTER DATA (UMUMNYA UNTUK ADMIN) ---

    // DIUBAH: Rute User disederhanakan menggunakan apiResource
    // Catatan: Frontend perlu menyesuaikan GET /users/showall menjadi GET /users
    Route::apiResource('users', UserController::class);

    Route::apiResource('departemen', DepartemenController::class);
    Route::get('/departemen-loket', [DepartemenController::class, 'getDepLok']);

    // Rute create, update, delete untuk Pelayanan & Panduan
    Route::apiResource('pelayanan', PelayananController::class)->except(['index', 'show']);
    Route::apiResource('panduan', PanduanController::class)->except(['index', 'show']);

    Route::apiResource('lokets', LoketController::class);
    Route::apiResource('pengunjung', PengunjungController::class);


    // --- MANAJEMEN ANTRIAN (UNTUK ADMIN & PETUGAS) ---
    Route::prefix('antrian')->group(function() {
        Route::get('/', [AntrianController::class, 'index']); // Daftar semua antrian (terpaginasi)
        Route::get('/pengunjung/{pengunjung}', [AntrianController::class, 'showByPengunjung']); // DIUBAH: URL lebih standar
        Route::get('/loket/{loket}', [AntrianController::class, 'getByLoket']); // DIUBAH: Menggunakan model binding

        // Aksi Panggilan oleh Petugas
        Route::post('/panggil', [AntrianController::class, 'callNextAntrian']); // DIUBAH: URL lebih singkat
        Route::post('/selesai', [AntrianController::class, 'finishAntrian']); // DIUBAH: URL lebih singkat
        Route::post('/lewati', [AntrianController::class, 'skipAntrian']); // DIUBAH: URL lebih singkat
        Route::post('/panggil-ulang', [AntrianController::class, 'recallAntrian']); // DIUBAH: URL lebih singkat
    });


    // --- LAPORAN ---
    // DIUBAH: Rute laporan dipindahkan ke dalam middleware agar aman
    // DIUBAH: Menggunakan ReportController & URL yang lebih rapi
    Route::get('/reports/activity-history', [ReportController::class, 'getActivityHistory']);
    Route::get('/reports/monthly', [ReportController::class, 'laporanBulanan']); // DIUBAH: Konsisten di ReportController
});