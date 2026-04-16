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
        Schema::create('roasted_bean', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kopi');
            $table->string('level_roast');
            $table->integer('total_stok')->default(0);
            $table->string('satuan_dasar')->default('gram');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roasted_bean');
    }
};
