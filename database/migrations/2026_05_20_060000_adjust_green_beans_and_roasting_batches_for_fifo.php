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
        // 1. Create green_bean_stock table
        Schema::create('green_bean_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_bean_id')->constrained('green_bean')->onDelete('cascade');
            $table->integer('stok_gram')->default(0);
            $table->integer('harga_beli')->default(0); // Price per Kg
            $table->date('tanggal_beli');
            $table->timestamps();
        });

        // 2. Add biaya_bahan_baku to roasting_batch table
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->integer('biaya_bahan_baku')->default(0)->after('berat_matang_bersih');
        });

        // 3. Migrate existing data from green_bean to green_bean_stock
        $beans = DB::table('green_bean')->get();
        foreach ($beans as $bean) {
            if ($bean->stok_gram > 0) {
                DB::table('green_bean_stock')->insert([
                    'green_bean_id' => $bean->id,
                    'stok_gram' => $bean->stok_gram,
                    'harga_beli' => $bean->harga_beli ?? 0,
                    'tanggal_beli' => $bean->tanggal_beli ?? now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Migrate existing roasting batch costs (retroactive calculation to prevent nulls/0s)
        $batches = DB::table('roasting_batch')->get();
        foreach ($batches as $batch) {
            $gb = DB::table('green_bean')->where('id', $batch->green_bean_id)->first();
            $hargaBeliPerKg = $gb ? ($gb->harga_beli ?? 100000) : 100000;
            $cost = ($batch->berat_mentah_gudang * 1000) * ($hargaBeliPerKg / 1000);
            DB::table('roasting_batch')->where('id', $batch->id)->update([
                'biaya_bahan_baku' => round($cost)
            ]);
        }

        // 5. Remove columns from green_bean table
        Schema::table('green_bean', function (Blueprint $table) {
            $table->dropColumn(['stok_gram', 'harga_beli', 'tanggal_beli']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add columns back to green_bean
        Schema::table('green_bean', function (Blueprint $table) {
            $table->integer('stok_gram')->default(0);
            $table->integer('harga_beli')->default(0);
            $table->date('tanggal_beli')->nullable();
        });

        // Restore green_bean stock and details from green_bean_stock
        $stocks = DB::table('green_bean_stock')->orderBy('id', 'desc')->get();
        foreach ($stocks as $stock) {
            DB::table('green_bean')->where('id', $stock->green_bean_id)->update([
                'stok_gram' => DB::raw('stok_gram + ' . $stock->stok_gram),
                'harga_beli' => $stock->harga_beli,
                'tanggal_beli' => $stock->tanggal_beli,
            ]);
        }

        // Drop green_bean_stock table
        Schema::dropIfExists('green_bean_stock');

        // Drop column from roasting_batch
        Schema::table('roasting_batch', function (Blueprint $table) {
            $table->dropColumn('biaya_bahan_baku');
        });
    }
};
