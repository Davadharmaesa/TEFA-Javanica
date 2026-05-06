<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pekerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roasting_batch_id')->constrained('roasting_batch')->onDelete('cascade');
            $table->foreignId('pekerja_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('biaya_id')->constrained('biaya')->onDelete('cascade');
            $table->decimal('jumlah_kerja', 8, 2);
            $table->integer('total_upah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pekerja');
    }
};
