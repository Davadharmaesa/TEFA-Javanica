<?php

namespace Tests\Unit;

use App\Models\Biaya;
use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use App\Http\Controllers\Api\RoastingApiController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoastingWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    // =========================================================================
    // WB-ROAST-01: storeBatch() — Basis Paths P1 s.d. P7 (V(G)=7)
    // =========================================================================

    /**
     * TC-WB-ROAST-001 (Path 1: N1 -> N2)
     * Menguji decision D1 True: validasi form gagal jika parameter wajib (misal: suhu) kosong.
     */
    public function test_store_batch_path_1_validation_error(): void
    {
        $controller = new RoastingApiController();
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => 1,
            // suhu sengaja dikosongkan
        ]);

        $this->expectException(ValidationException::class);
        $controller->storeBatch($request);
    }

    /**
     * TC-WB-ROAST-002 (Path 2: N1 -> N3 -> N4 -> N5)
     * Menguji decision D2 True: stok green bean di gudang tidak mencukupi (HTTP 400).
     */
    public function test_store_batch_path_2_insufficient_stock(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Kintamani', 'asal_kopi' => 'Bali']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Kintamani', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        // Stok cuma ada 2 kg (2000 g)
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 2000,
            'harga_beli' => 100000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new RoastingApiController();
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-TEST-400',
            'profil_sangrai' => 'Medium',
            'waktu_first_crack' => 12.0,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5.0, // Butuh 5 kg (melebihi 2 kg)
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response = $controller->storeBatch($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('tidak mencukupi', $response->getContent());
    }

    /**
     * TC-WB-ROAST-003 (Path 3: N1 -> N3 -> N4 -> N6 -> N7 -> N12 -> N13 -> N15)
     * Menguji decision D3 False: koleksi stok kosong (loop foreach dilewati).
     */
    public function test_store_batch_path_3_empty_stocks_collection(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Toraja', 'asal_kopi' => 'Sulawesi']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Toraja', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        // Simulasikan ada stok 0 gram
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 0,
            'harga_beli' => 100000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new RoastingApiController();
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-TEST-EMPTY',
            'profil_sangrai' => 'Medium',
            'waktu_first_crack' => 10.0,
            'waktu_development' => 150,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 1.0,
            'berat_mentah_sortasi' => 0.9,
            'berat_matang_kotor' => 0.8,
            'berat_matang_bersih' => 0.7,
        ]);

        // Ditolak di N4 karena total stok 0 < 1000g
        $response = $controller->storeBatch($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * TC-WB-ROAST-004 (Path 4: N1 -> N3 -> N4 -> N6 -> N7 -> N8 -> N9 -> N10 -> N7 -> N8 -> N12 -> N13 -> N15)
     * Menguji kondisi normal Single-Batch FIFO (stok 1 batch mencukupi seluruh kebutuhan).
     */
    public function test_store_batch_path_4_single_batch_fifo(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Ijen', 'asal_kopi' => 'Bondowoso']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Ijen', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $stock = GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000, // 10 kg
            'harga_beli' => 100000, // Rp 100.000 / kg (Rp 100 / gram)
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new RoastingApiController();
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-SINGLE-FIFO',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.0,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0, // Pakai 5 kg
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0, // Hasil 4 kg
        ]);

        $response = $controller->storeBatch($request);

        $this->assertEquals(201, $response->getStatusCode());
        // Sisa stok mentah harus tepat 5.000 gram (10.000 - 5.000)
        $this->assertEquals(5000, $stock->fresh()->stok_gram);
        // Stok matang bertambah 4.000 gram
        $this->assertEquals(4000, $roastedBean->fresh()->total_stok);
        // Biaya bahan baku = 5000g * Rp 100 = Rp 500.000
        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'BATCH-SINGLE-FIFO',
            'biaya_bahan_baku' => 500000,
        ]);
    }

    /**
     * TC-WB-ROAST-005 (Path 5: N9 -> N11 -> loop batch berikutnya)
     * Menguji Multi-Batch FIFO: Batch A habis (0g), sisa potongan diambil dari Batch B.
     */
    public function test_store_batch_path_5_multi_batch_fifo(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Gayo', 'asal_kopi' => 'Aceh']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Gayo', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        // Batch A (tertua): 2 kg @ Rp 100.000/kg
        $stockA = GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 2000,
            'harga_beli' => 100000,
            'tanggal_beli' => now()->subDays(5)->toDateString(),
        ]);

        // Batch B (lebih baru): 5 kg @ Rp 120.000/kg
        $stockB = GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new RoastingApiController();
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-MULTI-FIFO',
            'profil_sangrai' => 'Dark Roast',
            'waktu_first_crack' => 13.0,
            'waktu_development' => 200,
            'durasi_pre_heating' => 5,
            'suhu' => 210,
            'berat_mentah_gudang' => 4.0, // Butuh 4 kg (Habiskan Stock A 2kg, ambil Stock B 2kg)
            'berat_mentah_sortasi' => 3.9,
            'berat_matang_kotor' => 3.4,
            'berat_matang_bersih' => 3.2,
        ]);

        $response = $controller->storeBatch($request);

        $this->assertEquals(201, $response->getStatusCode());
        // Stock A harus habis total (0 gram)
        $this->assertEquals(0, $stockA->fresh()->stok_gram);
        // Stock B tersisa 3000 gram (5000 - 2000)
        $this->assertEquals(3000, $stockB->fresh()->stok_gram);
    }

    /**
     * TC-WB-ROAST-006 (Path 6: N13 -> N14 -> N15)
     * Menguji boundary susut bobot negatif (sortasi < matang bersih) dikoreksi menjadi 0%.
     */
    public function test_store_batch_path_6_negative_burnoff_boundary(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Wamena', 'asal_kopi' => 'Papua']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Wamena', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new RoastingApiController();
        // Anomali: sortasi 3.0 kg, tetapi matang bersih diisi 3.5 kg (susut < 0)
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-BURNOFF-NEG',
            'profil_sangrai' => 'Light',
            'waktu_first_crack' => 10.0,
            'waktu_development' => 120,
            'durasi_pre_heating' => 5,
            'suhu' => 195,
            'berat_mentah_gudang' => 4.0,
            'berat_mentah_sortasi' => 3.0,
            'berat_matang_kotor' => 3.6,
            'berat_matang_bersih' => 3.5,
        ]);

        $response = $controller->storeBatch($request);
        $this->assertEquals(201, $response->getStatusCode());

        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'BATCH-BURNOFF-NEG',
            'persentase_susut' => 0, // Dikoreksi jadi 0
        ]);
    }

    /**
     * TC-WB-ROAST-007 (Path 7: N1 -> N3 -> N16)
     * Menguji penanganan exception database (DB::rollBack dan status 500).
     */
    public function test_store_batch_path_7_database_exception_rollback(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $this->actingAs($user);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Flores', 'asal_kopi' => 'NTT']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Flores', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 100000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Buat batch pertama
        RoastingBatch::create([
            'kode_batch' => 'BATCH-DUPLICATE-KEY',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 2,
            'berat_mentah_sortasi' => 1.9,
            'berat_matang_kotor' => 1.7,
            'berat_matang_bersih' => 1.6,
            'sisa_stok' => 1600,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 200000,
        ]);

        $controller = new RoastingApiController();
        // Duplikasi kode batch yang sama persis
        $request = Request::create('/internal-api/roasting/batches', 'POST', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-DUPLICATE-KEY',
            'profil_sangrai' => 'Medium',
            'waktu_first_crack' => 12.0,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 1.0,
            'berat_mentah_sortasi' => 0.9,
            'berat_matang_kotor' => 0.8,
            'berat_matang_bersih' => 0.7,
        ]);

        $this->expectException(ValidationException::class);
        $controller->storeBatch($request);
    }

    // =========================================================================
    // WB-ROAST-02: getStats() — Basis Paths P1 s.d. P2 (V(G)=2)
    // =========================================================================

    /**
     * TC-WB-ROAST-008 (Path 1: N1 -> N2 -> N4 -> N5)
     * Menguji decision D1 False: tabel batch kosong, avg_burnoff = 0 tanpa division by zero.
     */
    public function test_get_stats_path_1_empty_database_zero_division(): void
    {
        $controller = new RoastingApiController();
        $response = $controller->getStats();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, $data['rata_susut']);
        $this->assertEquals(0, $data['total_batch']);
    }

    /**
     * TC-WB-ROAST-009 (Path 2: N1 -> N2 -> N3 -> N5)
     * Menguji decision D1 True: total_raw > 0, rumus rata-rata susut dieksekusi normal.
     */
    public function test_get_stats_path_2_with_data_calculation(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Mandailing', 'asal_kopi' => 'Sumatera']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'RB Mandailing', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        // Simpan 1 batch: mentah 10 kg, matang bersih 8.2 kg -> susut = (10 - 8.2) / 10 * 100 = 18.0%
        RoastingBatch::create([
            'kode_batch' => 'BATCH-CALC-STATS',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 10.0,
            'berat_mentah_sortasi' => 9.8,
            'berat_matang_kotor' => 8.5,
            'berat_matang_bersih' => 8.2,
            'sisa_stok' => 8200,
            'persentase_susut' => 18.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1000000,
        ]);

        $controller = new RoastingApiController();
        $response = $controller->getStats();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(18.0, $data['rata_susut']);
        $this->assertEquals(1, $data['total_batch']);
    }
}

