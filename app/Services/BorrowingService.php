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
     * Logika Bisnis: Memproses peminjaman barang.
     * Menggunakan Database Transaction agar jika satu proses gagal, semua dibatalkan.
     */
    public function borrowItem(array $data, $adminId)
    {
        DB::beginTransaction();
        try {
            // 1. Cari barang berdasarkan ID
            $item = Item::findOrFail($data['item_id']);

            // 2. Validasi: Pastikan barang sedang "Tersedia"
            if ($item->status !== 'Tersedia') {
                throw new Exception("Barang tidak tersedia untuk dipinjam. Status saat ini: {$item->status}");
            }

            // 3. Siapkan data transaksi peminjaman
            $borrowData = [
                'item_id' => $item->id,
                'user_id' => $data['user_id'],
                'admin_id' => $adminId,
                'borrowed_at' => Carbon::now(),
                // Misal durasi pinjam default adalah 3 hari (bisa disesuaikan dari request)
                'expected_return_at' => Carbon::now()->addDays($data['duration_days'] ?? 3),
                'status' => 'Dipinjam',
                'notes' => $data['notes'] ?? null
            ];

            // 4. Simpan transaksi ke tabel borrowings
            $borrowing = $this->borrowingRepo->create($borrowData);

            // 5. Update status barang di tabel items menjadi "Dipinjam"
            $item->update(['status' => 'Dipinjam']);

            DB::commit(); // Simpan semua perubahan secara permanen
            return $borrowing;

        } catch (Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika ada error
            throw $e;
        }
    }
    /**
     * Logika Bisnis: Memproses pengembalian barang.
     * 
     * @param int $borrowingId ID transaksi peminjaman
     * @param int $adminId ID admin yang memproses pengembalian
     * @param string|null $notes Catatan kondisi barang saat dikembalikan
     */
    public function returnItem($borrowingId, $adminId, $notes = null)
    {
        DB::beginTransaction();
        try {
            // 1. Cari data transaksi peminjaman berdasarkan ID
            $borrowing = $this->borrowingRepo->update($borrowingId, []); // dummy update untuk memastikan ada, atau pakai model langsung

            // Kita gunakan model langsung untuk kemudahan relasi
            $borrowing = \App\Models\Borrowing::with('item')->findOrFail($borrowingId);

            // 2. Validasi: Jangan proses jika barang sudah dikembalikan
            if ($borrowing->status !== 'Dipinjam') {
                throw new Exception("Transaksi ini sudah selesai atau berstatus: {$borrowing->status}");
            }

            // 3. Update data transaksi peminjaman
            $borrowing->update([
                'returned_at' => Carbon::now(),
                'status' => 'Dikembalikan',
                // Jika ada catatan baru, gabungkan dengan catatan lama
                'notes' => $notes ? ($borrowing->notes . ' | Return Note: ' . $notes) : $borrowing->notes,
                // Mencatat admin siapa yang menerima pengembalian ini (bisa saja admin shift siang)
                'admin_id' => $adminId 
            ]);

            // 4. Bebaskan status barang kembali menjadi "Tersedia"
            $borrowing->item->update(['status' => 'Tersedia']);

            DB::commit();
            return $borrowing;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}