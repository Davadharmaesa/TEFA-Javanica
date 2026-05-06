<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_pekerja', function (Blueprint $table) {
            $table->dropForeign(['pekerja_id']);
            $table->dropColumn('pekerja_id');
            $table->string('nama_pekerja')->after('roasting_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('log_pekerja', function (Blueprint $table) {
            $table->dropColumn('nama_pekerja');
            $table->foreignId('pekerja_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }
};
