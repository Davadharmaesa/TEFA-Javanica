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
            $table->renameColumn('berat_sebelum_sort', 'berat_mentah_gudang');
        });

        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->decimal('berat_mentah_sortasi', 8, 2)->after('berat_mentah_gudang')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->dropColumn('berat_mentah_sortasi');
        });

        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->renameColumn('berat_mentah_gudang', 'berat_sebelum_sort');
        });
    }
};
