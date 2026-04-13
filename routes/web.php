<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoasteryController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/tes', [RoasteryController::class, 'CekSistem']);
