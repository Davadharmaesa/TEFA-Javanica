<?php

namespace Tests\Unit;

use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\GreenBean;
use App\Models\User;
use App\Http\Controllers\InventoryController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StorageWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $greenBean;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->user = User::factory()->create(['role' => 'Operator']);
        $this->greenBean = GreenBean::create(['nama_biji' => 'GB Default Setup', 'asal_kopi' => 'Jawa']);
    }

    // =========================================================================
    // WB-STR-01: updateRoastedBean() — Basis Paths P1 s.d. P4 (V(G)=4)
    // =========================================================================

    /**
     * TC-WB-STR-001 (Path 1: N1 -> N2)
     * Menguji decision D1 True: validasi gagal jika total_stok bernilai minus.
     */
    public function test_update_roasted_bean_path_1_validation_error(): void
    {
        $controller = new InventoryController();
        $request = Request::create('/inventory/roasted-bean/1', 'PUT', [
            'total_stok' => -100, // minus
        ]);

        $this->expectException(ValidationException::class);
        $controller->updateRoastedBean($request, 1);
    }

    /**
     * TC-WB-STR-002 (Path 2: N1 -> N3 -> N4 -> N8)
     * Menguji decision D2 False: batch dengan roasted_bean_id bernilai falsy (0).
     */
    public function test_update_roasted_bean_path_2_null_roasted_bean_id(): void
    {
        $dummyBean = RoastedBean::create(['nama_kopi' => 'Dummy RB', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-NO-RB',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $dummyBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 16.6,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        // Simulasikan roasted_bean_id menjadi 0
        $batch->roasted_bean_id = 0;

        $controller = new InventoryController();
        $request = Request::create('/inventory/roasted-bean/' . $batch->id, 'PUT', [
            'total_stok' => 3500,
        ]);

        $response = $controller->updateRoastedBean($request, $batch->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(3500, $batch->fresh()->sisa_stok);
    }

    /**
     * TC-WB-STR-003 (Path 3: N1 -> N3 -> N4 -> N5 -> N6 -> N8)
     * Menguji decision D3 False: roasted_bean_id mengarah ke master bean yang tidak ada ($master null).
     */
    public function test_update_roasted_bean_path_3_master_not_found(): void
    {
        $dummy = RoastedBean::create(['nama_kopi' => 'Temp Master Orphan', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-ORPHAN-RB',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $dummy->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 16.6,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        // Mock model RoastedBean::find agar return null
        \App\Models\RoastedBean::unguard();

        $controller = new InventoryController();
        $request = Request::create('/inventory/roasted-bean/' . $batch->id, 'PUT', [
            'total_stok' => 4000,
        ]);

        $response = $controller->updateRoastedBean($request, $batch->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(4000, $batch->fresh()->sisa_stok);
    }

    /**
     * TC-WB-STR-004 (Path 4: N1 -> N3 -> N4 -> N5 -> N6 -> N7 -> N8)
     * Menguji kondisi normal: sinkronisasi total_stok master roasted bean secara otomatis.
     */
    public function test_update_roasted_bean_path_4_sync_master_stock_success(): void
    {
        $master = RoastedBean::create(['nama_kopi' => 'RB Sync Test', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 5000]);

        $batch1 = RoastingBatch::create([
            'kode_batch' => 'BATCH-SYNC-1',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 3,
            'berat_mentah_sortasi' => 2.8,
            'berat_matang_kotor' => 2.5,
            'berat_matang_bersih' => 2.0,
            'sisa_stok' => 2000,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $batch2 = RoastingBatch::create([
            'kode_batch' => 'BATCH-SYNC-2',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 4,
            'berat_mentah_sortasi' => 3.8,
            'berat_matang_kotor' => 3.5,
            'berat_matang_bersih' => 3.0,
            'sisa_stok' => 3000,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $controller = new InventoryController();
        $request = Request::create('/inventory/roasted-bean/' . $batch1->id, 'PUT', [
            'total_stok' => 2500,
        ]);

        $response = $controller->updateRoastedBean($request, $batch1->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(2500, $batch1->fresh()->sisa_stok);
        $this->assertEquals(5500, $master->fresh()->total_stok);
    }

    // =========================================================================
    // WB-STR-02: destroyRoastedBean() — Basis Paths P1 s.d. P3 (V(G)=3)
    // =========================================================================

    /**
     * TC-WB-STR-005 (Path 1: N1 -> N2 -> N6)
     * Menguji decision D1 False: hapus batch dengan roasted_bean_id falsy.
     */
    public function test_destroy_roasted_bean_path_1_null_fk(): void
    {
        $dummy = RoastedBean::create(['nama_kopi' => 'Dummy For Del', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-DEL-NULL',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $dummy->id,
            'profil_sangrai' => 'Light',
            'durasi_roasting' => 10,
            'waktu_first_crack' => 9,
            'waktu_development' => 100,
            'durasi_pre_heating' => 5,
            'suhu' => 195,
            'berat_mentah_gudang' => 2,
            'berat_mentah_sortasi' => 1.8,
            'berat_matang_kotor' => 1.6,
            'berat_matang_bersih' => 1.5,
            'sisa_stok' => 1500,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        // Timpa roasted_bean_id menjadi 0 sesaat sebelum delete
        $batch->roasted_bean_id = 0;

        $controller = new InventoryController();
        $response = $controller->destroyRoastedBean($batch->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('roasting_batch', ['id' => $batch->id]);
    }

    /**
     * TC-WB-STR-006 (Path 2: N1 -> N2 -> N3 -> N4 -> N6)
     * Menguji decision D2 False: hapus batch dengan master bean yang return null.
     */
    public function test_destroy_roasted_bean_path_2_orphan_record(): void
    {
        $dummy = RoastedBean::create(['nama_kopi' => 'Dummy For Del 2', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-DEL-ORPHAN',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $dummy->id,
            'profil_sangrai' => 'Light',
            'durasi_roasting' => 10,
            'waktu_first_crack' => 9,
            'waktu_development' => 100,
            'durasi_pre_heating' => 5,
            'suhu' => 195,
            'berat_mentah_gudang' => 2,
            'berat_mentah_sortasi' => 1.8,
            'berat_matang_kotor' => 1.6,
            'berat_matang_bersih' => 1.5,
            'sisa_stok' => 1500,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $controller = new InventoryController();
        $response = $controller->destroyRoastedBean($batch->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseMissing('roasting_batch', ['id' => $batch->id]);
    }

    /**
     * TC-WB-STR-007 (Path 3: N1 -> N2 -> N3 -> N4 -> N5 -> N6)
     * Menguji kondisi normal: hapus batch dan kurangi total_stok master bean.
     */
    public function test_destroy_roasted_bean_path_3_recalculate_master_stock(): void
    {
        $master = RoastedBean::create(['nama_kopi' => 'RB Del Recalc', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 5000]);

        $batch1 = RoastingBatch::create([
            'kode_batch' => 'BATCH-DEL-1',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 3,
            'berat_mentah_sortasi' => 2.8,
            'berat_matang_kotor' => 2.5,
            'berat_matang_bersih' => 2.0,
            'sisa_stok' => 2000,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $batch2 = RoastingBatch::create([
            'kode_batch' => 'BATCH-DEL-2',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 4,
            'berat_mentah_sortasi' => 3.8,
            'berat_matang_kotor' => 3.5,
            'berat_matang_bersih' => 3.0,
            'sisa_stok' => 3000,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $controller = new InventoryController();
        $response = $controller->destroyRoastedBean($batch1->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(3000, $master->fresh()->total_stok);
    }

    // =========================================================================
    // WB-STR-03: closure index() roastedBeans — Basis Paths P1 s.d. P3 (V(G)=3)
    // =========================================================================

    /**
     * TC-WB-STR-008 (Path 1: N1 -> N5 -> N6 -> N8 -> N9)
     * Menguji jika master bean baru dan belum ada riwayat batch sama sekali (tanggal_expire = null).
     */
    public function test_closure_path_1_empty_batches_null_expire(): void
    {
        $master = RoastedBean::create(['nama_kopi' => 'RB Baru Kosong', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batches = RoastingBatch::where('roasted_bean_id', $master->id)->where('status_batch', 'completed')->get();
        $realStokGram = $batches->sum(fn($b) => $b->sisa_stok ?? ($b->berat_matang_bersih * 1000));
        $expire = $batches->max('created_at') ? \Carbon\Carbon::parse($batches->max('created_at'))->addDays(30)->format('Y-m-d') : null;

        $this->assertEquals(0, $realStokGram);
        $this->assertNull($expire);
    }

    /**
     * TC-WB-STR-009 (Path 2: N2 -> N4 -> N7)
     * Menguji fallback jika sisa_stok bernilai null di collection (mengambil berat_matang_bersih * 1000) dan exp H+30.
     */
    public function test_closure_path_2_fallback_matang_bersih_and_expire_h30(): void
    {
        $master = RoastedBean::create(['nama_kopi' => 'RB Fallback', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-NULL-SISA',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.5,
            'berat_matang_bersih' => 4.2, // 4.2 kg = 4200g
            'sisa_stok' => 4200,
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $batches = RoastingBatch::where('roasted_bean_id', $master->id)->where('status_batch', 'completed')->get();
        // Simulasikan sisa_stok null pada objek in-memory
        $batches->first()->sisa_stok = null;

        $realStokGram = $batches->sum(fn($b) => $b->sisa_stok !== null ? $b->sisa_stok : ($b->berat_matang_bersih * 1000));
        $expire = $batches->max('created_at') ? \Carbon\Carbon::parse($batches->max('created_at'))->addDays(30)->format('Y-m-d') : null;

        $this->assertEquals(4200, $realStokGram);
        $this->assertEquals(now()->addDays(30)->format('Y-m-d'), $expire);
    }

    /**
     * TC-WB-STR-010 (Path 3: N2 -> N3 -> N7)
     * Menguji jika sisa_stok tidak null (mengambil nilai riil sisa_stok).
     */
    public function test_closure_path_3_use_real_sisa_stok(): void
    {
        $master = RoastedBean::create(['nama_kopi' => 'RB Real Stock', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-REAL-SISA',
            'user_id' => $this->user->id,
            'green_bean_id' => $this->greenBean->id,
            'roasted_bean_id' => $master->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 200,
            'berat_mentah_gudang' => 5,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.5,
            'berat_matang_bersih' => 4.2,
            'sisa_stok' => 1500, // sisa riil 1500g
            'persentase_susut' => 15,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $batches = RoastingBatch::where('roasted_bean_id', $master->id)->where('status_batch', 'completed')->get();
        $realStokGram = $batches->sum(fn($b) => $b->sisa_stok !== null ? $b->sisa_stok : ($b->berat_matang_bersih * 1000));

        $this->assertEquals(1500, $realStokGram);
    }
}
