<?php
namespace App\Services;

use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class MaintenanceService
{
    protected $maintenanceRepo;

    public function __construct(MaintenanceRepositoryInterface $maintenanceRepo)
    {
        $this->maintenanceRepo = $maintenanceRepo;
    }

    /**
     * ACTION 1: Melaporkan barang rusak.
     */
    public function reportDamage(array $data, $reporterId)
    {
        DB::beginTransaction();
        try {
            $item = Item::findOrFail($data['item_id']);

            if (strtolower($item->status) === 'dipinjam') {
                throw new Exception("Barang sedang dipinjam. Selesaikan transaksi peminjaman terlebih dahulu.");
            }

            $maintenanceData = [
                'item_id' => $item->id,
                'reported_by' => $reporterId,
                'problem_description' => $data['problem_description'],
                'reported_at' => Carbon::now(),
                // MENGGUNAKAN ENUM YANG SAH DI DATABASE
                'status' => 'Dilaporkan' 
            ];

            $maintenance = $this->maintenanceRepo->create($maintenanceData);
            $item->update(['status' => 'Rusak']);

            DB::commit();
            return $maintenance;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ACTION 2: Menyelesaikan perbaikan barang.
     */
    public function completeMaintenance($maintenanceId, $technicianId, array $data)
    {
        DB::beginTransaction();
        try {
            $maintenance = \App\Models\Maintenance::with('item')->findOrFail($maintenanceId);

            // Mengecek apakah statusnya sudah 'Selesai'
            if ($maintenance->status === 'Selesai') {
                throw new Exception("Pemeliharaan ini sudah ditandai selesai sebelumnya.");
            }

            $maintenance->update([
                'technician_id' => $technicianId,
                'completed_at' => Carbon::now(),
                'cost' => $data['cost'] ?? 0,
                // MENGGUNAKAN ENUM YANG SAH DI DATABASE
                'status' => 'Selesai',      
                'resolution_notes' => $data['resolution_notes']
            ]);

            $maintenance->item->update(['status' => 'Tersedia']);

            DB::commit();
            return $maintenance;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}