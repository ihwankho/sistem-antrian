<?php

use App\Http\Controllers\LoginWebController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartemenWebController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\AntrianController; // Controller Web
use App\Http\Controllers\PanggilanController; // Controller Panggilan
use App\Http\Controllers\DisplayController; // Controller Display

//LandingPage
Route::get('/', [LandingPageController::class, 'index'])->name('landing.page');

// Autentikasi
Route::get('/login', [LoginWebController::class, 'showLogin'])->name('login.form');
Route::post('/login', [LoginWebController::class, 'login'])->name('login.web');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');

// Pengguna - Resource Controller
Route::prefix('pengguna')->name('pengguna.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
});

//Departemens
Route::prefix('departemen')->name('departemen.')->group(function () {
    Route::get('/', [DepartemenWebController::class, 'index'])->name('index');
    Route::get('/create', [DepartemenWebController::class, 'create'])->name('create');
    Route::post('/', [DepartemenWebController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [DepartemenWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DepartemenWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [DepartemenWebController::class, 'destroy'])->name('destroy');
});

//Loket
Route::prefix('loket')->name('loket.')->group(function () {
    Route::get('/', [LoketController::class, 'index'])->name('index');
    
    // Rute untuk menampilkan form tambah data
    Route::get('/create', [LoketController::class, 'create'])->name('create');
    
    // Rute untuk menyimpan data baru
    Route::post('/', [LoketController::class, 'store'])->name('store');
    
    // Rute untuk menampilkan form edit data
    Route::get('/{loket}/edit', [LoketController::class, 'edit'])->name('edit');
    
    // Rute untuk menyimpan perubahan data
    Route::put('/{loket}', [LoketController::class, 'update'])->name('update');
    
    // Rute untuk menghapus data
    Route::delete('/{loket}', [LoketController::class, 'destroy'])->name('destroy');
});

// Antrian
Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/pilih-layanan', [AntrianController::class, 'pilihLayanan'])->name('pilih-layanan');
    Route::get('/isi-data', [AntrianController::class, 'isiData'])->name('isi-data');
    Route::post('/buat-tiket', [AntrianController::class, 'buatTiket'])->name('buat-tiket');
    Route::get('/tiket', [AntrianController::class, 'tampilTiket'])->name('tiket');
    Route::get('/antrian_all', [AntrianController::class, 'all']);
});

// Panggilan Antrian
Route::prefix('panggilan')->name('panggilan.')->group(function() {
    Route::get('/admin', [PanggilanController::class, 'admin'])->name('admin');
    Route::post('/admin/next', [PanggilanController::class, 'next'])->name('admin.next');
    Route::post('/admin/recall', [PanggilanController::class, 'recall'])->name('admin.recall');
    Route::post('/admin/finish', [PanggilanController::class, 'finish'])->name('admin.finish');
    Route::post('/admin/skip', [PanggilanController::class, 'skip'])->name('admin.skip');
});

// Display Antrian - Tampilan Public
Route::prefix('display')->name('display.')->group(function() {
    // Halaman utama display
    Route::get('/', [DisplayController::class, 'index'])->name('index');
    
    // API endpoints untuk data antrian
    Route::get('/queue-data', [DisplayController::class, 'getQueueData'])->name('queue-data');
    
    // API untuk mendapatkan antrian yang sedang dipanggil saja
    Route::get('/current-calling', [DisplayController::class, 'getCurrentCallingOnly'])->name('current-calling');
    
    // API untuk mendapatkan detail antrian berdasarkan loket
    Route::get('/loket/{loketId}', [DisplayController::class, 'getQueueByLoket'])->name('loket');
    
    // API untuk mendapatkan ringkasan harian
    Route::get('/daily-summary', [DisplayController::class, 'getDailySummary'])->name('daily-summary');
    
    // Endpoint untuk testing koneksi
    Route::get('/ping', [DisplayController::class, 'ping'])->name('ping');
});