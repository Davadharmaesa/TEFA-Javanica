<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roasted_bean_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roasted_bean_id')->constrained('roasted_bean')->onDelete('cascade');
            $table->integer('stok_gram')->default(0);
            $table->integer('harga_roasting')->default(0); // HPP per Kg
            $table->date('tanggal_roasting');
            $table->timestamps();
        });

        // Migrate existing data from roasted_bean to roasted_bean_stock
        $beans = DB::table('roasted_bean')->get();
        foreach ($beans as $bean) {
            if ($bean->total_stok > 0) {
                DB::table('roasted_bean_stock')->insert([
                    'roasted_bean_id' => $bean->id,
                    'stok_gram' => $bean->total_stok,
                    'harga_roasting' => 100000, // Default HPP per Kg (Rp 100k)
                    'tanggal_roasting' => $bean->tanggal_roasting ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roasted_bean_stock');
    }
};

