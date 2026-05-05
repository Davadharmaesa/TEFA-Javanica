<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\Api\AuthController;

// ==========================================
// AREA PUBLIK (Bisa diakses tanpa login)
// ==========================================
Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// PENTING: verify-2fa harus di LUAR middleware 'auth' 
// karena status user saat ini masih "setengah login"
Route::post('/verify-2fa', [AuthController::class, 'verify2fa'])->name('verify.2fa');


// ==========================================
// AREA TERTUTUP (Harus Lolos Login & OTP)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // ------------------------------------------
    // DASHBOARD & GLOBAL AUTHS
    // ------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Setup 2FA hanya bisa diakses kalau sudah login (untuk setting ulang)
    Route::get('/setup-2fa', function() {
        return view('auth.setup-2fa');
    })->name('setup.2fa');

    // ------------------------------------------
    // MODUL LAB PRODUKSI (OPERATOR)
    // ------------------------------------------
    Route::prefix('production')->name('production.')->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::get('/log-kerja', function() {
            return view('log-kerja');
        })->name('log_kerja');
    });

    // ------------------------------------------
    // INTERNAL AJAX ENDPOINTS (Untuk AlpineJS & Fitur Dinamis)
    // ------------------------------------------
    Route::prefix('internal-api')->group(function () {
        Route::get('/roasting/green-beans', [\App\Http\Controllers\Api\RoastingApiController::class, 'getGreenBeans']);
        Route::get('/roasting/roasted-beans', [\App\Http\Controllers\Api\RoastingApiController::class, 'getRoastedBeans']);
        Route::get('/roasting/batches', [\App\Http\Controllers\Api\RoastingApiController::class, 'getBatches']);
        Route::get('/roasting/stats', [\App\Http\Controllers\Api\RoastingApiController::class, 'getStats']);
        Route::post('/roasting/batches', [\App\Http\Controllers\Api\RoastingApiController::class, 'storeBatch']);

        // Master Data API
        Route::get('/master/packagings', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getPackagings']);
        Route::post('/master/packagings', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storePackaging']);
        Route::put('/master/packagings/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updatePackaging']);
        Route::delete('/master/packagings/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyPackaging']);

        Route::get('/master/biaya', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getBiaya']);
        Route::post('/master/biaya', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeBiaya']);
        Route::put('/master/biaya/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateBiaya']);
        Route::delete('/master/biaya/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyBiaya']);

        Route::get('/master/green-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getGreenBeans']);
        Route::post('/master/green-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeGreenBean']);
        Route::put('/master/green-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateGreenBean']);
        Route::delete('/master/green-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyGreenBean']);
        Route::get('/master/roasted-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getRoastedBeans']);
        Route::post('/master/roasted-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeRoastedBean']);
        Route::put('/master/roasted-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateRoastedBean']);
        Route::delete('/master/roasted-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyRoastedBean']);
    });

    // ------------------------------------------
    // MASTER DATA
    // ------------------------------------------
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/', function() {
            return view('master');
        })->name('index');
    });

    // ------------------------------------------
    // MODUL GUDANG (INVENTORY)
    // ------------------------------------------
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InventoryController::class, 'index'])->name('index');
    });

    // ------------------------------------------
    // MODUL KASIR / PENJUALAN
    // ------------------------------------------
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SalesController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SalesController::class, 'create'])->name('create');
    });

    // ------------------------------------------
    // MODUL PELAPORAN / MANAJERIAL (ADMIN)
    // ------------------------------------------
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
    });

    // ------------------------------------------
    // MODUL SISTEM / PENGATURAN (ADMIN)
    // ------------------------------------------
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function() {
            return "<div style='padding:50px; text-align:center;'><h2> Modul Pengaturan Karyawan </h2><p>Halaman sedang dirakit...</p><a href='/dashboard'>Kembali</a></div>";
        })->name('index');
    });

});