<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\GreenBeanStock;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoastingApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Jalankan artisan migrate agar tabel-tabel (users, green_bean, dll) tercipta di SQLite bayangan
        $this->artisan('migrate');
    }

    public function test_store_batch_requires_suhu_parameter(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Send request without suhu
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-001',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('suhu');
    }

    public function test_store_batch_saves_suhu_successfully(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Send request with suhu
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-001',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'BATCH-001',
            'suhu' => 205,
        ]);
    }
    
    public function test_store_batch_success_and_calculates_shrinkage_accurately(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000, // 10 kg
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'TC-001-BATCH',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(201);

        // Validasi persentase susut terhitung otomatis (Rumus: (4.8 - 4.0) / 4.8 * 100 = 16.67% -> dibulatkan jadi 16.67 atau 16.7)
        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'TC-001-BATCH',
            'persentase_susut' => 16.67,
        ]);
    }

    /**
     * TC-ROAST-002: Penolakan input jika stok Green Bean tidak cukup (BVA)
     */
    public function test_store_batch_rejects_when_green_bean_stock_is_insufficient(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta',
            'asal_kopi' => 'Lampung',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Robusta',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        // Stok gudang dibatasi hanya 2000 gram (2 kg)
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 2000, 
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Input 3kg (melebihi stok 2kg)
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'TC-002-FAIL',
            'profil_sangrai' => 'Dark Roast',
            'waktu_first_crack' => 10.00,
            'waktu_development' => 150,
            'durasi_pre_heating' => 4,
            'suhu' => 210,
            'berat_mentah_gudang' => 3.0, 
            'berat_mentah_sortasi' => 2.8,
            'berat_matang_kotor' => 2.4,
            'berat_matang_bersih' => 2.2,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Stok Green Bean di gudang tidak mencukupi.'
            ]);
    }

    /**
     * TC-ROAST-003: Penolakan input angka negatif atau nol (0) (Equivalence Partitioning)
     */
    public function test_store_batch_rejects_negative_or_zero_values(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Liberika', 'asal_kopi' => 'Riau']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Roasted Liberika', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 100000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Sengaja mengirim nilai negatif / nol pada parameter krusial
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'TC-003-NEG',
            'profil_sangrai' => 'Light Roast',
            'waktu_first_crack' => -1.0, // Negatif (Gagal validasi min:0.1)
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['waktu_first_crack']);
    }

    /**
     * TC-ROAST-004: Integrasi penambahan Stok Matang (Roasted Bean) & Expired H+30
     */
    public function test_store_batch_integrates_roasted_bean_stock_and_expiration(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Gayo', 'asal_kopi' => 'Aceh']);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Gayo',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000, // Stok awal 1000 gram (1 kg)
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 150000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'TC-004-INT',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 11.5,
            'waktu_development' => 160,
            'durasi_pre_heating' => 5,
            'suhu' => 202,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0, // Menambah 4 kg (4000 gram)
        ]);

        $response->assertStatus(201);

        // Validasi stok roasted bean bertambah (1000 + 4000 = 5000 gram) dan tanggal expired ter-set H+30
        $this->assertDatabaseHas('roasted_bean', [
            'id' => $roastedBean->id,
            'total_stok' => 5000,
            'tanggal_roasting' => now()->toDateString(),
            'tanggal_expire' => now()->addDays(30)->toDateString(),
        ]);
    }
}
