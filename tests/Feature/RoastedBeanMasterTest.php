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
    use RefreshDatabase;

    /**
     * TC-RBMST-001: Sistem otomatis mengeset tanggal kadaluarsa H+30 dari roasting
     */
    public function test_sistem_otomatis_mengeset_tanggal_kadaluarsa_h_plus_30_dari_roasting(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'Arabica Kintamani',
            'asal_kopi' => 'Bali',
        ]);

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

        // Verifikasi perhitungan H+30 secara matematis dan sistem
        $expectedExpireIso = Carbon::parse($roastDate)->addDays(30)->toDateString(); // 2026-10-23
        $this->assertEquals('2026-10-23', $expectedExpireIso);

        // Verifikasi tampilan expired date pada menu gudang / inventory
        $response = $this->actingAs($pic)->get('/inventory');
        $response->assertStatus(200);

        $roastingBatches = collect($response->viewData('roastingBatches'));
        $batchData = $roastingBatches->firstWhere('kode', 'BATCH-EXP-001');

        $this->assertNotNull($batchData);
        $this->assertEquals(Carbon::parse($roastDate)->addDays(30)->format('d-m-Y'), $batchData['tanggal_expire']);
    }

    /**
     * TC-RBMST-002: Update stok hasil roasting per batch pada master roasted bean
     */
    public function test_update_stok_hasil_roasting_per_batch_pada_master_roasted_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'Robusta Dampit',
            'asal_kopi' => 'Malang',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Robusta Dampit Dark',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4000,
        ]);

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

        // Pengguna memperbarui stok batch menjadi 3500 gram (3.5 Kg)
        $response = $this->actingAs($pic)->put("/inventory/roasted-bean/{$batch->id}", [
            'total_stok' => 3500,
        ]);

        $response->assertRedirect(route('inventory.index', ['tab' => 'roastedbean']));
        $response->assertSessionHas('success');

        // Pastikan sisa stok dan berat matang bersih pada batch terupdate
        $batch->refresh();
        $this->assertEquals(3500, $batch->sisa_stok);
        $this->assertEquals(3.5, $batch->berat_matang_bersih);

        // Pastikan total stok master roasted bean tersinkronisasi
        $roastedBean->refresh();
        $this->assertEquals(3500, $roastedBean->total_stok);
    }

    /**
     * TC-RBMST-003: Membuat profil Master Roasted Bean baru secara manual
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
     * TC-RBMST-004: Menampilkan daftar seluruh Master Roasted Bean beserta total akumulasi stoknya
     */
    public function test_menampilkan_daftar_seluruh_master_roasted_bean_beserta_total_akumulasi_stoknya(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Multi Varietas',
            'asal_kopi' => 'Indonesia',
        ]);

        $rb1 = RoastedBean::create([
            'nama_kopi' => 'Arabica Gayo Wine',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 6000,
        ]);

        $rb2 = RoastedBean::create([
            'nama_kopi' => 'Robusta Flores Fine',
            'bentuk_akhir' => 'Bubuk Halus',
            'total_stok' => 3500,
        ]);

        // Buat batch completed untuk masing-masing master roasted bean
        RoastingBatch::create([
            'kode_batch' => 'BATCH-RB1',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $rb1->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 7.0,
            'berat_mentah_sortasi' => 7.0,
            'berat_matang_kotor' => 6.2,
            'berat_matang_bersih' => 6.0,
            'sisa_stok' => 6000,
            'persentase_susut' => 14.3,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-RB2',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $rb2->id,
            'profil_sangrai' => 'Dark',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 4.0,
            'berat_mentah_sortasi' => 4.0,
            'berat_matang_kotor' => 3.6,
            'berat_matang_bersih' => 3.5,
            'sisa_stok' => 3500,
            'persentase_susut' => 12.5,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        // 1. Verifikasi via Master Data API
        $apiRes = $this->actingAs($pic)->getJson('/internal-api/master/roasted-beans');
        $apiRes->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['nama_kopi' => 'Arabica Gayo Wine', 'total_stok' => 6000])
            ->assertJsonFragment(['nama_kopi' => 'Robusta Flores Fine', 'total_stok' => 3500]);

        // 2. Verifikasi via Halaman Inventory
        $invRes = $this->actingAs($pic)->get('/inventory');
        $invRes->assertStatus(200);

        $viewRoastedBeans = collect($invRes->viewData('roastedBeans'));
        $this->assertCount(2, $viewRoastedBeans);
        $this->assertEquals(6.0, $viewRoastedBeans->firstWhere('id', $rb1->id)['stok']);
        $this->assertEquals(3.5, $viewRoastedBeans->firstWhere('id', $rb2->id)['stok']);
    }

    /**
     * TC-UAT-001: Operator menyelesaikan pencatatan roasting dan memeriksa stok Roasted Bean di gudang
     */
    public function test_operator_menyelesaikan_pencatatan_roasting_dan_memeriksa_stok_roasted_bean_di_gudang(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabica Java Preanger',
            'asal_kopi' => 'Jawa Barat',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 90000,
            'total_harga' => 450000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Java Preanger',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        // Operator melakukan pencatatan proses roasting (berat mentah 5 kg, hasil matang bersih 4 kg)
        $roastResponse = $this->actingAs($operator)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-UAT-001',
            'profil_sangrai' => 'Medium Roast',
            'suhu' => 205,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $roastResponse->assertStatus(201);

        // Verifikasi batch tersimpan dengan status completed dan sisa_stok 4000 gram
        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'BATCH-UAT-001',
            'status_batch' => 'completed',
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
        ]);

        // Verifikasi stok Roasted Bean di database bertambah 4000 gram
        $roastedBean->refresh();
        $this->assertEquals(4000, $roastedBean->total_stok);

        // Operator memeriksa ketersediaan stok di menu Gudang
        $gudangResponse = $this->actingAs($operator)->get('/inventory');
        $gudangResponse->assertStatus(200);

        $gudangBatches = collect($gudangResponse->viewData('roastingBatches'));
        $savedBatch = $gudangBatches->firstWhere('kode', 'BATCH-UAT-001');

        $this->assertNotNull($savedBatch);
        $this->assertEquals(4.0, $savedBatch['stok']);
    }
}
