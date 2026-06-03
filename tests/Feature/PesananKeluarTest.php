<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RoastedBean;
use App\Models\RoastedBeanStock;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananKeluarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that creating acompleted roasting batch automatically creates a roasted_bean_stock record.
     */
    public function test_creating_completed_roasting_batch_triggers_stock_creation(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Gayo',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => now()->toDateString(),
            'tanggal_expire' => now()->addDays(30)->toDateString(),
            'sisa_stok' => 0,
        ]);

        // Simulating green bean
        $greenBean = \App\Models\GreenBean::create([
            'nama_biji' => 'Green Arabica Gayo',
            'asal_kopi' => 'Aceh',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15.5,
            'waktu_first_crack' => 12.0,
            'waktu_development' => 210,
            'durasi_pre_heating' => 5.0,
            'berat_mentah_gudang' => 3.0,
            'berat_mentah_sortasi' => 2.9,
            'berat_matang_kotor' => 2.6,
            'berat_matang_bersih' => 2.5,
            'biaya_bahan_baku' => 250000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => '2026-05-25',
            'status_batch' => 'completed',
        ]);

        // Verify that a RoastedBeanStock entry was automatically created
        $this->assertDatabaseHas('roasted_bean_stock', [
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 2500, // 2.5 * 1000
            'harga_roasting' => 100000, // 250000 / 2.5
            'tanggal_roasting' => '2026-05-25',
        ]);
    }

    /**
     * Test that cashier dynamics sale reduces stock using FIFO method.
     */
    public function test_sale_reduces_stock_using_fifo(): void
    {
        $user = User::factory()->create(['role' => 'Kasir']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Robusta Temanggung',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4500,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => '2026-05-25',
            'tanggal_expire' => '2026-06-25',
            'sisa_stok' => 0,
        ]);

        // Setup stock entries (FIFO order: oldest to newest)
        // Entry A: oldest
        $stockA = RoastedBeanStock::create([
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 1000,
            'harga_roasting' => 50000,
            'tanggal_roasting' => '2026-05-20',
        ]);

        // Entry B: middle
        $stockB = RoastedBeanStock::create([
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 2000,
            'harga_roasting' => 60000,
            'tanggal_roasting' => '2026-05-22',
        ]);

        // Entry C: newest
        $stockC = RoastedBeanStock::create([
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 1500,
            'harga_roasting' => 55000,
            'tanggal_roasting' => '2026-05-25',
        ]);

        // Make sale of 1500 grams (1000 from StockA and 500 from StockB)
        $response = $this->actingAs($user)->postJson('/api/v1/pesanan-keluar', [
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_gram_pesanan' => 1500,
            'harga_jual_pesanan' => 100, // Rp 100 per gram * 1500 grams = Rp 150.000 total price
            'nama_pesanan' => 'Meja 5',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_pembayaran', 150000)
            ->assertJsonPath('data.status_pembayaran', 'lunas');

        // Check stock reductions
        $this->assertEquals(0, $stockA->refresh()->stok_gram, 'Oldest stock should be fully depleted');
        $this->assertEquals(1500, $stockB->refresh()->stok_gram, 'Middle stock should be partially depleted (2000 - 500)');
        $this->assertEquals(1500, $stockC->refresh()->stok_gram, 'Newest stock should remain untouched');

        // Check cache in roasted_bean
        $this->assertEquals(3000, $roastedBean->refresh()->total_stok, 'RoastedBean total_stok cache should be decremented');

        // Check order recorded in database
        $this->assertDatabaseHas('pesanan_keluar', [
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_gram_pesanan' => 1500,
            'harga_jual_pesanan' => 100,
            'total_pembayaran' => 150000,
            'status_pembayaran' => 'lunas',
        ]);
    }

    /**
     * Test that a sale request is rejected when there is not enough stock.
     */
    public function test_sale_rejected_if_stock_insufficient(): void
    {
        $user = User::factory()->create(['role' => 'Kasir']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Robusta Temanggung',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 500,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => '2026-05-25',
            'tanggal_expire' => '2026-06-25',
            'sisa_stok' => 0,
        ]);

        RoastedBeanStock::create([
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 500,
            'harga_roasting' => 50000,
            'tanggal_roasting' => '2026-05-20',
        ]);

        // Attempting to buy 600 grams when only 500 available
        $response = $this->actingAs($user)->postJson('/api/v1/pesanan-keluar', [
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_gram_pesanan' => 600,
            'harga_jual_pesanan' => 100,
            'nama_pesanan' => 'Meja 5', // Added required field
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Stok Roasted Bean di gudang tidak mencukupi (Tersedia: 0.5 kg).');
    }

    /**
     * Test that a pre-order sale allows negative stock (backorder).
     */
    public function test_pre_order_sales_allows_negative_stock(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Gayo',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 500,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => '2026-05-25',
            'tanggal_expire' => '2026-06-25',
            'sisa_stok' => 0,
        ]);

        $stock = RoastedBeanStock::create([
            'roasted_bean_id' => $roastedBean->id,
            'stok_gram' => 500,
            'harga_roasting' => 50000,
            'tanggal_roasting' => '2026-05-20',
        ]);

        // Pre-order 1200 grams when only 500 is available
        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Budi Pre-Order',
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_gram_pesanan' => 1200,
            'harga_jual_pesanan' => 200000,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'metode_pembayaran' => 'lunas',
        ]);

        $response->assertRedirect('/sales');

        // Check that the stock is negative in roasted_bean
        $this->assertEquals(-700, $roastedBean->refresh()->total_stok);

        // Check that RoastedBeanStock is depleted to 0
        $this->assertEquals(0, $stock->refresh()->stok_gram);

        // Check pesanan_keluar record
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Budi Pre-Order',
            'jumlah_gram_pesanan' => 1200,
            'status_pembayaran' => 'lunas',
        ]);
    }

    /**
     * Test that pre-order sale auto-detects DP status correctly.
     */
    public function test_pre_order_sales_auto_detects_payment_status(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Gayo',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
            'tanggal_roasting' => '2026-05-25',
            'tanggal_expire' => '2026-06-25',
            'sisa_stok' => 0,
        ]);

        // Pre-order with DP
        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Customer DP',
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_gram_pesanan' => 500,
            'harga_jual_pesanan' => 100000,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'metode_pembayaran' => 'dp',
            'nominal_dp' => 40000,
            'tanggal_bayar_dp' => '2026-06-03',
            'batas_waktu_pembayaran' => '2026-06-10',
        ]);

        $response->assertRedirect('/sales');

        // Check pesanan_keluar record has status_pembayaran = 'dp' and sisa_pembayaran = 60000
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Customer DP',
            'status_pembayaran' => 'dp',
            'nominal_dp' => 40000,
            'sisa_pembayaran' => 60000,
        ]);
    }
}
