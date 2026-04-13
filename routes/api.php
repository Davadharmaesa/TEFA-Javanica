<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GreenBeanController;
use App\Http\Controllers\Api\RoastedBeanController;
use App\Http\Controllers\Api\RoastingBatchController;
use App\Http\Controllers\Api\PesananKeluarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route API TEFA Javanica didaftarkan di sini.
| Sengaja diringkas pakai apiResource() biar programmer
| gak perlu nulis route GET POST PUT DELETE satu-satu.
|
*/

// TODO: Nanti kalau fitur Login sudah dibuat anak koding, pindahkan semua ini ke dalam middleware auth:sanctum
Route::prefix('v1')->group(function () {
    
    // API Tabel Gudang Kopi Mentah
    Route::apiResource('green-beans', GreenBeanController::class);
    
    // API Tabel Produk Kopi Matang
    Route::apiResource('roasted-beans', RoastedBeanController::class);
    
    // API Tabel Aktivitas Roasting (Pak Deka masak kopi)
    Route::apiResource('roasting-batches', RoastingBatchController::class);
    
    // API Form Kasir "Mode Timbangan Dinamis"
    Route::apiResource('pesanan-keluar', PesananKeluarController::class);

});
