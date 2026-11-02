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

    public function test_packaging_process_deducts_roasted_bean_and_batch_stock_correctly(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'GB Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4000,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'RST-TEST-001',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Light Roast',
            'durasi_roasting' => 10,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.5,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 15.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);

        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Hitam 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2500,
            'stok_pcs' => 30,
        ]);

        // Simulasikan pengemasan 4000g kopi matang menjadi 20 pcs kemasan 200g
        $response = $this->actingAs($user)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $pouch->id,
            'jumlah_stok_digunakan_gram' => 4000,
            'ukuran_kemasan_gram' => 200,
        ]);

        $response->assertStatus(200);

        // 1. RoastingBatch sisa_stok berkurang dari 4000g menjadi 0
        $batch->refresh();
        $this->assertEquals(0, $batch->sisa_stok);
        $this->assertEquals(0, (float) $batch->berat_matang_bersih);

        // 2. Master RoastedBean total_stok berkurang menjadi 0
        $roastedBean->refresh();
        $this->assertEquals(0, $roastedBean->total_stok);

        // 3. Stok Pouch berkurang dari 30 menjadi 10 (terpakai 20 pcs)
        $pouch->refresh();
        $this->assertEquals(10, $pouch->stok_pcs);

        // 4. FinishedGood tercipta dengan stok 20 pcs
        $this->assertDatabaseHas('finished_goods', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $pouch->id,
            'stok_pcs' => 20,
        ]);
    }

    public function test_packaging_process_leaves_residue_when_amount_not_exact(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta',
            'asal_kopi' => 'Dampit',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'GB Robusta',
            'bentuk_akhir' => 'Bubuk',
            'total_stok' => 2150,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'RST-TEST-002',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 9.0,
            'waktu_development' => 70,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 2.5,
            'berat_mentah_sortasi' => 2.4,
            'berat_matang_kotor' => 2.2,
            'berat_matang_bersih' => 2.15,
            'sisa_stok' => 2150,
            'persentase_susut' => 15.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 200000,
        ]);

        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2000,
            'stok_pcs' => 50,
        ]);

        // Kemas 2150g -> 10 pcs (2000g), sisa residu 150g
        $response = $this->actingAs($user)->postJson('/internal-api/pengemasan/process', [
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $pouch->id,
            'jumlah_stok_digunakan_gram' => 2150,
            'ukuran_kemasan_gram' => 200,
        ]);

        $response->assertStatus(200);

        $batch->refresh();
        $this->assertEquals(150, $batch->sisa_stok);
        $this->assertEquals(0.15, (float) $batch->berat_matang_bersih);

        $roastedBean->refresh();
        $this->assertEquals(150, $roastedBean->total_stok);

        // Test clear residue
        $clearRes = $this->actingAs($user)->postJson('/internal-api/pengemasan/clear-residue', [
            'batch_id' => $batch->id,
        ]);

        $clearRes->assertStatus(200);

        $batch->refresh();
        $this->assertEquals(0, $batch->sisa_stok);
        $this->assertEquals(0, (float) $batch->berat_matang_bersih);

        $roastedBean->refresh();
        $this->assertEquals(0, $roastedBean->total_stok);
    }

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
