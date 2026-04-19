<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GreenBeanController;
use App\Http\Controllers\Api\RoastedBeanController;
use App\Http\Controllers\Api\RoastingBatchController;
use App\Http\Controllers\Api\PesananKeluarController;

use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route API TEFA Javanica didaftarkan di sini.
|
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // AREA TERTUTUP (Harus Punya Token Sanctum)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function() {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::apiResource('green-beans', GreenBeanController::class);
    
    // API Tabel Produk Kopi Matang
    Route::apiResource('roasted-beans', RoastedBeanController::class);
    
    // (Dipindahkan ke web.php untuk menggunakan proteksi CSRF Session Blade)
    
    // API Form Kasir "Mode Timbangan Dinamis"
    Route::apiResource('pesanan-keluar', PesananKeluarController::class);
    });
});
