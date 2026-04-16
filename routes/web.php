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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Setup 2FA hanya bisa diakses kalau sudah login (untuk setting ulang)
    Route::get('/setup-2fa', function() {
        return view('auth.setup-2fa');
    })->name('setup.2fa');
});