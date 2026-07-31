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
     * Endpoint untuk menampilkan daftar peminjaman.
     */
    public function index(Request $request, $location_id)
    {
        try {
            $borrowings = \App\Models\Borrowing::with(['item', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $borrowings
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk memproses pengajuan peminjaman barang.
     */
    public function store(Request $request, $location_id)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'duration_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        try {
            $borrowing = $this->borrowingService->borrowItem($validated, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil dicatat dan menunggu persetujuan.',
                'data' => $borrowing
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Endpoint untuk MENYETUJUI peminjaman (Approval)
     */
    public function approve(Request $request, $location_id, $id)
    {
        try {
            $borrowing = \App\Models\Borrowing::findOrFail($id);
            
            if ($borrowing->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengajuan berstatus pending yang bisa disetujui.'
                ], 422);
            }

            // Otorisasi: Ubah status dan catat siapa Admin yang menyetujui
            $borrowing->status = 'borrowed';
            $borrowing->admin_id = $request->user()->id; 
            $borrowing->save();

            // Ubah fisik barang menjadi 'dipinjam'
            $item = \App\Models\Item::findOrFail($borrowing->item_id);
            $item->status = 'dipinjam'; 
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil disetujui.',
                'data' => $borrowing
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint untuk MENOLAK peminjaman (Rejection)
     */
    public function reject(Request $request, $location_id, $id)
    {
        try {
            $borrowing = \App\Models\Borrowing::findOrFail($id);
            
            if ($borrowing->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengajuan berstatus pending yang bisa ditolak.'
                ], 422);
            }

            // Otorisasi: Ubah status menjadi ditolak
            $borrowing->status = 'rejected';
            $borrowing->admin_id = $request->user()->id;
            $borrowing->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman telah ditolak.',
                'data' => $borrowing
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint untuk memproses pengembalian barang.
     */
    public function returnItem(Request $request, $location_id, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            $borrowing = $this->borrowingService->returnItem(
                $id, 
                $request->user()->id, 
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dikembalikan dan tersedia kembali.',
                'data' => $borrowing
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}