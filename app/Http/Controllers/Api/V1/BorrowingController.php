<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Exception;

class BorrowingController extends Controller
{
    protected $borrowingService;

    public function __construct(BorrowingService $borrowingService)
    {
        $this->borrowingService = $borrowingService;
    }

    /**
     * Endpoint untuk memproses peminjaman barang.
     */
    public function store(Request $request, $location_id)
    {
        // Validasi input dari user
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'duration_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        try {
            // Memanggil service untuk memproses peminjaman (mengirim ID admin yang sedang login)
            $borrowing = $this->borrowingService->borrowItem($validated, $request->user()->id);

            // JSRF: JSON Standardized Response Format
            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil dicatat.',
                'data' => $borrowing
            ], 201);

        } catch (Exception $e) {
            // Menangkap error dari Service (misal: Barang tidak tersedia)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422); // 422 Unprocessable Entity
        }
    }
    /**
     * Endpoint untuk memproses pengembalian barang.
     * Menggunakan method POST karena ini adalah action khusus (bukan sekadar update data biasa).
     */
    public function returnItem(Request $request, $location_id, $id)
    {
        // Validasi opsional: Admin bisa menambahkan catatan barang (misal: "Layar sedikit tergores")
        $validated = $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            // Panggil Service untuk mengeksekusi logika pengembalian
            $borrowing = $this->borrowingService->returnItem(
                $id, 
                $request->user()->id, 
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dikembalikan dan tersedia kembali.',
                'data' => $borrowing
            ], 200); // 200 OK

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}