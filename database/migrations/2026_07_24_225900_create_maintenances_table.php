<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel maintenances.
     * Tabel ini berfungsi sebagai log kerusakan dan perbaikan aset.
     */
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke barang yang rusak
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            
            // Siapa yang melaporkan kerusakan (Admin Lab / Guru)
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            
            // Siapa teknisi yang memperbaiki (Opsional, bisa diisi saat selesai)
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Detail masalah kerusakan
            $table->text('problem_description');
            
            // Tanggal dilaporkan dan tanggal selesai perbaikan
            $table->dateTime('reported_at');
            $table->dateTime('completed_at')->nullable();
            
            // Biaya perbaikan (Total 15 digit, 2 angka di belakang koma)
            $table->decimal('cost', 15, 2)->default(0);
            
            // Status perbaikan
            $table->enum('status', ['Dilaporkan', 'Dalam Perbaikan', 'Selesai'])->default('Dilaporkan');
            
            // Catatan setelah perbaikan selesai (Misal: "Ganti RAM 8GB")
            $table->text('resolution_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};