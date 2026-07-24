<?php
namespace App\Repositories\Eloquent;

use App\Models\Maintenance;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    /**
     * Simpan data log kerusakan baru ke tabel maintenances.
     */
    public function create(array $data)
    {
        return Maintenance::create($data);
    }

    /**
     * Update data pemeliharaan yang sudah ada (Misal: saat perbaikan selesai).
     */
    public function update($id, array $data)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update($data);
        return $maintenance;
    }
}