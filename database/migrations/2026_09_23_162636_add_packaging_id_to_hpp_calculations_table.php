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
        Schema::table('hpp_calculations', function (Blueprint $table) {
            $table->unsignedBigInteger('packaging_id')->nullable()->after('roasting_batch_id');
            
            // Optionally, add a foreign key constraint
            // $table->foreign('packaging_id')->references('id')->on('packagings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hpp_calculations', function (Blueprint $table) {
            $table->dropColumn('packaging_id');
        });
    }
};
