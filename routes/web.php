<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoasteryController;
Route::get('/', function () {
    return view('login');
});
Route::get('/tes', [RoasteryController::class, 'CekSistem']);

// Rute Tampilan Testing (UI Login API)
Route::get('/login', function () {
    return view('login');
});

// Rute Lab Produksi (Roastery Dashboard)
use App\Http\Controllers\ProductionController;
Route::get('/production', [ProductionController::class, 'index']);
