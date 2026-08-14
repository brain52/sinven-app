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
        Schema::table('items', function (Blueprint $table) {
            // Cek dan buat kolom jika belum ada
            if (!Schema::hasColumn('items', 'brand')) {
                $table->string('brand')->nullable()->after('name');
            }
            if (!Schema::hasColumn('items', 'unit')) {
                $table->string('unit')->default('Piece (pcs)')->after('brand');
            }
            if (!Schema::hasColumn('items', 'room')) {
                $table->string('room')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('items', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('room');
            }
            if (!Schema::hasColumn('items', 'supplier')) {
                $table->string('supplier')->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('items', 'tech_specs')) {
                $table->text('tech_specs')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('items', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('tech_specs');
            }
            if (!Schema::hasColumn('items', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('items', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('purchase_price');
            }
            if (!Schema::hasColumn('items', 'funding_source')) {
                $table->string('funding_source')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('items', 'warranty_months')) {
                $table->integer('warranty_months')->nullable()->after('funding_source');
            }
            if (!Schema::hasColumn('items', 'warranty_expiry')) {
                $table->date('warranty_expiry')->nullable()->after('warranty_months');
            }
            if (!Schema::hasColumn('items', 'condition')) {
                $table->string('condition')->default('Good Condition')->after('status');
            }
            if (!Schema::hasColumn('items', 'notes')) {
                $table->text('notes')->nullable()->after('condition');
            }
            if (!Schema::hasColumn('items', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
