<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenBeanMasterTest extends TestCase
{
    // Membersihkan database in-memory setiap kali sebuah test selesai (Test Isolation)
    use RefreshDatabase;

    /**
     * TC-GBMST-001: Tambah Green Bean dan harga beli awal.
     * TUJUAN: Menguji penambahan komoditas Green Bean baru dan stok awal di gudang.
     */
    public function test_tambah_data_master_green_bean_baru(): void
    {
        // LANGKAH 1: Login sebagai PIC
        $pic = User::factory()->create(['role' => 'PIC']);

        // LANGKAH 2: PIC menambahkan data komoditas Green Bean baru melalui master API
        $response = $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => 'Arabica Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        // Verifikasi HTTP 200 OK dan data sesuai JSON response
        $response->assertStatus(200)
            ->assertJsonPath('data.nama_biji', 'Arabica Ijen')
            ->assertJsonPath('data.asal_kopi', 'Bondowoso');

        // Pastikan tersimpan di tabel database 'green_bean'
        $this->assertDatabaseHas('green_bean', [
            'nama_biji' => 'Arabica Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $greenBean = GreenBean::where('nama_biji', 'Arabica Ijen')->first();
        $inventoryRes = $this->actingAs($pic)->post('/inventory/green-bean', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'tanggal_beli' => now()->format('d/m/Y'),
            'total_harga' => 475000,
        ]);
        $inventoryRes->assertRedirect(route('inventory.index', ['tab' => 'greenbean']));
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 95000,
            'total_harga' => 475000,
        ]);

        $listRes = $this->actingAs($pic)->getJson('/internal-api/master/green-beans');
        $listRes->assertStatus(200)
            ->assertJsonFragment([
                'nama_biji' => 'Arabica Ijen',
                'asal_kopi' => 'Bondowoso',
                'stok_gram' => 5000,
            ]);
    }

    /**
     * TC-GBMST-002: Tolak nama Green Bean kosong.
     * TUJUAN: Menguji sistem menolak input komoditas jika nama biji dikosongkan.
     */
    public function test_validasi_nama_biji_kosong(): void
    {
        // LANGKAH 1: Login sebagai PIC
        $pic = User::factory()->create(['role' => 'PIC']);

        // LANGKAH 2: Submit nama_biji kosong string ""
        $response = $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => '',
            'asal_kopi' => 'Bondowoso',
        ]);

        // LANGKAH 3: Sistem harus menolak dengan HTTP 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors('nama_biji');

        // Pastikan tidak ada data yang masuk ke database
        $this->assertDatabaseMissing('green_bean', [
            'asal_kopi' => 'Bondowoso',
        ]);
    }

    /**
     * TC-GBMST-003: Perbarui Green Bean dan harga stok.
     * TUJUAN: Menguji pengubahan informasi komoditas dan stok pembelian.
     */
    public function test_memperbarui_update_harga_atau_nama_data_green_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'Arabica Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $stock = GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 95000,
            'total_harga' => 475000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $response = $this->actingAs($pic)->putJson("/internal-api/master/green-beans/{$greenBean->id}", [
            'nama_biji' => 'Arabica Ijen Premium',
            'asal_kopi' => 'Bondowoso',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_biji', 'Arabica Ijen Premium');

        $this->assertDatabaseHas('green_bean', [
            'id' => $greenBean->id,
            'nama_biji' => 'Arabica Ijen Premium',
        ]);

        $this->actingAs($pic)->put("/inventory/green-bean/{$stock->id}", [
            'stok_gram' => 5000,
            'tanggal_beli' => now()->format('d/m/Y'),
            'total_harga' => 425000,
        ])->assertRedirect(route('inventory.index', ['tab' => 'greenbean']));

        $this->assertDatabaseHas('green_bean_stock', [
            'id' => $stock->id,
            'harga_beli' => 85000,
            'total_harga' => 425000,
        ]);
    }

    /**
     * TC-WB-GB-003: Tambah Green Bean valid.
     */
    public function test_tambah_green_bean_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => 'Arabica Ijen Whitebox',
            'asal_kopi' => 'Bondowoso',
        ])->assertOk()
            ->assertJsonPath('data.nama_biji', 'Arabica Ijen Whitebox');

        $this->assertDatabaseHas('green_bean', [
            'nama_biji' => 'Arabica Ijen Whitebox',
            'asal_kopi' => 'Bondowoso',
        ]);
    }

    /**
     * TC-WB-GB-004: Tolak nama Green Bean kosong.
     */
    public function test_tolak_nama_green_bean_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => '',
            'asal_kopi' => 'Bondowoso',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('nama_biji');

        $this->assertDatabaseMissing('green_bean', ['asal_kopi' => 'Bondowoso']);
    }

    /**
     * TC-WB-GB-005: Perbarui Green Bean valid.
     */
    public function test_perbarui_green_bean_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = GreenBean::create(['nama_biji' => 'Arabica Ijen', 'asal_kopi' => 'Bondowoso']);

        $this->actingAs($pic)->putJson("/internal-api/master/green-beans/{$bean->id}", [
            'nama_biji' => 'Arabica Ijen Premium',
            'asal_kopi' => 'Ijen',
        ])->assertOk()
            ->assertJsonPath('data.nama_biji', 'Arabica Ijen Premium')
            ->assertJsonPath('data.asal_kopi', 'Ijen');

        $this->assertDatabaseHas('green_bean', [
            'id' => $bean->id,
            'nama_biji' => 'Arabica Ijen Premium',
            'asal_kopi' => 'Ijen',
        ]);
    }

    /**
     * TC-GBMST-004: Indikator peringatan untuk stok Green Bean rendah.
     */
    public function test_indikator_visual_peringatan_stok_rendah_pada_tabel_green_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $lowBean = GreenBean::create(['nama_biji' => 'GB Rendah', 'asal_kopi' => 'Bondowoso']);
        $safeBean = GreenBean::create(['nama_biji' => 'GB Aman', 'asal_kopi' => 'Ijen']);
        GreenBeanStock::create([
            'green_bean_id' => $lowBean->id,
            'stok_gram' => 5000,
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);
        GreenBeanStock::create([
            'green_bean_id' => $safeBean->id,
            'stok_gram' => 15000,
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $apiResponse = $this->actingAs($pic)->getJson('/internal-api/master/green-beans');
        $apiResponse->assertOk();
        $beans = collect($apiResponse->json());
        $this->assertLessThan(10, $beans->firstWhere('id', $lowBean->id)['stok_gram'] / 1000);
        $this->assertGreaterThanOrEqual(10, $beans->firstWhere('id', $safeBean->id)['stok_gram'] / 1000);
        $this->actingAs($pic)->get('/master')->assertOk()
            ->assertSee("(item.stok_gram/1000) < 10 ? 'text-red-500' : 'text-primary'", false);
    }

    /**
     * TC-WB-GB-008: Daftar Green Bean dengan stok aktual.
     */
    public function test_menampilkan_total_sisa_stok_per_jenis_green_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $robusta = GreenBean::create(['nama_biji' => 'Robusta Dampit', 'asal_kopi' => 'Malang']);
        $arabica = GreenBean::create(['nama_biji' => 'Arabica Kintamani', 'asal_kopi' => 'Bali']);
        GreenBeanStock::create(['green_bean_id' => $robusta->id, 'stok_gram' => 8000, 'harga_beli' => 60000, 'tanggal_beli' => now()->toDateString()]);
        GreenBeanStock::create(['green_bean_id' => $arabica->id, 'stok_gram' => 12000, 'harga_beli' => 90000, 'tanggal_beli' => now()->toDateString()]);

        $response = $this->actingAs($pic)->get('/inventory');
        $response->assertOk();
        $beans = collect($response->viewData('greenBeans'));
        $this->assertEquals(8, $beans->firstWhere('green_bean_id', $robusta->id)['stok']);
        $this->assertEquals(8000, $beans->firstWhere('green_bean_id', $robusta->id)['stok_raw']);
        $this->assertEquals(12, $beans->firstWhere('green_bean_id', $arabica->id)['stok']);
        $this->assertEquals(12000, $beans->firstWhere('green_bean_id', $arabica->id)['stok_raw']);

        $apiResponse = $this->actingAs($pic)->getJson('/internal-api/master/green-beans');
        $apiResponse->assertOk()
            ->assertJsonFragment(['id' => $robusta->id, 'stok_gram' => 8000])
            ->assertJsonFragment(['id' => $arabica->id, 'stok_gram' => 12000]);
    }

    /**
     * TC-WB-GB-007: Daftar Green Bean tanpa stok.
     */
    public function test_daftar_green_bean_mengembalikan_stok_nol_jika_tidak_ada_pembelian(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        GreenBean::create(['nama_biji' => 'Arabika tanpa stok', 'asal_kopi' => 'Ijen']);

        $this->actingAs($pic)->getJson('/internal-api/master/green-beans')
            ->assertOk()
            ->assertJsonFragment(['nama_biji' => 'Arabika tanpa stok', 'stok_gram' => 0]);
    }

    /**
     * TC-GBMST-005: Hapus data master Green Bean tanpa relasi.
     */
    public function test_menghapus_delete_data_master_yang_belum_memiliki_relasi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // Data dummy tanpa relasi
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Dummy Test',
            'asal_kopi' => 'Lokasi Test',
        ]);

        // Berhasil dihapus karena tidak punya keterikatan batch
        $response = $this->actingAs($pic)->deleteJson("/internal-api/master/green-beans/{$greenBean->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Data Green Bean berhasil dihapus']);

        $this->assertDatabaseMissing('green_bean', [
            'id' => $greenBean->id,
        ]);

    }

    /**
     * TC-WB-GB-001: Hapus Green Bean tanpa relasi.
     */
    public function test_hapus_green_bean_tanpa_relasi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = GreenBean::create(['nama_biji' => 'Green Bean Whitebox', 'asal_kopi' => 'Ijen']);

        $this->actingAs($pic)
            ->deleteJson("/internal-api/master/green-beans/{$bean->id}")
            ->assertOk();

        $this->assertDatabaseMissing('green_bean', ['id' => $bean->id]);
    }

    /**
     * TC-WB-GB-002: Tolak hapus Green Bean yang masih terhubung ke batch.
     */
    public function test_penolakan_penghapusan_green_bean_yang_memiliki_relasi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Terkait Roasting',
            'asal_kopi' => 'Bali',
        ]);
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'RB Bali',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-REL-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5,
            'berat_mentah_sortasi' => 5,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
        ]);

        $this->actingAs($pic)
            ->deleteJson("/internal-api/master/green-beans/{$greenBean->id}")
            ->assertStatus(400);

        $this->assertDatabaseHas('green_bean', ['id' => $greenBean->id]);
    }

    /**
     * TC-WB-GB-006: Tolak nama kosong saat update Green Bean.
     */
    public function test_update_green_bean_ditolak_jika_nama_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $bean = GreenBean::create(['nama_biji' => 'Arabika', 'asal_kopi' => 'Ijen']);

        $this->actingAs($pic)->putJson("/internal-api/master/green-beans/{$bean->id}", [
            'nama_biji' => '',
            'asal_kopi' => 'Ijen',
        ])->assertStatus(422)->assertJsonValidationErrors('nama_biji');

        $this->assertDatabaseHas('green_bean', ['id' => $bean->id, 'nama_biji' => 'Arabika']);
    }

}
