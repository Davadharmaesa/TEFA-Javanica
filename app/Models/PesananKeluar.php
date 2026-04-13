<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PesananKeluar extends Model
{
     public function up() : void {
        Schema::create('pesanan_keluars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->contrained('users');
            $table->foreignId('roasted_bean_id')->contrained('roasted_beans');

            $table->string('nama_pesanan');
            $table->integer('jumlah_gram_pesanan');
            $table->integer('harga_jual_pesanan');
            $table->date('tanggal_pesanan');
            $table->integer('total_pembayaran');
            $table->string('status_pembayaran');
            $table->string('status_pengiriman');
            
            $table->timestamps();
        });
    }
}
