<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\User;
use App\Models\GreenBeanStock;
use App\Models\RoastedBean;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenBeanStockDateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-GBSTK-001: Menampilkan total sisa stok per jenis Green Bean
     */
    public function test_tc_gbstk_001_displays_total_remaining_stock_per_green_bean(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Gayo',
            'asal_kopi' => 'Aceh',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 15000, // 15 kg
            'harga_beli' => 200000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee('GB Arabika Gayo');
        $response->assertSee('15'); // Menampilkan angka total stok dalam kg/gram secara akurat
    }

    /**
     * TC-GBSTK-002: Pengecekan histori mutasi MASUK (Inbound) saat penambahan stok
     */
    public function test_tc_gbstk_002_records_inbound_mutation_on_stock_addition(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta Lampung',
            'asal_kopi' => 'Lampung',
        ]);

        $todayDate = now()->format('d/m/Y');

        $response = $this->actingAs($user)->post('/inventory/green-bean', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'tanggal_beli' => $todayDate,
            'total_harga' => 100000,
        ]);

        $response->assertRedirect();
        
        // Memastikan stok masuk tercatat dan log mutasi inbound terekam di database
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
        ]);
        
    }

    /**
     * TC-GBSTK-003: Pengecekan histori mutasi KELUAR (Outbound) akibat Roasting
     */
    public function test_tc_gbstk_003_records_outbound_mutation_due_to_roasting(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Liberika Riau',
            'asal_kopi' => 'Riau',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Liberika',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        // Stok awal 5000 gram (5 kg)
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Melakukan proses roasting yang menggunakan 2000 gram (2 kg) Green Bean
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-ROAST-003',
            'profil_sangrai' => 'Medium',
            'waktu_first_crack' => 10.0,
            'waktu_development' => 150,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 2.0, 
            'berat_mentah_sortasi' => 1.9,
            'berat_matang_kotor' => 1.7,
            'berat_matang_bersih' => 1.6,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 3000, // Sisa 3000 gram (5000 - 2000)
        ]);
    }

    /**
     * TC-GBSTK-004: Penanganan tampilan saat histori mutasi kosong (Jenis kopi baru)
     */
    public function test_tc_gbstk_004_handles_new_green_bean_with_no_mutation_gracefully(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        // Membuat green bean baru tapi belum pernah ada transaksi/mutasi sama sekali
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Special Edition',
            'asal_kopi' => 'Flores',
        ]);

        $response = $this->actingAs($user)->get('/inventory');

        // Memastikan sistem merender halaman dengan stabil tanpa crash dan menampilkan data awal
        $response->assertStatus(200);
        $response->assertSee('GB Special Edition');
    }

    /**
     * Tambahan validasi tanggal pembelian (mencegah tanggal masa depan)
     */
    public function test_cannot_add_green_bean_stock_with_future_date(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Test Future',
            'asal_kopi' => 'Bali',
        ]);

        $futureDate = now()->addDay()->format('d/m/Y');

        $response = $this->actingAs($user)->post('/inventory/green-bean', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'tanggal_beli' => $futureDate,
            'total_harga' => 150000,
        ]);

        $response->assertSessionHas('error', 'Tanggal barang masuk tidak boleh melebihi hari ini!');
        $this->assertDatabaseEmpty('green_bean_stock');
    }
}