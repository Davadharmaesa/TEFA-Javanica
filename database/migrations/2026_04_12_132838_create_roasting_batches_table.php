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
        Schema::create('roasting_batch', function (Blueprint $table) {
            $table->id();
            $table->string('kode_batch')->unique();
            
            // Relasi 
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('green_bean_id')->constrained('green_bean');
            $table->foreignId('roasted_bean_id')->constrained('roasted_bean');
            
            // Atribut Produksi (Sesuai UI Baru)
            $table->string('profil_sangrai');
            $table->string('bentuk_akhir');
            $table->decimal('berat_mentah', 8, 2); // Input dalam kilogram
            $table->decimal('berat_matang', 8, 2); // Input dalam kilogram
            $table->decimal('persentase_susut', 5, 2); // Burnoff %
            
            $table->date('tanggal_roasting');
            $table->string('status_batch')->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roasting_batch');
    }
};
