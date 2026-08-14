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
    Schema::create('asset_transfers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('item_id');
        $table->unsignedBigInteger('from_location_id'); // Ruangan asal
        $table->unsignedBigInteger('to_location_id');   // Ruangan tujuan
        $table->unsignedBigInteger('transferred_by');   // Admin yang memindahkan
        $table->date('transfer_date');
        $table->text('reason')->nullable();             // Alasan dipindah
        $table->timestamps();

        // (Opsional) Foreign keys jika Anda menggunakan strict relasi
        // $table->foreign('item_id')->references('id')->on('items');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};
