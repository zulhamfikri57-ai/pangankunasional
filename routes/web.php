<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PanganController;
use App\Http\Controllers\AdminController;

// Panel Khusus Administrator
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/komoditas', [AdminController::class, 'komoditas'])->name('komoditas');
    Route::post('/komoditas', [AdminController::class, 'storeKomoditas'])->name('komoditas.store');
    Route::put('/komoditas/{id}', [AdminController::class, 'updateKomoditas'])->name('komoditas.update');
    Route::delete('/komoditas/{id}', [AdminController::class, 'destroyKomoditas'])->name('komoditas.destroy');

    Route::get('/pengguna', [AdminController::class, 'pengguna'])->name('pengguna');
    Route::put('/pengguna/{id}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [AdminController::class, 'destroyPengguna'])->name('pengguna.destroy');

    Route::get('/panduan', [AdminController::class, 'panduan'])->name('panduan');
    Route::post('/panduan', [AdminController::class, 'storePanduan'])->name('panduan.store');
    Route::delete('/panduan/{id}', [AdminController::class, 'destroyPanduan'])->name('panduan.destroy');
});

// Rute Tamu (Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Rute Terproteksi (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::get('/', [PanganController::class, 'beranda'])->name('beranda');

    Route::get('/produksi', [PanganController::class, 'produksi'])->name('produksi');
    Route::post('/produksi', [PanganController::class, 'storeProduksi'])->name('produksi.store');

    Route::get('/jual-hasil', [PanganController::class, 'jualHasil'])->name('jual');
    Route::post('/jual-hasil', [PanganController::class, 'storeJualHasil'])->name('jual.store');
    Route::put('/jual-hasil/{id}', [PanganController::class, 'updateJualHasil'])->name('jual.update');
    Route::delete('/jual-hasil/{id}', [PanganController::class, 'destroyJualHasil'])->name('jual.destroy');

    Route::get('/keuangan', [PanganController::class, 'keuangan'])->name('keuangan');
    Route::post('/keuangan', [PanganController::class, 'storeKeuangan'])->name('keuangan.store');
    Route::put('/keuangan/{id}', [PanganController::class, 'updateKeuangan'])->name('keuangan.update');
    Route::delete('/keuangan/{id}', [PanganController::class, 'destroyKeuangan'])->name('keuangan.destroy');

    Route::get('/laporan', [PanganController::class, 'laporan'])->name('laporan');
    Route::get('/harga', [PanganController::class, 'harga'])->name('harga');

    Route::get('/pantauan', [PanganController::class, 'pantauan'])->name('pantauan');
    Route::post('/pantauan', [PanganController::class, 'storeAlert'])->name('pantauan.store');

    Route::get('/panduan', [PanganController::class, 'panduan'])->name('panduan');
    Route::post('/panduan/tanya-pakar', [PanganController::class, 'tanyaPakar'])->name('panduan.tanya');

    Route::get('/profil', [PanganController::class, 'profil'])->name('profil');
    Route::post('/profil/upgrade', [PanganController::class, 'upgradePeran'])->name('profil.upgrade');
});