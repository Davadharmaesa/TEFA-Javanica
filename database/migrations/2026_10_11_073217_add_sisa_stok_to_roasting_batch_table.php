<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->integer('sisa_stok')->default(0)->after('berat_matang_bersih');
        });
    }

    public function down()
    {
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->dropColumn('sisa_stok');
        });
    }
};
