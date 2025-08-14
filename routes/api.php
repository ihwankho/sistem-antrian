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

// ======================
// ROUTE PUBLIK (TANPA LOGIN)
// ======================

// Tes koneksi
Route::get('/test-api', function () {
    return 'API terhubung!';
});

// Login
Route::post('/login', [AuthController::class, 'login']);


// ======================
// ROUTE DENGAN TOKEN (DEFAULT SEMUA LOGIN)
// ======================
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD User
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/showall', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/users-loket', [UserController::class, 'getUsLok']);

    // Departemen & Pelayanan
    Route::apiResource('departemen', DepartemenController::class);
    Route::get('/departemen-loket', [DepartemenController::class, 'getDepLok']);

    Route::apiResource('pelayanan', PelayananController::class);
    Route::get('/pelayanan-departemen', [PelayananController::class, 'getPelaDep']);

    // Loket
    Route::apiResource('lokets', LoketController::class);

    // Panduan
    Route::apiResource('panduan', PanduanController::class);

    // Pengunjung
    Route::apiResource('pengunjung', PengunjungController::class);

    // Antrian
    Route::get('/antrian', [AntrianController::class, 'index']);
    Route::post('/antrian', [AntrianController::class, 'store']);
    Route::get('/antrian_all', [AntrianController::class, 'getAllAntrian']);
    Route::get('/antrian/loket/{id_loket}', [AntrianController::class, 'getByLoket']);
    Route::get('/antrian/loket', [AntrianController::class, 'getAntrianDipanggil']);
    Route::post('/antrian/call', [AntrianController::class, 'callNextAntrian']);
    Route::post('/antrian/finish', [AntrianController::class, 'finishAntrian']);
    Route::post('/antrian/skip', [AntrianController::class, 'SkipAntrian']);
    Route::post('/antrian/recall', [AntrianController::class, 'recallAntrian']);
});
