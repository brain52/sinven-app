<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel borrowings.
     */
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke barang yang dipinjam
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            
            // Relasi ke user peminjam (Guru/Siswa)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Relasi ke admin lab yang memproses transaksi (Opsional/Bisa Null jika self-service)
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Waktu transaksi
            $table->dateTime('borrowed_at'); // Kapan dipinjam
            $table->dateTime('expected_return_at'); // Tenggat waktu pengembalian
            $table->dateTime('returned_at')->nullable(); // Kapan dikembalikan (Null = belum kembali)
            
            // Status Peminjaman
            $table->enum('status', ['Dipinjam', 'Dikembalikan', 'Terlambat'])->default('Dipinjam');
            
            // Catatan kondisi saat dipinjam/dikembalikan
            $table->text('notes')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};