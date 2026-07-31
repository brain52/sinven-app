<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom role, secara default (bawaan) semua akun baru adalah 'user'
            $table->string('role')->default('user')->after('password');
        });

        // Trik Cepat: Jadikan akun pertama Anda ("Admin Utama") sebagai admin
        DB::table('users')->where('id', 1)->update(['role' => 'admin']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};