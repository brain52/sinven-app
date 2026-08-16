<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // Nomor PO / Referensi
            $table->string('title'); // Contoh: "Pengadaan Alat Lab Ganjil 2026"
            $table->date('order_date');
            $table->enum('status', ['Pending', 'Disetujui', 'Ditolak', 'Diterima'])->default('Pending');
            
            // Kolom Finansial (Sesuai Excel Anda)
            $table->decimal('total_items_cost', 15, 2)->default(0); // Sub Total
            $table->decimal('shipping_cost', 15, 2)->default(0);    // Ongkir
            $table->decimal('service_fee', 15, 2)->default(0);      // Biaya Layanan
            $table->decimal('grand_total', 15, 2)->default(0);      // Total Keseluruhan Harga

            $table->unsignedBigInteger('created_by'); // Siapa yang menginput
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};