<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Menambahkan kolom relasi baru
            $table->unsignedBigInteger('brand_id')->nullable()->after('brand');
            $table->unsignedBigInteger('room_id')->nullable()->after('room');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('supplier');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'room_id', 'supplier_id']);
        });
    }
};