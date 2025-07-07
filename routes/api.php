<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

Route::get('/test-api', function () {
    return 'API terhubung!';
});

Route::post('/users', [UserController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
