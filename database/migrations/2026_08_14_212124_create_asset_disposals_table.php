<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('asset_disposals', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('item_id');
        $table->string('disposal_type'); // Tipe: Rusak Berat, Hilang, Dijual, Dihibahkan
        $table->text('reason');          // Alasan detail / Berita Acara
        $table->unsignedBigInteger('disposed_by'); // Admin yang memproses
        $table->date('disposal_date');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
