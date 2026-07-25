<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// RUTE PUBLIK (TIDAK BUTUH LOGIN)
// Posisinya WAJIB di luar middleware auth!
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ==========================================
// RUTE OTENTIKASI (Butuh Middleware Web untuk Sesi)
// ==========================================
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// ==========================================
// RUTE PRIVAT (WAJIB LOGIN)
// Semua API kita masukkan ke dalam prefix V1
// ==========================================
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Master Data Lokasi
    Route::middleware(['role:Super Admin'])->apiResource('locations', \App\Http\Controllers\Api\V1\LocationController::class);

    // Area Operasional (Dilindungi LBAC)
    Route::middleware(['lbac.verify'])->group(function () {

        // Rute ini akan menjadi: /api/v1/{location_id}/items
        Route::prefix('{location_id}')->group(function () {
            
            Route::apiResource('items', \App\Http\Controllers\Api\V1\ItemController::class);
            
            // Endpoint Peminjaman Baru
            Route::post('borrowings', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'store']);
            
            // Endpoint Pengembalian Barang (BARU)
            Route::post('borrowings/{id}/return', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'returnItem']);
            
            // --- ENDPOINT PEMELIHARAAN (BARU) ---
            Route::post('maintenances', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'store']);
            Route::post('maintenances/{id}/complete', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'complete']);
            
            // Endpoint Cetak QR Code (BARU)
            Route::get('items/{id}/qrcode', [\App\Http\Controllers\Api\V1\ItemController::class, 'generateQr']);
            
        });
    });
});