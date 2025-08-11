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
use Illuminate\Support\Facades\DB;
use App\Models\Pelayanan;

Route::get('/test-api', function () {
    return 'API terhubung!';
});

Route::post('/users', [UserController::class, 'store']);
Route::get('/users/showall', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/login', [AuthController::class, 'login']);
Route::put('/users/{id}', [UserController::class, 'update']); // Edit user
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Hapus user
Route::get('/users-loket', [UserController::class, 'getUsLok']); //data user x nama loket


//Departemen
Route::apiResource('departemen', DepartemenController::class);
Route::get('/departemen-loket', [DepartemenController::class, 'getDepLok']);

//Pelayanan
Route::apiResource('pelayanan', PelayananController::class);
Route::get('/pelayanan-departemen', [PelayananController::class, 'getPelaDep']);
//pengunjung
Route::apiResource('pengunjung', PengunjungController::class);

//Loket
Route::apiResource('lokets', LoketController::class);

//Panduan
Route::apiResource('panduan', PanduanController::class);

//Antrian
Route::get('/antrian', [AntrianController::class, 'index']);
Route::post('/antrian', [AntrianController::class, 'store']);
Route::get('/antrian/loket/{id_loket}', [AntrianController::class, 'getByLoket']);
Route::get('/antrian_all', [AntrianController::class, 'getAllAntrian']);
Route::post('/antrian/call', [AntrianController::class, 'callNextAntrian']); //panggil antrian
Route::post('/antrian/finish', [AntrianController::class, 'finishAntrian']); //antrian selesai
Route::post('antrian/skip', [AntrianController::class, 'SkipAntrian']); //Skip Antrian
Route::post('/antrian/recall', [AntrianController::class, 'recallAntrian']); // Panggil Ulang
Route::get('/antrian/loket', [AntrianController::class, 'getAntrianDipanggil']); // nama loket serta antrian nya
