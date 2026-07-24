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
     * Mengunci status barang menjadi "Rusak" agar tidak bisa dipinjam.
     */
    public function reportDamage(array $data, $reporterId)
    {
        DB::beginTransaction();
        try {
            $item = Item::findOrFail($data['item_id']);

            // Validasi: Barang yang sedang dipinjam tidak bisa langsung masuk perbaikan.
            // Harus dikembalikan terlebih dahulu.
            if ($item->status === 'Dipinjam') {
                throw new Exception("Barang sedang dipinjam. Selesaikan transaksi peminjaman terlebih dahulu.");
            }

            // Siapkan payload data untuk tabel maintenance
            $maintenanceData = [
                'item_id' => $item->id,
                'reported_by' => $reporterId,
                'problem_description' => $data['problem_description'],
                'reported_at' => Carbon::now(),
                'status' => 'Dilaporkan'
            ];

            // 1. Simpan log kerusakan
            $maintenance = $this->maintenanceRepo->create($maintenanceData);

            // 2. KUNCI status barang menjadi "Rusak"
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
     * Membebaskan kembali status barang menjadi "Tersedia".
     */
    public function completeMaintenance($maintenanceId, $technicianId, array $data)
    {
        DB::beginTransaction();
        try {
            // Kita gunakan model langsung untuk load relasi item-nya
            $maintenance = \App\Models\Maintenance::with('item')->findOrFail($maintenanceId);

            if ($maintenance->status === 'Selesai') {
                throw new Exception("Pemeliharaan ini sudah ditandai selesai sebelumnya.");
            }

            // 1. Update log pemeliharaan dengan biaya dan catatan resolusi
            $maintenance->update([
                'technician_id' => $technicianId,
                'completed_at' => Carbon::now(),
                'cost' => $data['cost'] ?? 0,
                'status' => 'Selesai',
                'resolution_notes' => $data['resolution_notes']
            ]);

            // 2. BEBASKAN status barang kembali ke "Tersedia"
            $maintenance->item->update(['status' => 'Tersedia']);

            DB::commit();
            return $maintenance;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}