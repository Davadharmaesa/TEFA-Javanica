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
        Schema::create('hpp_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roasting_batch_id')->constrained('roasting_batch')->onDelete('cascade');
            $table->decimal('biaya_bahan_baku', 12, 2);
            $table->decimal('biaya_listrik', 12, 2);
            $table->decimal('biaya_penyusutan', 12, 2);
            $table->decimal('biaya_gaji', 12, 2);
            $table->decimal('biaya_kemasan', 12, 2);
            $table->integer('hasil_kemasan_pcs');
            $table->decimal('total_hpp', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpp_calculations');
    }
};
