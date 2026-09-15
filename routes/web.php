<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\Api\AuthController;

// ==========================================
// AREA PUBLIK
// ==========================================
Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/verify-2fa', [AuthController::class, 'verify2fa'])->name('verify.2fa');


// ==========================================
// AREA TERTUTUP (LOGIN)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ------------------------------------------
    // GLOBAL
    // ------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/internal-api/search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('internal-api.search');

    Route::get('/setup-2fa', function () {
        return view('auth.setup-2fa');
    })->name('setup.2fa');


    // ==========================================
    // 🧑‍🏭 SHARING AREA (Operator & PIC)
    // ==========================================
    Route::middleware(['role:Operator,PIC'])->group(function () {

        // Production / Roasting
        Route::prefix('production')->name('production.')->group(function () {
            Route::get('/', [ProductionController::class, 'index'])->name('index');
        });

        // Log Pekerja
        Route::get('/log-kerja', function () {
            return view('log-kerja');
        })->name('log_kerja.index');

        // Inventory (PIC & Operator bisa lihat dan edit)
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\InventoryController::class, 'index'])->name('index');
            Route::post('/green-bean', [\App\Http\Controllers\InventoryController::class, 'storeGreenBean'])->name('store-green-bean');
            Route::put('/green-bean/{id}', [\App\Http\Controllers\InventoryController::class, 'updateGreenBean'])->name('update-green-bean');
            Route::delete('/green-bean/{id}', [\App\Http\Controllers\InventoryController::class, 'destroyGreenBean'])->name('destroy-green-bean');

            Route::post('/roasted-bean', [\App\Http\Controllers\InventoryController::class, 'storeRoastedBean'])->name('store-roasted-bean');
            Route::put('/roasted-bean/{id}', [\App\Http\Controllers\InventoryController::class, 'updateRoastedBean'])->name('update-roasted-bean');
            Route::delete('/roasted-bean/{id}', [\App\Http\Controllers\InventoryController::class, 'destroyRoastedBean'])->name('destroy-roasted-bean');

            Route::post('/packaging', [\App\Http\Controllers\InventoryController::class, 'storePackaging'])->name('store-packaging');
            Route::put('/packaging/{id}', [\App\Http\Controllers\InventoryController::class, 'updatePackaging'])->name('update-packaging');
            Route::delete('/packaging/{id}', [\App\Http\Controllers\InventoryController::class, 'destroyPackaging'])->name('destroy-packaging');
        });

        // Sales (PIC & Operator)
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SalesController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SalesController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\SalesController::class, 'store'])->name('store');
            Route::post('/{id}/payment', [\App\Http\Controllers\SalesController::class, 'addPayment'])->name('payment.store');
            Route::get('/{id}/edit', [\App\Http\Controllers\SalesController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\SalesController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\SalesController::class, 'destroy'])->name('destroy');
        });

        // Internal API: Kasir Timbangan Dinamis
        Route::prefix('internal-api/sales')->group(function () {
            Route::post('/cashier', [\App\Http\Controllers\SalesController::class, 'storeCashierSale']);
            Route::get('/cashier/history', [\App\Http\Controllers\SalesController::class, 'getCashierHistory']);
            Route::get('/cashier/roasted-beans', [\App\Http\Controllers\SalesController::class, 'getRoastedBeansStock']);
        });

        // INTERNAL API KHUSUS PRODUKSI (ROASTING & LOG PEKERJA)
        Route::prefix('internal-api')->group(function () {
            Route::get('/roasting/green-beans', [\App\Http\Controllers\Api\RoastingApiController::class, 'getGreenBeans']);
            Route::get('/roasting/roasted-beans', [\App\Http\Controllers\Api\RoastingApiController::class, 'getRoastedBeans']);
            Route::get('/roasting/batches', [\App\Http\Controllers\Api\RoastingApiController::class, 'getBatches']);
            Route::get('/roasting/stats', [\App\Http\Controllers\Api\RoastingApiController::class, 'getStats']);
            Route::post('/roasting/batches', [\App\Http\Controllers\Api\RoastingApiController::class, 'storeBatch']);

            // Proses Pengemasan
            Route::post('/pengemasan/process', [\App\Http\Controllers\Api\PackagingApiController::class, 'storePackagingProcess']);

            // Log Pekerja API
            Route::get('/log-kerja/batches', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'getActiveBatches']);
            Route::get('/log-kerja/pekerja', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'getPekerja']);
            Route::get('/log-kerja/tugas', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'getTugas']);
            Route::get('/log-kerja/history', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'getHistory']);
            Route::post('/log-kerja', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'store']);
            Route::put('/log-kerja/{id}', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'update']);
            Route::delete('/log-kerja/{id}', [\App\Http\Controllers\Api\LogPekerjaApiController::class, 'destroy']);
        });
    });

    // ==========================================
    // 🧑‍💼 ADMIN / PIC AREA
    // ==========================================
    Route::middleware(['role:PIC'])->group(function () {

        // Master Data
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/', function () {
                return view('master');
            })->name('index');
        });

        // Kelola User
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
            Route::get('/whatsapp/status', [\App\Http\Controllers\UserController::class, 'getWaStatus'])->name('wa.status');
            Route::post('/whatsapp/logout', [\App\Http\Controllers\UserController::class, 'logoutWa'])->name('wa.logout');
        });


        // Route production sudah dipindah ke shared area di atas

            // Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
            });

            // Kalkulator HPP
            Route::prefix('hpp-calculator')->name('hpp.')->group(function () {
                Route::get('/', [\App\Http\Controllers\HppController::class, 'index'])->name('index');
                Route::post('/store', [\App\Http\Controllers\HppController::class, 'store'])->name('store');
            });

            // INTERNAL API KHUSUS ADMIN
            Route::prefix('internal-api')->group(function () {
                
                Route::post('/hpp-calculator/preview', [\App\Http\Controllers\HppController::class, 'calculatePreview']);

                Route::prefix('master')->group(function () {

                Route::get('/packagings', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getPackagings']);
                Route::post('/packagings', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storePackaging']);
                Route::put('/packagings/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updatePackaging']);
                Route::delete('/packagings/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyPackaging']);

                Route::get('/biaya', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getBiaya']);
                Route::post('/biaya', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeBiaya']);
                Route::put('/biaya/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateBiaya']);
                Route::delete('/biaya/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyBiaya']);

                Route::get('/green-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getGreenBeans']);
                Route::post('/green-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeGreenBean']);
                Route::put('/green-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateGreenBean']);
                Route::delete('/green-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyGreenBean']);

                Route::get('/roasted-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'getRoastedBeans']);
                Route::post('/roasted-beans', [\App\Http\Controllers\Api\MasterDataApiController::class, 'storeRoastedBean']);
                Route::put('/roasted-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'updateRoastedBean']);
                Route::delete('/roasted-beans/{id}', [\App\Http\Controllers\Api\MasterDataApiController::class, 'destroyRoastedBean']);
                });
            });
        });
    });