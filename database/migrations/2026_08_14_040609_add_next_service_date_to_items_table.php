<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('items', function (Blueprint $table) {
        // Menambahkan kolom tanggal servis berikutnya setelah kolom status
        $table->date('next_service_date')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('items', function (Blueprint $table) {
        $table->dropColumn('next_service_date');
    });
}
};
