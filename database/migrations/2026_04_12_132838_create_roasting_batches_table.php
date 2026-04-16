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
            //relasi 
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('green_bean_id')->constrained('green_bean');
            $table->foreignId('roasted_bean_id')->constrained('roasted_bean');
            //atribut
            $table->decimal('berat_mentah', 8, 2); 
            $table->decimal('susut_uap', 5, 2); 
            $table->decimal('susut_kotoran', 5, 2); 
            $table->decimal('susut_gosong', 5, 2); 
            $table->decimal('berat_matang', 8, 2);
            
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
