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

    public function index(Request $request, $location_id)
    {
        try {
            $maintenances = \App\Models\Maintenance::with(['item'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $maintenances
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $location_id)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'problem_description' => 'required|string|min:5'
        ]);

        try {
            $maintenance = $this->maintenanceService->reportDamage($validated, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Laporan kerusakan berhasil dicatat.',
                'data' => $maintenance
            ], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function complete(Request $request, $location_id, $id)
    {
        // 1. Tambahkan validasi untuk next_service_date
        $validated = $request->validate([
            'cost' => 'nullable|numeric|min:0',
            'resolution_notes' => 'required|string|min:5',
            'next_service_date' => 'nullable|date' 
        ]);

        try {
            // 2. Jalankan logika penyelesaian utama melalui Service Anda
            $maintenance = $this->maintenanceService->completeMaintenance($id, $request->user()->id, $validated);

            // 3. Tangkap dan simpan Jadwal Servis Berikutnya (Kalibrasi) ke tabel Item
            if ($request->filled('next_service_date')) {
                // Pastikan model Item di-import atau dipanggil secara absolut
                $item = \App\Models\Item::find($maintenance->item_id);
                if ($item) {
                    $item->next_service_date = $request->next_service_date;
                    $item->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Perbaikan selesai. Barang kembali tersedia dan jadwal pemeliharaan diperbarui.',
                'data' => $maintenance
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}