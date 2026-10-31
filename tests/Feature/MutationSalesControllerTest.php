<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\RoastedBean;
use App\Models\Packaging;
use App\Http\Controllers\Mutation\SalesController;

class MutationSalesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutation_store_automatically_calculates_sales_price(): void
    {
        // Route khusus pengujian, tidak mengubah route produksi
        Route::middleware('web')->post(
            '/__testing/mutation-sales/store',
            [SalesController::class, 'store']
        );

        // 1. Membuat user PIC
        $user = User::factory()->create([
            'role' => 'PIC'
        ]);

        // 2. Membuat data kopi
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Mutation Test',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 5000,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => '2026-05-25',
            'tanggal_expire' => '2026-06-25',
            'sisa_stok' => 0,
        ]);

        // 3. Membuat kemasan
        $packaging = Packaging::create([
            'tipe' => 'Pouch Stand',
            'nama_kemasan' => 'Pouch 250g Mutation',
            'kapasitas_gram' => 250,
            'harga_satuan' => 2000,
            'stok_pcs' => 100,
        ]);

        // 4. Mengirim transaksi ke Mutation Controller
        $response = $this->actingAs($user)->post(
            '/__testing/mutation-sales/store',
            [
                'customer_name' => 'Pelanggan Mutation',
                'roasted_bean_id' => $roastedBean->id,
                'finished_good_id' => $roastedBean->id . '-250',
                'jumlah_pcs' => 4,
                'jumlah_gram_pesanan' => 1000,
                'harga_jual_pesanan' => 999999,
                'status_pengiriman' => 'Menunggu Konfirmasi',
                'metode_pembayaran' => 'lunas',
            ]
        );

        // 5. Memastikan redirect berhasil
        $response->assertRedirect('/sales?tab=external');

        // 6. Memastikan transaksi benar-benar tersimpan
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Pelanggan Mutation',
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_pcs' => 4,
            'harga_jual_pesanan' => 120000,
            'total_pembayaran' => 120000,
            'status_pembayaran' => 'lunas',
        ]);

        // 7. Memastikan stok kopi berkurang
        $this->assertDatabaseHas('roasted_bean', [
            'id' => $roastedBean->id,
            'total_stok' => 4000,
        ]);
    }
}
