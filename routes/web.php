<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoasteryController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/tes', [RoasteryController::class, 'CekSistem']);

// Rute Tampilan Testing (UI Login API)
Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
