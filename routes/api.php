<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\PelayananController;
use App\Http\Controllers\Controller;

Route::get('/test-api', function () {
    return 'API terhubung!';
});

Route::post('/users', [UserController::class, 'store']);
Route::get('/users/showall', [UserController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::put('/users/{id}', [UserController::class, 'update']); // Edit user
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Hapus user


//Departemen
Route::apiResource('departemen', DepartemenController::class);

//Pelayanan
Route::apiResource('pelayanan', PelayananController::class);
