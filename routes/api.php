<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BorrowingController;
use App\Http\Controllers\Api\V1\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// RUTE PUBLIK & OTENTIKASI
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


// ==========================================
// RUTE PRIVAT (WAJIB LOGIN)
// Semua API kita masukkan ke dalam prefix V1
// ==========================================
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // --- RUTE KONDISI ---
    Route::apiResource('conditions', \App\Http\Controllers\Api\V1\ConditionController::class);

    // --- MASTER DATA ---
    Route::apiResource('users', \App\Http\Controllers\Api\V1\UserController::class);

    Route::apiResource('rooms', \App\Http\Controllers\Api\V1\RoomController::class);
    Route::apiResource('brands', \App\Http\Controllers\Api\V1\BrandController::class);
    Route::apiResource('suppliers', \App\Http\Controllers\Api\V1\SupplierController::class);
    
    // Ini sudah mencakup semua rute Kategori (GET, POST, PUT, DELETE)
    Route::apiResource('categories', CategoryController::class);

    // Master Data Lokasi
    Route::middleware(['role:Super Admin'])->apiResource('locations', \App\Http\Controllers\Api\V1\LocationController::class);


    // Area Operasional (Dilindungi LBAC)
    Route::middleware(['lbac.verify'])->group(function () {

        Route::prefix('{location_id}')->group(function () {

            Route::apiResource('items', \App\Http\Controllers\Api\V1\ItemController::class);

            // --- ENDPOINT PEMINJAMAN ---
            Route::get('borrowings', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'index']);
            Route::post('borrowings', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'store']);
            Route::post('borrowings/{id}/return', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'returnItem']);
            Route::post('borrowings/{id}/approve', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'approve']);
            Route::post('borrowings/{id}/reject', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'reject']);

            // --- ENDPOINT PEMELIHARAAN ---
            Route::get('maintenances', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'index']);
            Route::post('maintenances', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'store']);
            Route::post('maintenances/{id}/complete', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'complete']);

            // --- ENDPOINT CETAK QR CODE ---
            Route::get('items/{id}/qrcode', [\App\Http\Controllers\Api\V1\ItemController::class, 'generateQr']);

            // --- DASHBOARD ---
            Route::get('dashboard', [\App\Http\Controllers\Api\V1\DashboardController::class, 'index']);
        });
    });
});