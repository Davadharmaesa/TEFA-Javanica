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
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->decimal('waktu_first_crack', 5, 2)->nullable()->after('durasi_roasting');
            $table->integer('waktu_development')->nullable()->after('waktu_first_crack');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->dropColumn(['waktu_first_crack', 'waktu_development']);
        });
    }
};
