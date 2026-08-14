<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Kita gunakan pengecekan agar tidak error jika kolom sudah ada
            if (!Schema::hasColumn('items', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable();
            }
            if (!Schema::hasColumn('items', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable();
            }
            if (!Schema::hasColumn('items', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'room_id', 'supplier_id']);
        });
    }
};