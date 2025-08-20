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
// ROUTE TANPA TOKEN
// ======================

Route::get('/pelayanan-departemen', [PelayananController::class, 'getPelaDep']); //pelayanan x departemen
Route::get('/users-loket', [UserController::class, 'getUsLok']); //user dengan loket
Route::apiResource('panduan', PanduanController::class) //panduan
    ->only(['index', 'show']);
Route::apiResource('pelayanan', PelayananController::class) // pelayanan
    ->only(['index', 'show']);
Route::post('/antrian', [AntrianController::class, 'store']); // Membuat antrian
Route::get('/antrian/loket', [AntrianController::class, 'getAntrianDipanggil']); //daftar antrian yang sedang dipanggil






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


    // Departemen & Pelayanan
    Route::apiResource('departemen', DepartemenController::class);
    Route::get('/departemen-loket', [DepartemenController::class, 'getDepLok']);

    Route::apiResource('pelayanan', PelayananController::class)
        ->only(['store', 'update', 'destroy']);


    // Loket
    Route::apiResource('lokets', LoketController::class);

    // Panduan
    Route::apiResource('panduan', PanduanController::class)
        ->only(['store', 'update', 'destroy']);
    // Pengunjung
    Route::apiResource('pengunjung', PengunjungController::class);

    // Antrian
    Route::get('/antrian', [AntrianController::class, 'index']);
    Route::get('/antrian/pengunjung/{id}', [AntrianController::class, 'ShowPe']); //Antrian x pengunjung

    Route::get('/antrian_all', [AntrianController::class, 'getAllAntrian']);
    Route::get('/antrian/loket/{id_loket}', [AntrianController::class, 'getByLoket']);

    Route::post('/antrian/call', [AntrianController::class, 'callNextAntrian']);
    Route::post('/antrian/finish', [AntrianController::class, 'finishAntrian']);
    Route::post('/antrian/skip', [AntrianController::class, 'SkipAntrian']);
    Route::post('/antrian/recall', [AntrianController::class, 'recallAntrian']);
});
