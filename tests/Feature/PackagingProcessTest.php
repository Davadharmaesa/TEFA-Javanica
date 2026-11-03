<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\Packaging;
use App\Models\FinishedGood;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagingProcessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-PCK-001: Pencatatan pembagian volume kopi massal ke beberapa varian kemasan
     */
    public function test_pencatatan_pembagian_volume_kopi_massal_ke_beberapa_varian_kemasan(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Ijen',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-PCK-001',
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
            'biaya_bahan_baku' => 120000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Pouch 250g Silver',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3500,
            'stok_pcs' => 10,
        ]);

        // Volume batch: 1.000 g; Kemasan: 250 g; Jumlah: 4 pcs
        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 1000,
            'ukuran_kemasan_gram' => 250,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hasil_kemasan_pcs', 4)
            ->assertJsonPath('data.sisa_gram_dicatat', 0);

        // Verifikasi FinishedGood bertambah 4 pcs
        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 4,
        ]);

        // Verifikasi sisa stok batch dan roasted bean menjadi 0
        $batch->refresh();
        $this->assertEquals(0, $batch->sisa_stok);

        $roastedBean->refresh();
        $this->assertEquals(0, $roastedBean->total_stok);
    }

    /**
     * TC-PCK-002: Pencatatan instruksi pengemasan dengan jumlah kopi melebihi stok yang tersedia
     */
    public function test_pencatatan_instruksi_pengemasan_dengan_jumlah_kopi_melebihi_stok_yang_tersedia(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta Dampit',
            'asal_kopi' => 'Malang',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Robusta Dampit',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 500,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-PCK-002',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Dark',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10.0,
            'waktu_development' => 120,
            'durasi_pre_heating' => 5,
            'suhu' => 210,
            'berat_mentah_gudang' => 0.6,
            'berat_mentah_sortasi' => 0.58,
            'berat_matang_kotor' => 0.52,
            'berat_matang_bersih' => 0.5,
            'sisa_stok' => 500,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 50000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Pouch 250g Kraft',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3000,
            'stok_pcs' => 10,
        ]);

        // Stok kopi: 500 g; Jumlah kopi yang akan dikemas: 600 g (melebihi stok)
        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 600,
            'ukuran_kemasan_gram' => 250,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Stok Roasted Bean tidak mencukupi.'
            ]);

        // Verifikasi tidak ada barang jadi yang tersimpan
        $this->assertDatabaseMissing('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
        ]);

        // Verifikasi stok batch tidak berkurang
        $batch->refresh();
        $this->assertEquals(500, $batch->sisa_stok);
    }

    /**
     * TC-PCK-003: Pengujian pengemasan dengan jumlah output minimum 1 pcs
     */
    public function test_pengujian_pengemasan_dengan_jumlah_output_minimum_1_pcs(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Flores',
            'asal_kopi' => 'Bajawa',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Flores',
            'bentuk_akhir' => 'Bubuk',
            'total_stok' => 100,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-PCK-003',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 10,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 0.15,
            'berat_mentah_sortasi' => 0.14,
            'berat_matang_kotor' => 0.11,
            'berat_matang_bersih' => 0.1,
            'sisa_stok' => 100,
            'persentase_susut' => 15.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 15000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch 100g',
            'nama_kemasan' => 'Sample Pouch 100g',
            'kapasitas_gram' => 100,
            'harga_satuan' => 1500,
            'stok_pcs' => 5,
        ]);

        // Volume batch: 100 g; Kemasan: 100 g; Output: 1 pcs
        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 100,
            'ukuran_kemasan_gram' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hasil_kemasan_pcs', 1)
            ->assertJsonPath('data.sisa_gram_dicatat', 0);

        // Verifikasi output 1 pcs tersimpan di finished goods
        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 1,
        ]);
    }
}
