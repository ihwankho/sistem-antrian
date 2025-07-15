<?php

use App\Http\Controllers\LoginWebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginWebController::class, 'showLogin'])->name('login.form');
Route::post('/login', [LoginWebController::class, 'login'])->name('login.web');

Route::get('/dashboard', function () {
    return 'Selamat datang di dashboard!';
})->middleware('auth');
