<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthWebController;

Route::get('/', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');
Route::get('/dashboard', [AuthWebController::class, 'dashboard'])->name('dashboard');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
