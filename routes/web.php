<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanganController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiklusController;
use App\Http\Controllers\AuthVerifikasiController;
use App\Http\Controllers\SpkDistribusiController;

// =========================================================================
// 1. RUTE PUBLIK (Bisa Diakses Tanpa Login)
// =========================================================================
Route::get('/', [PanganController::class, 'beranda'])->name('beranda');
Route::get('/harga', [PanganController::class, 'harga'])->name('harga');
Route::get('/jual-hasil', [PanganController::class, 'jualHasil'])->name('jual');
Route::get('/panduan', [PanganController::class, 'panduan'])->name('panduan');

// =========================================================================
// 2. RUTE AUTENTIKASI TAMU (Email Asli atau Nomor HP Asli)
// =========================================================================
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthVerifikasiController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthVerifikasiController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthVerifikasiController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthVerifikasiController::class, 'login'])->name('login.post');

    Route::get('/lupa-sandi', [AuthVerifikasiController::class, 'showLupaSandi'])->name('password.request');
    Route::post('/lupa-sandi', [AuthVerifikasiController::class, 'kirimOtpResetSandi'])->name('password.email');
    Route::get('/reset-sandi', [AuthVerifikasiController::class, 'showResetSandi'])->name('password.reset.form');
    Route::post('/reset-sandi', [AuthVerifikasiController::class, 'prosesResetSandi'])->name('password.update');
});

Route::post('/logout', [AuthVerifikasiController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =========================================================================
// 3. RUTE PENGGUNA TERAUTENTIKASI (Wajib Login)
// =========================================================================
Route::middleware('auth')->group(function () {

    // Verifikasi OTP Email / Nomor HP
    Route::get('/verifikasi-kontak', [AuthVerifikasiController::class, 'showVerifikasiOtp'])->name('verifikasi.otp');
    Route::post('/verifikasi-kontak', [AuthVerifikasiController::class, 'prosesVerifikasiOtp'])->name('verifikasi.otp.proses');
    Route::post('/verifikasi-kontak/kirim-ulang', [AuthVerifikasiController::class, 'kirimUlangOtp'])->name('verifikasi.otp.resend');

    // Pengajuan Upgrade Peran dengan Dokumen Asli
    Route::get('/upgrade-peran', [AuthVerifikasiController::class, 'showUpgradePeran'])->name('peran.upgrade');
    Route::post('/upgrade-peran', [AuthVerifikasiController::class, 'submitUpgradePeran'])->name('peran.upgrade.submit');

    // Sinkronisasi Harga & Ketersediaan Real-Time
    Route::post('/harga/sync', function (\App\Services\HargaSyncService $service) {
        $hasil = $service->sinkronkanSemua();
        return redirect()->route('harga')->with('success', "Sinkronisasi selesai ({$hasil['status_api']}) untuk {$hasil['jumlah_komoditas']} komoditas!");
    })->name('harga.sync');

    // Manajemen Siklus Produksi & Jadwal Perawatan
    Route::get('/siklus', [SiklusController::class, 'index'])->name('siklus.index');
    Route::post('/siklus', [SiklusController::class, 'store'])->name('siklus.store');
    Route::put('/siklus/{id}', [SiklusController::class, 'update'])->name('siklus.update');
    Route::patch('/siklus/{id}/selesai', [SiklusController::class, 'selesaikan'])->name('siklus.selesai');
    Route::delete('/siklus/{id}', [SiklusController::class, 'destroy'])->name('siklus.destroy');

    Route::post('/siklus/{siklusId}/jadwal', [SiklusController::class, 'storeJadwal'])->name('jadwal.store');
    Route::patch('/jadwal/{id}/toggle', [SiklusController::class, 'toggleJadwal'])->name('jadwal.toggle');
    Route::delete('/jadwal/{id}', [SiklusController::class, 'destroyJadwal'])->name('jadwal.destroy');

    // Catatan Produksi
    Route::get('/produksi', [PanganController::class, 'produksi'])->name('produksi');
    Route::post('/produksi', [PanganController::class, 'storeProduksi'])->name('produksi.store');
    Route::put('/produksi/{id}', [PanganController::class, 'updateProduksi'])->name('produksi.update');
    Route::delete('/produksi/{id}', [PanganController::class, 'destroyProduksi'])->name('produksi.destroy');

    // Marketplace & Jual Hasil
    Route::post('/jual-hasil', [PanganController::class, 'storeJualHasil'])->name('jual.store');
    Route::put('/jual-hasil/{id}', [PanganController::class, 'updateJualHasil'])->name('jual.update');
    Route::delete('/jual-hasil/{id}', [PanganController::class, 'destroyJualHasil'])->name('jual.destroy');

    // Pembukuan Keuangan
    Route::get('/keuangan', [PanganController::class, 'keuangan'])->name('keuangan');
    Route::post('/keuangan', [PanganController::class, 'storeKeuangan'])->name('keuangan.store');
    Route::put('/keuangan/{id}', [PanganController::class, 'updateKeuangan'])->name('keuangan.update');
    Route::delete('/keuangan/{id}', [PanganController::class, 'destroyKeuangan'])->name('keuangan.destroy');

    // Laporan & Analisis
    Route::get('/laporan', [PanganController::class, 'laporan'])->name('laporan');

    // Pantauan & Alert Harga
    Route::get('/pantauan', [PanganController::class, 'pantauan'])->name('pantauan');
    Route::post('/pantauan/alert', [PanganController::class, 'storeAlert'])->name('pantauan.store');
    Route::put('/pantauan/alert/{id}', [PanganController::class, 'updateAlert'])->name('pantauan.update');
    Route::patch('/pantauan/alert/{id}/toggle', [PanganController::class, 'toggleAlert'])->name('pantauan.toggle');
    Route::delete('/pantauan/alert/{id}', [PanganController::class, 'destroyAlert'])->name('pantauan.destroy');

    // Konsultasi Pakar & Profil
    Route::post('/panduan/tanya-pakar', [PanganController::class, 'tanyaPakar'])->name('panduan.tanya');
    Route::get('/profil', [PanganController::class, 'profil'])->name('profil');

    // SPK Distribusi
    Route::get('/spk-distribusi', [SpkDistribusiController::class, 'index'])->name('spk.distribusi');
});

// =========================================================================
// 4. PANEL KHUSUS ADMINISTRATOR
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Moderasi Verifikasi Dokumen Upgrade Peran
    Route::post('/verifikasi-peran/{userId}', [AuthVerifikasiController::class, 'adminPutuskanPeran'])->name('verifikasi.peran');

    // Manajemen Komoditas
    Route::get('/komoditas', [AdminController::class, 'komoditas'])->name('komoditas');
    Route::post('/komoditas', [AdminController::class, 'storeKomoditas'])->name('komoditas.store');
    Route::put('/komoditas/{id}', [AdminController::class, 'updateKomoditas'])->name('komoditas.update');
    Route::delete('/komoditas/{id}', [AdminController::class, 'destroyKomoditas'])->name('komoditas.destroy');

    // Manajemen Pengguna
    Route::get('/pengguna', [AdminController::class, 'pengguna'])->name('pengguna');
    Route::put('/pengguna/{id}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [AdminController::class, 'destroyPengguna'])->name('pengguna.destroy');

    // Manajemen Panduan Teknis
    Route::get('/panduan', [AdminController::class, 'panduan'])->name('panduan');
    Route::post('/panduan', [AdminController::class, 'storePanduan'])->name('panduan.store');
    Route::delete('/panduan/{id}', [AdminController::class, 'destroyPanduan'])->name('panduan.destroy');
});