<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Borrowing extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
     */
    protected $fillable = [
        'item_id', 'user_id', 'admin_id', 'borrowed_at', 
        'expected_return_at', 'returned_at', 'status', 'notes'
    ];

    /**
     * Casts tipe data tanggal agar otomatis menjadi objek Carbon
     */
    protected $casts = [
        'borrowed_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * Relasi: Satu peminjaman merujuk pada satu Barang (Item).
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi: Satu peminjaman dilakukan oleh satu User (Peminjam).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Satu peminjaman diproses oleh satu Admin Lab.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}