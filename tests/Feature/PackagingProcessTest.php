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
     * TC-PCK-001: Pencatatan pembagian kopi massal pas tanpa sisa residu
     */
    public function test_pencatatan_pembagian_kopi_massal_pas_tanpa_sisa_residu(): void
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
        $response = $this->actingAs($pic)->post('/internal-api/pengemasan/process', [
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

        // Stok kopi: 500 g; Permintaan: 600 g (melebihi stok)
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

        $this->assertDatabaseMissing('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
        ]);

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
            'nama_biji' => 'GB Arabika Sindoro',
            'asal_kopi' => 'Jawa Tengah',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Sindoro',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 100,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
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

        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 1,
        ]);
    }

    /**
     * TC-PCK-004: Penolakan instruksi saat berat kopi kurang dari kapasitas 1 kemasan
     */
    public function test_penolakan_instruksi_saat_berat_kopi_kurang_dari_kapasitas_satu_kemasan(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Bali Kintamani',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 500,
            'satuan_dasar' => 'gram',
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Pouch 250g Silver',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3500,
            'stok_pcs' => 10,
        ]);

        // Permintaan: 100 g (kurang dari kapasitas kemasan 250 g)
        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 100,
            'ukuran_kemasan_gram' => 250,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Berat kopi yang digunakan tidak mencukupi untuk membuat minimal 1 kemasan.'
            ]);
    }

    /**
     * TC-PCK-005: Penolakan proses saat stok fisik pouch kemasan kosong tidak mencukupi
     */
    public function test_penolakan_proses_saat_stok_fisik_pouch_kemasan_kosong_tidak_mencukupi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta Dampit',
            'asal_kopi' => 'Malang',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Robusta Dampit',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-PCK-005',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Dark',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10.0,
            'waktu_development' => 120,
            'durasi_pre_heating' => 5,
            'suhu' => 210,
            'berat_mentah_gudang' => 1.2,
            'berat_matang_bersih' => 1.0,
            'sisa_stok' => 1000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 100000,
        ]);

        // Stok pouch hanya ada 2 pcs (padahal 1000g / 250g butuh 4 pcs)
        $packaging = Packaging::create([
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Pouch 250g Kraft',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3000,
            'stok_pcs' => 2,
        ]);

        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 1000,
            'ukuran_kemasan_gram' => 250,
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('Stok kemasan kosong (Pouch) tidak mencukupi', $response->json('message'));
    }

    /**
     * TC-PCK-006: Pengujian batas minimum pengemasan tepat output 1 pcs
     */
    public function test_pengujian_batas_minimum_pengemasan_tepat_output_satu_pcs(): void
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

        RoastingBatch::create([
            'kode_batch' => 'BATCH-PCK-006',
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

        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 100,
            'ukuran_kemasan_gram' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hasil_kemasan_pcs', 1)
            ->assertJsonPath('data.sisa_gram_dicatat', 0);

        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 1,
        ]);
    }

    /**
     * TC-PCK-007: Sinkronisasi pemotongan stok batch roasting dan penambahan barang jadi (FIFO)
     */
    public function test_sinkronisasi_pemotongan_stok_batch_roasting_dan_penambahan_barang_jadi_fifo(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Java Preanger',
            'asal_kopi' => 'Jawa Barat',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Blend Multi-Batch',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1500,
            'satuan_dasar' => 'gram',
        ]);

        // Batch A (tanggal roasting lebih lama): 500 g
        $batchA = RoastingBatch::create([
            'kode_batch' => 'BATCH-A-OLD',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 10,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 0.6,
            'berat_matang_bersih' => 0.5,
            'sisa_stok' => 500,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => '2026-05-01',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 50000,
        ]);

        // Batch B (tanggal roasting lebih baru): 1.000 g
        $batchB = RoastingBatch::create([
            'kode_batch' => 'BATCH-B-NEW',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.5,
            'waktu_development' => 70,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 1.2,
            'berat_matang_bersih' => 1.0,
            'sisa_stok' => 1000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => '2026-05-10',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 100000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch 200g',
            'nama_kemasan' => 'Pouch 200g Eco',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2000,
            'stok_pcs' => 20,
        ]);

        // Kemas 800 g ke pouch 200 g (hasil 4 pcs)
        // Batch A (500g) harus habis (0g), Batch B (1000g) harus terpotong 300g (sisa 700g)
        $response = $this->actingAs($pic)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'jumlah_stok_digunakan_gram' => 800,
            'ukuran_kemasan_gram' => 200,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hasil_kemasan_pcs', 4);

        $batchA->refresh();
        $this->assertEquals(0, $batchA->sisa_stok);

        $batchB->refresh();
        $this->assertEquals(700, $batchB->sisa_stok);

        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 4,
        ]);
    }
}
