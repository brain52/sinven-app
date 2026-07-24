<?php
namespace App\Repositories\Eloquent;

use App\Models\Borrowing;
use App\Repositories\Contracts\BorrowingRepositoryInterface;

class BorrowingRepository implements BorrowingRepositoryInterface
{
    /**
     * Menyimpan data transaksi peminjaman baru ke database.
     */
    public function create(array $data)
    {
        return Borrowing::create($data);
    }

    /**
     * Mencari transaksi peminjaman yang masih aktif (belum dikembalikan)
     * berdasarkan ID barang.
     */
    public function getActiveBorrowingByItem($itemId)
    {
        return Borrowing::where('item_id', $itemId)
                        ->where('status', 'Dipinjam')
                        ->first();
    }

    /**
     * Memperbarui data peminjaman (misal: saat pengembalian).
     */
    public function update($id, array $data)
    {
        $borrowing = Borrowing::findOrFail($id);
        $borrowing->update($data);
        return $borrowing;
    }
}