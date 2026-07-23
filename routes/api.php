<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Semua API kita masukkan ke dalam prefix V1
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Master Data Lokasi
    Route::middleware(['role:Super Admin'])->apiResource('locations', \App\Http\Controllers\Api\V1\LocationController::class);

    // Area Operasional (Dilindungi LBAC)
    Route::middleware(['lbac.verify'])->group(function () {
        
        // Rute ini akan menjadi: /api/v1/{location_id}/items
        Route::prefix('{location_id}')->group(function () {
            Route::apiResource('items', \App\Http\Controllers\Api\V1\ItemController::class);
        });
        
    });
});