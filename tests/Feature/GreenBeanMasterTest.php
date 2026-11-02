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
    use RefreshDatabase;

    /**
     * TC-GBMST-001: Tambah data master green bean baru dengan harga beli awal
     */
    public function test_tambah_data_master_green_bean_baru_dengan_harga_beli_awal(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. PIC menambahkan data komoditas Green Bean baru melalui master API
        $response = $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => 'Arabica Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_biji', 'Arabica Ijen')
            ->assertJsonPath('data.asal_kopi', 'Bondowoso');

        $this->assertDatabaseHas('green_bean', [
            'nama_biji' => 'Arabica Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $greenBean = GreenBean::where('nama_biji', 'Arabica Ijen')->first();

        // 2. Input harga beli awal dan stok di gudang inventory (5 kg @ Rp 95.000 / kg = total Rp 475.000)
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

        // 3. Pastikan muncul di daftar persediaan master green bean
        $listRes = $this->actingAs($pic)->getJson('/internal-api/master/green-beans');
        $listRes->assertStatus(200)
            ->assertJsonFragment([
                'nama_biji' => 'Arabica Ijen',
                'asal_kopi' => 'Bondowoso',
                'stok_gram' => 5000,
            ]);
    }

    /**
     * TC-GBMST-002: Validasi Nama Biji Kosong
     */
    public function test_validasi_nama_biji_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->postJson('/internal-api/master/green-beans', [
            'nama_biji' => '',
            'asal_kopi' => 'Bondowoso',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('nama_biji');

        $this->assertDatabaseMissing('green_bean', [
            'asal_kopi' => 'Bondowoso',
        ]);
    }

    /**
     * TC-GBMST-003: Memperbarui (Update) harga atau nama data Green Bean
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

        // 1. Update nama komoditas menjadi Arabica Ijen Premium
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

        // 2. Update harga beli stok menjadi Rp 85.000 / kg (total Rp 425.000 untuk 5 kg)
        $stockUpdateRes = $this->actingAs($pic)->put("/inventory/green-bean/{$stock->id}", [
            'stok_gram' => 5000,
            'tanggal_beli' => now()->format('d/m/Y'),
            'total_harga' => 425000,
        ]);

        $stockUpdateRes->assertRedirect(route('inventory.index', ['tab' => 'greenbean']));

        $this->assertDatabaseHas('green_bean_stock', [
            'id' => $stock->id,
            'harga_beli' => 85000,
            'total_harga' => 425000,
        ]);
    }

    /**
     * TC-GBMST-004: Indikator visual peringatan stok rendah (< 10 Kg) pada tabel Green Bean
     */
    public function test_indikator_visual_peringatan_stok_rendah_pada_tabel_green_bean(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // Green Bean dengan stok rendah (5 kg = 5000g < 10 kg)
        $lowGb = GreenBean::create(['nama_biji' => 'GB Rendah', 'asal_kopi' => 'Bondowoso']);
        GreenBeanStock::create([
            'green_bean_id' => $lowGb->id,
            'stok_gram' => 5000,
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Green Bean dengan stok aman (15 kg = 15000g >= 10 kg)
        $safeGb = GreenBean::create(['nama_biji' => 'GB Aman', 'asal_kopi' => 'Ijen']);
        GreenBeanStock::create([
            'green_bean_id' => $safeGb->id,
            'stok_gram' => 15000,
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Ambil data via API dan pastikan kalkulasi stok < 10 Kg tepat
        $apiRes = $this->actingAs($pic)->getJson('/internal-api/master/green-beans');
        $apiRes->assertStatus(200);

        $data = collect($apiRes->json());
        $lowItem = $data->firstWhere('id', $lowGb->id);
        $safeItem = $data->firstWhere('id', $safeGb->id);

        $this->assertLessThan(10, $lowItem['stok_gram'] / 1000);
        $this->assertGreaterThanOrEqual(10, $safeItem['stok_gram'] / 1000);

        // Pastikan di halaman master view terdapat styling indikator teks merah (:class text-red-500) saat stok < 10 Kg
        $viewRes = $this->actingAs($pic)->get('/master');
        $viewRes->assertStatus(200);
        $viewRes->assertSee("(item.stok_gram/1000) < 10 ? 'text-red-500' : 'text-primary'", false);
    }

    /**
     * TC-GBMST-005: Menghapus (Delete) data Master yang belum memiliki relasi
     */
    public function test_menghapus_delete_data_master_yang_belum_memiliki_relasi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // Data dummy tanpa relasi
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Dummy Test',
            'asal_kopi' => 'Lokasi Test',
        ]);

        $response = $this->actingAs($pic)->deleteJson("/internal-api/master/green-beans/{$greenBean->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Data Green Bean berhasil dihapus']);

        $this->assertDatabaseMissing('green_bean', [
            'id' => $greenBean->id,
        ]);

        // Negative check: Data yang sudah memiliki relasi roasting tidak boleh dihapus
        $greenBeanWithRelation = GreenBean::create([
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
            'green_bean_id' => $greenBeanWithRelation->id,
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

        $deleteNegativeRes = $this->actingAs($pic)->deleteJson("/internal-api/master/green-beans/{$greenBeanWithRelation->id}");
        $deleteNegativeRes->assertStatus(400);

        $this->assertDatabaseHas('green_bean', [
            'id' => $greenBeanWithRelation->id,
        ]);
    }
}
