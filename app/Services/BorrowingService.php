<?php

namespace App\Services;

use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BorrowingService
{
    protected $borrowingRepo;

    public function __construct(BorrowingRepositoryInterface $borrowingRepo)
    {
        $this->borrowingRepo = $borrowingRepo;
    }

    /**
     * Logika Bisnis: Memproses PENGAJUAN peminjaman barang.
     */
    public function borrowItem(array $data, $userId) // Mengubah parameter menjadi $userId karena yang meminjam adalah user/staf
    {
        DB::beginTransaction();
        try {
            $item = Item::findOrFail($data['item_id']);

            // 1. Validasi: Pastikan barang sedang "Tersedia"
            if (strtolower($item->status) !== 'tersedia') {
                throw new Exception("Barang tidak tersedia. Status saat ini: {$item->status}");
            }

            // 2. Proteksi Ganda: Cek apakah barang ini sedang diajukan oleh orang lain dan belum direspons Admin
            $hasPending = \App\Models\Borrowing::where('item_id', $item->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                throw new Exception("Barang ini sedang dalam proses antrean pengajuan oleh pengguna lain.");
            }

            // 3. Siapkan data transaksi pengajuan
            $borrowData = [
                'item_id' => $item->id,
                'user_id' => $data['user_id'], // User/Staf yang meminjam
                'admin_id' => null, // KOSONGKAN DULU! Belum ada admin yang menyetujui
                'borrowed_at' => Carbon::now(),
                'expected_return_at' => Carbon::now()->addDays($data['duration_days'] ?? 3),
                'status' => 'pending', // STATUS DITAHAN: PENDING
                'notes' => $data['notes'] ?? null
            ];

            // 4. Simpan ke database
            $borrowing = $this->borrowingRepo->create($borrowData);

            // CATATAN PENTING: Kita TIDAK MENGUBAH status fisik $item di sini.
            // Status item akan diubah menjadi 'dipinjam' di Controller SAAT Admin menekan Approve.

            DB::commit();
            return $borrowing;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Logika Bisnis: Memproses pengembalian barang.
     */
    public function returnItem($borrowingId, $adminId, $notes = null)
    {
        DB::beginTransaction();
        try {
            $borrowing = \App\Models\Borrowing::with('item')->findOrFail($borrowingId);

            // 1. Validasi: Pastikan barang statusnya memang sedang dipinjam (borrowed)
            // Ubah menjadi memvalidasi kata 'Dipinjam'
            if (strtolower($borrowing->status) !== 'dipinjam' && strtolower($borrowing->status) !== 'borrowed') {
                throw new Exception("Transaksi gagal. Status transaksi ini adalah: " . $borrowing->status);
            }

            // 2. Update status transaksi menjadi dikembalikan
            $borrowing->update([
                'returned_at' => Carbon::now(),
                'status' => 'returned',
                'notes' => $notes ? ($borrowing->notes . ' | Return Note: ' . $notes) : $borrowing->notes,
                'admin_id' => $adminId // Admin yang mengeksekusi pengembalian
            ]);

            // 3. Bebaskan status fisik barang kembali menjadi "Tersedia"
            $borrowing->item->update(['status' => 'Tersedia']);

            DB::commit();
            return $borrowing;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
