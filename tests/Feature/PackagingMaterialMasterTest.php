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

class PackagingMaterialMasterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-MAT-001: Kelola master kemasan standing pouch, stok, dan harga satuan
     */
    public function test_kelola_master_kemasan_standing_pouch_stok_dan_harga_satuan(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. PIC menyimpan master kemasan standing pouch baru
        $response = $this->actingAs($pic)->postJson('/internal-api/master/packagings', [
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Kemasan 1',
            'kapasitas_gram' => 250,
            'satuan' => 'pcs',
            'stok_pcs' => 100,
            'harga_satuan' => 3500,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.tipe', 'Standing Pouch 250gr')
            ->assertJsonPath('data.nama_kemasan', 'Kemasan 1')
            ->assertJsonPath('data.stok_pcs', 100)
            ->assertJsonPath('data.harga_satuan', 3500);

        $this->assertDatabaseHas('packagings', [
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Kemasan 1',
            'stok_pcs' => 100,
            'harga_satuan' => 3500,
        ]);

        $packaging = Packaging::where('nama_kemasan', 'Kemasan 1')->first();

        // 2. Siapkan data produksi kopi matang untuk instruksi pengemasan
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Java Preanger',
            'asal_kopi' => 'Jawa Barat',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Java Preanger',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-MAT-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 9.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 1.2,
            'berat_mentah_sortasi' => 1.1,
            'berat_matang_kotor' => 1.05,
            'berat_matang_bersih' => 1.0,
            'sisa_stok' => 1000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 100000,
        ]);

        // 3. Buat instruksi pengemasan 1000g ke dalam 4 kemasan 250g
        $pckResponse = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 1000,
            'ukuran_kemasan_gram' => 250,
        ]);

        $pckResponse->assertStatus(200);

        // Verifikasi stok material kemasan terpotong dari 100 menjadi 96 pcs
        $packaging->refresh();
        $this->assertEquals(96, $packaging->stok_pcs);
    }

    /**
     * TC-MAT-002: Penambahan master material kemasan dengan field wajib kosong
     */
    public function test_penambahan_master_material_kemasan_dengan_field_wajib_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // Data tidak valid: field nama_kemasan kosong
        $response = $this->actingAs($pic)->postJson('/internal-api/master/packagings', [
            'tipe' => 'Pouch',
            'nama_kemasan' => '',
            'kapasitas_gram' => 100,
            'satuan' => 'pcs',
            'stok_pcs' => 1000,
            'harga_satuan' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('nama_kemasan');

        $this->assertDatabaseMissing('packagings', [
            'tipe' => 'Pouch',
            'kapasitas_gram' => 100,
            'stok_pcs' => 1000,
        ]);
    }

    /**
     * TC-MAT-003: Penambahan master material kemasan dengan stok fisik pada jumlah minimum
     */
    public function test_penambahan_master_material_kemasan_dengan_stok_fisik_pada_jumlah_minimum(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // PIC menyimpan material kemasan dengan stok minimum (1 pcs)
        $response = $this->actingAs($pic)->postJson('/internal-api/master/packagings', [
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Kemasan 2',
            'kapasitas_gram' => 10,
            'satuan' => 'pcs',
            'stok_pcs' => 1,
            'harga_satuan' => 500,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_kemasan', 'Kemasan 2')
            ->assertJsonPath('data.stok_pcs', 1);

        $this->assertDatabaseHas('packagings', [
            'nama_kemasan' => 'Kemasan 2',
            'kapasitas_gram' => 10,
            'stok_pcs' => 1,
            'harga_satuan' => 500,
        ]);

        // Pastikan data tampil pada daftar material
        $listResponse = $this->actingAs($pic)->getJson('/internal-api/master/packagings');
        $listResponse->assertStatus(200)
            ->assertJsonFragment([
                'nama_kemasan' => 'Kemasan 2',
                'stok_pcs' => 1,
            ]);
    }

    /**
     * TC-MAT-004: Pembaruan (Update) harga modal master material kemasan
     */
    public function test_pembaruan_update_harga_modal_master_material_kemasan(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. Setup awal: Kemasan dengan harga lama Rp 3.000
        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Master Pouch 250g',
            'kapasitas_gram' => 250,
            'satuan' => 'pcs',
            'stok_pcs' => 50,
            'harga_satuan' => 3000,
        ]);

        // 2. PIC memperbarui harga menjadi Rp 3.500
        $updateResponse = $this->actingAs($pic)->putJson("/internal-api/master/packagings/{$packaging->id}", [
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Master Pouch 250g',
            'kapasitas_gram' => 250,
            'satuan' => 'pcs',
            'stok_pcs' => 50,
            'harga_satuan' => 3500,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.harga_satuan', 3500);

        $this->assertDatabaseHas('packagings', [
            'id' => $packaging->id,
            'harga_satuan' => 3500,
        ]);

        // 3. Verifikasi integrasi ke kalkulasi HPP otomatis menggunakan tarif baru (Rp 3.500)
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Sindoro',
            'asal_kopi' => 'Jawa Tengah',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Sindoro',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 5000,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-MAT-004',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10.0,
            'waktu_development' => 120,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 20.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);

        Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        $previewResponse = $this->actingAs($pic)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'pouch_id' => $packaging->id,
            'hasil_kemasan_pcs' => 10,
        ]);

        $previewResponse->assertStatus(200);

        // Biaya kemasan untuk 10 pcs @ Rp 3.500 = Rp 35.000
        $this->assertEquals(35000, $previewResponse->json('biaya_kemasan'));
    }
}
