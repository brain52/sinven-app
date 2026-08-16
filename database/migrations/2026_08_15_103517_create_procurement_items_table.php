<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_id'); // Relasi ke tabel induk
            
            $table->string('item_name'); // Nama Barang dari Excel
            $table->integer('quantity'); // Kuantitas
            $table->decimal('unit_price', 15, 2); // Harga Produk
            $table->decimal('subtotal', 15, 2); // Qty * Harga Produk
            
            // PENTING: Untuk membedakan mana yang perlu masuk Master Data, mana yang hanya stok habis pakai
            $table->enum('item_type', ['Asset', 'Consumable'])->default('Asset'); 
            
            $table->timestamps();

            // Kunci relasi (Foreign Key)
            $table->foreign('procurement_id')->references('id')->on('procurements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_items');
    }
};