<?php

namespace Tests\Feature;

use App\Models\Biaya;
use App\Models\GreenBean;
use App\Models\Packaging;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostComponentMasterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-COST-001: Memperbarui tarif dasar biaya yang sudah ada
     */
    public function test_memperbarui_tarif_dasar_biaya_yang_sudah_ada(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $biaya = Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        // PIC mengedit harga menjadi Rp 2.000
        $response = $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biaya->id}", [
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 2000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.harga', 2000);

        $this->assertDatabaseHas('biaya', [
            'id' => $biaya->id,
            'harga' => 2000,
        ]);
    }

    /**
     * TC-COST-002: Integrasi pembaruan tarif Master Biaya terhadap Modul HPP
     */
    public function test_integrasi_pembaruan_tarif_master_biaya_terhadap_modul_hpp(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. Setup awal: Tarif Listrik Rp 1.500
        $biayaListrik = Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Bali', 'asal_kopi' => 'Kintamani']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Arabika Bali', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 4000]);

        // Batch dengan durasi total kerja mesin = 5m (preheat) + 12m (first crack) + 3m (dev 180s) = 20 menit (1/3 jam)
        // Konsumsi listrik: (20 / 60) * 0.75 kW = 0.25 kWh
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-COST-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0, // 4000g
            'sisa_stok' => 4000,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);

        $pouch = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 100g',
            'kapasitas_gram' => 100,
            'satuan' => 'pcs',
            'harga_satuan' => 1000,
            'stok_pcs' => 100,
        ]);

        // Kalkulasi awal dengan tarif lama Rp 1.500:
        // (0.25 * 1500) + 1000 = 375 + 1000 = 1375
        $previewAwal = $this->actingAs($pic)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 40,
            'pouch_id' => $pouch->id,
        ]);
        $this->assertEquals(1375, $previewAwal->json('biaya_listrik'));

        // 2. Lakukan TC-COST-001: Naikkan tarif listrik menjadi Rp 2.000
        $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biayaListrik->id}", [
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 2000,
        ])->assertStatus(200);

        // 3. Verifikasi pada kalkulasi HPP baru: otomatis menggunakan tarif baru Rp 2.000
        // (0.25 * 2000) + 1000 = 500 + 1000 = 1500
        $previewBaru = $this->actingAs($pic)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 40,
            'pouch_id' => $pouch->id,
        ]);

        $this->assertEquals(1500, $previewBaru->json('biaya_listrik'));
        $this->assertNotEquals(1375, $previewBaru->json('biaya_listrik'));
    }

    /**
     * TC-COST-003: Tambah dan kelola komponen biaya operasional baru
     */
    public function test_tambah_komponen_biaya_operasional_baru(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. Tambah biaya baru
        $response = $this->actingAs($pic)->postJson('/internal-api/master/biaya', [
            'nama' => 'Gas Roasting',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama', 'Gas Roasting')
            ->assertJsonPath('data.harga', 25000);

        $this->assertDatabaseHas('biaya', [
            'nama' => 'Gas Roasting',
            'harga' => 25000,
        ]);

        // 2. Tampil di daftar master komponen biaya
        $listRes = $this->actingAs($pic)->getJson('/internal-api/master/biaya');
        $listRes->assertStatus(200)
            ->assertJsonFragment([
                'nama' => 'Gas Roasting',
                'harga' => 25000,
            ]);
    }
}
