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
        Schema::table('green_bean', function (Blueprint $table) {
            $table->renameColumn('harga_jual', 'harga_beli');
            $table->date('tanggal_beli')->nullable()->after('asal_kopi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('green_bean', function (Blueprint $table) {
            $table->renameColumn('harga_beli', 'harga_jual');
            $table->dropColumn('tanggal_beli');
        });
    }
};
