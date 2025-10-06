<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\POSController;

// API routes are stateless and automatically use the 'api' middleware group
Route::middleware(['auth', 'permission:pos.access'])->group(function () {
    Route::post('/pos/checkout', [POSController::class, 'checkout']);
    Route::get('/pos/search-product', [POSController::class, 'searchProduct']);
    Route::post('/pos/calculate-total', [POSController::class, 'calculateTotal']);
});