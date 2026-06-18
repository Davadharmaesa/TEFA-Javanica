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
        Schema::table('pesanan_keluar', function (Blueprint $table) {
            $table->foreignId('packaging_id')->nullable()->after('roasted_bean_id')->constrained('packagings')->onDelete('set null');
            $table->integer('jumlah_pcs')->nullable()->after('jumlah_gram_pesanan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_keluar', function (Blueprint $table) {
            $table->dropForeign(['packaging_id']);
            $table->dropColumn(['packaging_id', 'jumlah_pcs']);
        });
    }
};
