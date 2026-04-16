<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void {
        Schema::create('pesanan_keluar', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('roasted_bean_id')->constrained('roasted_bean');

            $table->string('nama_pesanan');
            $table->integer('jumlah_gram_pesanan');
            $table->integer('harga_jual_pesanan');
            $table->date('tanggal_pesanan');
            $table->bigInteger('total_pembayaran');
            $table->string('status_pembayaran')->default('pending');
            $table->string('status_pengiriman')->default('diproses');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_keluar');
    }
};
