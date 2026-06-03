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
        Schema::table('packagings', function (Blueprint $table) {
            $table->string('satuan')->default('pcs')->after('kapasitas_gram');
            $table->integer('harga_total_pembelian')->default(0)->after('harga_satuan');
            $table->integer('jumlah_qty_pembelian')->default(0)->after('harga_total_pembelian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packagings', function (Blueprint $table) {
            $table->dropColumn(['satuan', 'harga_total_pembelian', 'jumlah_qty_pembelian']);
        });
    }
};
