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
        Schema::table('green_bean_stock', function (Blueprint $table) {
            $table->decimal('total_harga', 15, 2)->default(0)->after('harga_beli');
        });
    }

    public function down(): void
    {
        Schema::table('green_bean_stock', function (Blueprint $table) {
            $table->dropColumn('total_harga');
        });
    }
};
