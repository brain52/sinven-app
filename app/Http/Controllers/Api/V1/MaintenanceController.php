<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Exception;

class MaintenanceController extends Controller
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Endpoint untuk merekam pelaporan barang rusak.
     */
    public function store(Request $request, $location_id)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'problem_description' => 'required|string|min:5'
        ]);

        try {
            // Meneruskan request ke Service Layer
            $maintenance = $this->maintenanceService->reportDamage($validated, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Laporan kerusakan berhasil dicatat. Status barang kini Rusak.',
                'data' => $maintenance
            ], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Endpoint untuk mencatat bahwa perbaikan telah selesai.
     */
    public function complete(Request $request, $location_id, $id)
    {
        $validated = $request->validate([
            'cost' => 'nullable|numeric|min:0',
            'resolution_notes' => 'required|string|min:5'
        ]);

        try {
            // Menyelesaikan perbaikan dan mencatat teknisi (user yang sedang login)
            $maintenance = $this->maintenanceService->completeMaintenance($id, $request->user()->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Perbaikan selesai. Barang kembali tersedia.',
                'data' => $maintenance
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}