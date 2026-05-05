<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->string('tipe'); // e.g., 'Pouch Alumunium', 'Stiker'
            $table->string('nama_kemasan'); // e.g., 'Pouch Hitam 200g'
            $table->integer('harga_satuan')->default(0);
            $table->integer('stok_pcs')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packagings');
    }
};
