<?php

namespace Tests\Feature;

use App\Models\Biaya;
use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\Packaging;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use App\Models\PesananKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HppAndSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hpp_calculation_preview_allocates_proportional_costs(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'GB Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 8200,
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00,
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 8.20, // 8.2 Kg = 8200 Gram
            'persentase_susut' => 16.33,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1200000 // Rp 1.200.000
        ]);

        Biaya::create(['nama' => 'Listrik Mesin', 'kategori' => 'Overhead', 'satuan' => 'Kwh', 'harga' => 1500]);
        Biaya::create(['nama' => 'Penyusutan Mesin', 'kategori' => 'Overhead', 'satuan' => 'Batch', 'harga' => 10000]);
        $biayaRoaster = Biaya::create(['nama' => 'Upah Roaster', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Batch', 'harga' => 20500]);

        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Hitam 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 1500,
            'stok_pcs' => 100,
        ]);

        \App\Models\LogPekerja::create([
            'roasting_batch_id' => $batch->id,
            'nama_pekerja' => 'Pak Deka',
            'biaya_id' => $biayaRoaster->id,
            'jumlah_kerja' => 1,
            'total_upah' => 20500,
        ]);

        $response = $this->actingAs($user)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 25,
            'pouch_id' => $pouch->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('biaya_gaji', 12500)
            ->assertJsonPath('biaya_kemasan', 37500);

        $data = $response->json();
        $this->assertEquals(731707, round($data['biaya_bahan_baku']));
        $this->assertEquals(1229, round($data['biaya_listrik']));
    }

    public function test_hpp_can_be_calculated_and_stored_when_packaging_stock_is_zero(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Robusta Dampit', 'asal_kopi' => 'Malang']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'GB Robusta Dampit', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 2000]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-ZERO-STOCK',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10.00,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 2.50,
            'berat_mentah_sortasi' => 2.40,
            'berat_matang_kotor' => 2.10,
            'berat_matang_bersih' => 2.00, // 2000g
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 300000,
        ]);

        Biaya::create(['nama' => 'Listrik Mesin', 'kategori' => 'Overhead', 'satuan' => 'Kwh', 'harga' => 1500]);
        Biaya::create(['nama' => 'Penyusutan Mesin', 'kategori' => 'Overhead', 'satuan' => 'Batch', 'harga' => 10000]);
        Biaya::create(['nama' => 'Upah Roaster', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Batch', 'harga' => 15000]);

        // Packaging with 0 stock (e.g. exhausted in previous packaging / sales)
        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Standar 100g',
            'kapasitas_gram' => 100,
            'harga_satuan' => 1000,
            'stok_pcs' => 0, // STOK KOSONG
        ]);

        // 1. Preview kalkulasi tetap bisa menghasilkan output pcs dan total HPP
        $previewRes = $this->actingAs($user)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 20, // 2000g / 100g = 20 pcs
            'pouch_id' => $pouch->id,
        ]);

        $previewRes->assertStatus(200);
        $previewData = $previewRes->json();
        $this->assertEquals(20, $previewData['max_output_pcs']);
        $this->assertGreaterThan(0, $previewData['total_hpp']);

        // 2. Simpan kalkulasi HPP ke database
        $storeRes = $this->actingAs($user)->post('/hpp-calculator/store', [
            'roasting_batch_id' => $batch->id,
            'packaging_id' => $pouch->id,
            'hasil_kemasan_pcs' => 20,
            'biaya_bahan_baku' => $previewData['biaya_bahan_baku'],
            'biaya_listrik' => $previewData['biaya_listrik'],
            'biaya_penyusutan' => $previewData['biaya_penyusutan'],
            'biaya_gaji' => $previewData['biaya_gaji'],
            'biaya_kemasan' => $previewData['biaya_kemasan'],
            'total_hpp' => $previewData['total_hpp'],
            'harga_jual_final' => 25000,
        ]);

        $storeRes->assertRedirect(route('hpp.index'));
        $this->assertDatabaseHas('hpp_calculations', [
            'roasting_batch_id' => $batch->id,
            'packaging_id' => $pouch->id,
            'hasil_kemasan_pcs' => 20,
            'harga_jual_final' => 25000,
        ]);
    }

    public function test_sales_transaction_deducts_stock_using_fifo(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Belawan', 'asal_kopi' => 'Bali']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'GB Arabika Belawan', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 8200]);

        // Create two completed batches with different dates
        $batch1 = RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00,
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 3.20, // 3.2 Kg = 3200 Gram remaining
            'persentase_susut' => 16.33,
            'tanggal_roasting' => '2026-05-10',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1200000
        ]);

        $batch2 = RoastingBatch::create([
            'kode_batch' => 'BATCH-002',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00,
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 5.00, // 5.0 Kg = 5000 Gram
            'persentase_susut' => 16.33,
            'tanggal_roasting' => '2026-05-15',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1200000
        ]);

        // Sell 4000 grams of coffee. Should take 3200g from batch1 and 800g from batch2.
        \Laravel\Sanctum\Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/pesanan-keluar', [
            'roasted_bean_id' => $roastedBean->id,
            'nama_pesanan' => 'Pesanan Test',
            'jumlah_gram_pesanan' => 4000,
            'harga_jual_pesanan' => 120000,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('roasted_bean', [
            'id' => $roastedBean->id,
            'total_stok' => 4200, // 8200 - 4000
        ]);

        // batch1 should be 0 Kg
        $this->assertEquals(0, RoastingBatch::find($batch1->id)->berat_matang_bersih);

        // batch2 should be 4.2 Kg (5.0 - 0.8)
        $this->assertEquals(4.2, RoastingBatch::find($batch2->id)->berat_matang_bersih);
    }
}
