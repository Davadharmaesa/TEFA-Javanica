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
            $table->string('metode_pembayaran')->default('lunas')->after('status_pembayaran');
            $table->bigInteger('nominal_dp')->nullable()->after('metode_pembayaran');
            $table->bigInteger('sisa_pembayaran')->nullable()->after('nominal_dp');
            $table->date('tanggal_bayar_dp')->nullable()->after('sisa_pembayaran');
            $table->date('tanggal_pelunasan')->nullable()->after('tanggal_bayar_dp');
            $table->date('batas_waktu_pembayaran')->nullable()->after('tanggal_pelunasan');
            $table->string('status_pengambilan')->default('Belum Diambil')->after('status_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_keluar', function (Blueprint $table) {
            $table->dropColumn([
                'metode_pembayaran',
                'nominal_dp',
                'sisa_pembayaran',
                'tanggal_bayar_dp',
                'tanggal_pelunasan',
                'batas_waktu_pembayaran',
                'status_pengambilan'
            ]);
        });
    }
};
