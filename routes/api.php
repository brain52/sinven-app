<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// RUTE PUBLIK & OTENTIKASI
// (Cukup didefinisikan satu kali saja agar tidak konflik)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


// ==========================================
// RUTE PRIVAT (WAJIB LOGIN)
// Semua API kita masukkan ke dalam prefix V1
// ==========================================
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // --- RUTE KATEGORI & KONDISI (Dikembalikan untuk form Data Barang) ---
    Route::get('/categories', function () {
        return response()->json([
            'success' => true, 
            'data' => \App\Models\Category::all()
        ], 200);
    });

    Route::get('/conditions', function () {
        return response()->json([
            'success' => true, 
            'data' => \App\Models\Condition::all()
        ], 200);
    });
    // ---------------------------------------------------------

    // Master Data Lokasi
    Route::middleware(['role:Super Admin'])->apiResource('locations', \App\Http\Controllers\Api\V1\LocationController::class);

    // Area Operasional (Dilindungi LBAC)
    Route::middleware(['lbac.verify'])->group(function () {

        Route::prefix('{location_id}')->group(function () {
            
            Route::apiResource('items', \App\Http\Controllers\Api\V1\ItemController::class);
            
            // --- ENDPOINT PEMINJAMAN ---
            // [DITAMBAHKAN KEMBALI] Endpoint untuk mengambil daftar peminjaman
            Route::get('borrowings', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'index']);
            Route::post('borrowings', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'store']);
            Route::post('borrowings/{id}/return', [\App\Http\Controllers\Api\V1\BorrowingController::class, 'returnItem']);
            
            // --- ENDPOINT PEMELIHARAAN ---
            Route::post('maintenances', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'store']);
            Route::post('maintenances/{id}/complete', [\App\Http\Controllers\Api\V1\MaintenanceController::class, 'complete']);
            
            // --- ENDPOINT CETAK QR CODE ---
            Route::get('items/{id}/qrcode', [\App\Http\Controllers\Api\V1\ItemController::class, 'generateQr']);
            
        });
    });
});