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
}
