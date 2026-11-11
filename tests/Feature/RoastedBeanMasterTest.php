<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoastedBeanMasterTest extends TestCase
{
    // Membersihkan database in-memory setiap kali sebuah test selesai (Test Isolation)
    use RefreshDatabase;

    /**
     * TC-RBMST-003: Membuat profil Master Roasted Bean baru secara manual.
     * TUJUAN: Menguji fungsi penambahan varian produk roasted bean baru dengan stok awal 0.
     */
    public function test_membuat_profil_master_roasted_bean_baru_secara_manual(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->postJson('/internal-api/master/roasted-beans', [
            'nama_kopi' => 'Espresso Blend Premium',
            'bentuk_akhir' => 'Biji Utuh',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_kopi', 'Espresso Blend Premium')
            ->assertJsonPath('data.bentuk_akhir', 'Biji Utuh')
            ->assertJsonPath('data.total_stok', 0);

        $this->assertDatabaseHas('roasted_bean', [
            'nama_kopi' => 'Espresso Blend Premium',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);
    }

    /**
     * TC-WB-RB-003: Tambah Roasted Bean valid dengan stok awal nol.
     */
    public function test_tambah_roasted_bean_valid_dengan_stok_awal_nol(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/roasted-beans', [
            'nama_kopi' => 'Espresso Blend Whitebox',
            'bentuk_akhir' => 'Biji Utuh',
        ])->assertOk()
            ->assertJsonPath('data.total_stok', 0);

        $this->assertDatabaseHas('roasted_bean', [
            'nama_kopi' => 'Espresso Blend Whitebox',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);
    }

    /**
     * TC-WB-RB-002: Tolak hapus Roasted Bean yang masih memiliki relasi batch.
     * TUJUAN: Menguji penolakan HTTP 400 Bad Request jika menghapus produk yang sudah ada di catatan roasting.
     */
    public function test_mencegah_penghapusan_master_yang_memiliki_relasi_batch(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $gb = GreenBean::create(['nama_biji' => 'GB Aceh', 'asal_kopi' => 'Aceh']);
        $rb = RoastedBean::create(['nama_kopi' => 'RB Aceh', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 2000]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-DEL-001', 'user_id' => $pic->id, 'green_bean_id' => $gb->id, 'roasted_bean_id' => $rb->id,
            'profil_sangrai' => 'Light', 'durasi_roasting' => 12, 'waktu_first_crack' => 9, 'waktu_development' => 45, 'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 2.5, 'berat_mentah_sortasi' => 2.5, 'berat_matang_kotor' => 2.2, 'berat_matang_bersih' => 2.0,
            'sisa_stok' => 2000, 'persentase_susut' => 20, 'tanggal_roasting' => now()->toDateString(), 'status_batch' => 'completed',
        ]);

        // Coba hapus Master Roasted Bean yang terkait batch
        $response = $this->actingAs($pic)->deleteJson("/internal-api/master/roasted-beans/{$rb->id}");

        $response->assertStatus(400)
            ->assertJson(['message' => 'Data Roasted Bean tidak dapat dihapus karena sudah terhubung dengan data produksi (Roasting Batch) atau produk kemasan!']);

        $this->assertDatabaseHas('roasted_bean', ['id' => $rb->id]);
    }

    /**
     * TC-WB-RB-001: Hapus Roasted Bean tanpa relasi.
     */
    public function test_roasted_bean_dapat_dihapus_jika_tidak_terpakai(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = RoastedBean::create(['nama_kopi' => 'Robusta', 'bentuk_akhir' => 'Biji Utuh']);

        $this->actingAs($pic)->deleteJson("/internal-api/master/roasted-beans/{$bean->id}")
            ->assertOk();

        $this->assertDatabaseMissing('roasted_bean', ['id' => $bean->id]);
    }

    /**
     * TC-WB-RB-004: Tolak bentuk akhir kosong saat membuat Roasted Bean.
     */
    public function test_pembuatan_roasted_bean_ditolak_jika_bentuk_akhir_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/roasted-beans', [
            'nama_kopi' => 'Robusta',
            'bentuk_akhir' => '',
        ])->assertStatus(422)->assertJsonValidationErrors('bentuk_akhir');

        $this->assertDatabaseMissing('roasted_bean', ['nama_kopi' => 'Robusta']);
    }

    /**
     * TC-WB-RB-005: Perbarui Roasted Bean dengan data valid.
     */
    public function test_roasted_bean_dapat_diperbarui_dengan_data_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = RoastedBean::create(['nama_kopi' => 'Robusta', 'bentuk_akhir' => 'Biji Utuh']);

        $this->actingAs($pic)->putJson("/internal-api/master/roasted-beans/{$bean->id}", [
            'nama_kopi' => 'Robusta Premium',
            'bentuk_akhir' => 'Bubuk Halus',
        ])->assertOk()->assertJsonPath('data.nama_kopi', 'Robusta Premium');

        $this->assertDatabaseHas('roasted_bean', ['id' => $bean->id, 'nama_kopi' => 'Robusta Premium']);
    }

    /**
     * TC-WB-RB-006: Tolak nama kosong saat update Roasted Bean.
     */
    public function test_pembaruan_roasted_bean_ditolak_jika_nama_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = RoastedBean::create(['nama_kopi' => 'Robusta', 'bentuk_akhir' => 'Biji Utuh']);

        $this->actingAs($pic)->putJson("/internal-api/master/roasted-beans/{$bean->id}", [
            'nama_kopi' => '',
            'bentuk_akhir' => 'Biji Utuh',
        ])->assertStatus(422)->assertJsonValidationErrors('nama_kopi');

        $this->assertDatabaseHas('roasted_bean', ['id' => $bean->id, 'nama_kopi' => 'Robusta']);
    }

    /**
     * TC-RBMST-001: Tanggal kedaluwarsa H+30 sejak roasting.
     */
    public function test_sistem_otomatis_mengeset_tanggal_kadaluarsa_h_plus_30_dari_roasting(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create(['nama_biji' => 'Arabica Kintamani', 'asal_kopi' => 'Bali']);
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Kintamani Roast',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4000,
        ]);
        $roastDate = '2026-09-23';
        $batch = new RoastingBatch([
            'kode_batch' => 'BATCH-EXP-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'persentase_susut' => 20,
            'tanggal_roasting' => $roastDate,
            'status_batch' => 'completed',
        ]);
        $batch->timestamps = false;
        $batch->created_at = Carbon::parse($roastDate . ' 08:30:00');
        $batch->updated_at = Carbon::parse($roastDate . ' 08:30:00');
        $batch->save();

        $expectedExpire = Carbon::parse($roastDate)->addDays(30);
        $this->assertEquals('2026-10-23', $expectedExpire->toDateString());
        $response = $this->actingAs($pic)->get('/inventory');
        $response->assertOk();
        $batchData = collect($response->viewData('roastingBatches'))
            ->firstWhere('kode', 'BATCH-EXP-001');
        $this->assertNotNull($batchData);
        $this->assertEquals($expectedExpire->format('d-m-Y'), $batchData['tanggal_expire']);
    }

    /**
     * TC-RBMST-002: Update stok hasil roasting per batch.
     */
    public function test_update_stok_hasil_roasting_per_batch_pada_master_roasted_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create(['nama_biji' => 'Robusta Dampit', 'asal_kopi' => 'Malang']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Dampit Dark', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 4000]);
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-UPD-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Dark Roast',
            'durasi_roasting' => 18,
            'waktu_first_crack' => 11,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.1,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $this->actingAs($pic)->put("/inventory/roasted-bean/{$batch->id}", [
            'total_stok' => 3500,
        ])->assertRedirect(route('inventory.index', ['tab' => 'roastedbean']))
            ->assertSessionHas('success');

        $batch->refresh();
        $roastedBean->refresh();
        $this->assertEquals(3500, $batch->sisa_stok);
        $this->assertEquals(3.5, $batch->berat_matang_bersih);
        $this->assertEquals(3500, $roastedBean->total_stok);
    }

    /**
     * TC-RBMST-004 / TC-WB-RB-007: Daftar Roasted Bean dengan stok.
     */
    /**
     * TC-RBMST-004: Menampilkan daftar Master Roasted Bean beserta akumulasi stok.
     */
    public function test_menampilkan_daftar_seluruh_master_roasted_bean_beserta_total_akumulasi_stoknya(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Multi Varietas', 'asal_kopi' => 'Indonesia']);
        $firstBean = RoastedBean::create(['nama_kopi' => 'Arabica Gayo Wine', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 6000]);
        $secondBean = RoastedBean::create(['nama_kopi' => 'Robusta Flores Fine', 'bentuk_akhir' => 'Bubuk Halus', 'total_stok' => 3500]);

        foreach ([[$firstBean, 'BATCH-RB1', 6.0, 6000], [$secondBean, 'BATCH-RB2', 3.5, 3500]] as [$bean, $code, $weight, $stock]) {
            RoastingBatch::create([
                'kode_batch' => $code,
                'user_id' => $pic->id,
                'green_bean_id' => $greenBean->id,
                'roasted_bean_id' => $bean->id,
                'profil_sangrai' => 'Medium',
                'durasi_roasting' => 15,
                'waktu_first_crack' => 10,
                'waktu_development' => 60,
                'durasi_pre_heating' => 5,
                'berat_mentah_gudang' => $weight + 1,
                'berat_mentah_sortasi' => $weight + 1,
                'berat_matang_kotor' => $weight + 0.2,
                'berat_matang_bersih' => $weight,
                'sisa_stok' => $stock,
                'persentase_susut' => 14,
                'tanggal_roasting' => now()->toDateString(),
                'status_batch' => 'completed',
            ]);
        }

        $apiResponse = $this->actingAs($pic)->getJson('/internal-api/master/roasted-beans');
        $apiResponse->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['nama_kopi' => 'Arabica Gayo Wine', 'total_stok' => 6000])
            ->assertJsonFragment(['nama_kopi' => 'Robusta Flores Fine', 'total_stok' => 3500]);
        $inventoryResponse = $this->actingAs($pic)->get('/inventory');
        $inventoryResponse->assertOk();
        $beans = collect($inventoryResponse->viewData('roastedBeans'));
        $this->assertEquals(6.0, $beans->firstWhere('id', $firstBean->id)['stok']);
        $this->assertEquals(3.5, $beans->firstWhere('id', $secondBean->id)['stok']);
    }

    /**
     * TC-WB-RB-007: Tampilkan daftar seluruh Roasted Bean.
     */
    public function test_mengembalikan_daftar_roasted_bean_dari_api_master(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = RoastedBean::create([
            'nama_kopi' => 'Robusta Whitebox',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1500,
        ]);

        $this->actingAs($pic)->getJson('/internal-api/master/roasted-beans')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $bean->id,
                'nama_kopi' => 'Robusta Whitebox',
            ]);
    }

    /**
     * TC-UAT-001: Operator menyelesaikan roasting dan memeriksa stok Roasted Bean.
     */
    public function test_operator_menyelesaikan_pencatatan_roasting_dan_memeriksa_stok_roasted_bean_di_gudang(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabica Java Preanger', 'asal_kopi' => 'Jawa Barat']);
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 90000,
            'total_harga' => 450000,
            'tanggal_beli' => now()->toDateString(),
        ]);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Roasted Java Preanger', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 0]);

        $this->actingAs($operator)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-UAT-001',
            'profil_sangrai' => 'Medium Roast',
            'suhu' => 205,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ])->assertStatus(201);

        $roastedBean->refresh();
        $this->assertEquals(4000, $roastedBean->total_stok);
        $batch = collect($this->actingAs($operator)->get('/inventory')->viewData('roastingBatches'))
            ->firstWhere('kode', 'BATCH-UAT-001');
        $this->assertEquals(4.0, $batch['stok']);
    }

}