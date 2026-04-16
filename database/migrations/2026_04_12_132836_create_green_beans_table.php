<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('green_bean', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biji');
            $table->integer('stok_gram')->default(0);
            $table->string('asal_kopi');
            $table->integer('harga_jual')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('green_bean');
    }
};
