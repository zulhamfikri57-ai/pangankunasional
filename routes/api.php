<?php

use App\Http\Controllers\Api\MobileApiController;
use Illuminate\Support\Facades\Route;

// Endpoint Publik (Bisa diakses sebelum login di Flutter)
Route::post('/login', [MobileApiController::class, 'login']);
Route::get('/komoditas', [MobileApiController::class, 'komoditas']);
Route::get('/pasar-panen', [MobileApiController::class, 'pasarPanen']);
Route::get('/spk-distribusi', [MobileApiController::class, 'spkDistribusi']);

// Endpoint Terproteksi Token Bearer (Laravel Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MobileApiController::class, 'logout']);
    Route::get('/dashboard', [MobileApiController::class, 'dashboard']);
    Route::post('/produksi', [MobileApiController::class, 'storeProduksi']);
    Route::post('/pasar-panen', [MobileApiController::class, 'storePasarPanen']);
});