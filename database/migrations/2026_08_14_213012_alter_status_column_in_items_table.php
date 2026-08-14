<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan DB di-import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah kolom status dari ENUM yang kaku menjadi VARCHAR yang fleksibel
        DB::statement("ALTER TABLE items MODIFY status VARCHAR(50) DEFAULT 'Tersedia'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM jika suatu saat migrasi di-rollback
        DB::statement("ALTER TABLE items MODIFY status ENUM('Tersedia', 'Dipinjam', 'Diservis', 'Rusak') DEFAULT 'Tersedia'");
    }
};