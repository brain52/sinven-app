<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_code', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('condition_id')->constrained('conditions');
            $table->string('serial_number', 100)->nullable()->unique();
            $table->date('purchase_date')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('status', ['Tersedia', 'Dipinjam', 'Rusak', 'Dihapus'])->default('Tersedia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
