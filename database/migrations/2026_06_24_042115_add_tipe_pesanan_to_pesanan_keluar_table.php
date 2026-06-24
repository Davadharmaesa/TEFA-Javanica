<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan_keluar', function (Blueprint $table) {
            $table->enum('tipe_pesanan', ['internal', 'eksternal'])->default('eksternal')->after('nama_pesanan');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan_keluar', function (Blueprint $table) {
            $table->dropColumn('tipe_pesanan');
        });
    }
};
