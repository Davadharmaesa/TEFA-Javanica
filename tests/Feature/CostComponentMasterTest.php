<?php

namespace Tests\Feature;

use App\Models\Biaya;
use App\Models\GreenBean;
use App\Models\LogPekerja;
use App\Models\Packaging;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostComponentMasterTest extends TestCase
{
    // Membersihkan database in-memory setiap kali sebuah test selesai (Test Isolation)
    use RefreshDatabase;

    /**
     * TC-COST-001: Memperbarui tarif dasar biaya yang sudah ada.
     */
    public function test_memperbarui_tarif_dasar_biaya_yang_sudah_ada(): void
    {
        // LANGKAH 1: Buat user PIC (karena rute ini diproteksi middleware role:PIC)
        $pic = User::factory()->create(['role' => 'PIC']);

        // LANGKAH 2: Buat data awal komponen biaya di database (Tarif awal: Rp 1.500)
        $biaya = Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        // LANGKAH 3: Simulasi PIC mengedit harga jadi Rp 2.000 via HTTP PUT ke API master biaya
        $response = $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biaya->id}", [
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 2000,
        ]);

        // LANGKAH 4: Pastikan response server 200 OK dan JSON mengembalikan harga 2000
        $response->assertStatus(200)
            ->assertJsonPath('data.harga', 2000);

        // LANGKAH 5: Pastikan ke database fisik bahwa harga di tabel 'biaya' sudah berubah jadi 2000
        $this->assertDatabaseHas('biaya', [
            'id' => $biaya->id,
            'harga' => 2000,
        ]);
    }

    /**
     * TC-COST-002: Tarif listrik master yang diperbarui dipakai oleh kalkulator HPP.
     */
    public function test_tarif_listrik_master_yang_diperbarui_dipakai_di_preview_hpp(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $biayaListrik = Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Integrasi Biaya',
            'asal_kopi' => 'Ijen',
        ]);
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'RB Integrasi Biaya',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4000,
        ]);
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-COST-INTEGRATION',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);
        $pouch = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch Integrasi 100g',
            'kapasitas_gram' => 100,
            'satuan' => 'pcs',
            'harga_satuan' => 1000,
            'stok_pcs' => 100,
        ]);
        $previewInput = [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 40,
            'pouch_id' => $pouch->id,
        ];

        $previewAwal = $this->actingAs($pic)
            ->postJson('/internal-api/hpp-calculator/preview', $previewInput)
            ->assertOk();
        $this->assertEquals(1375, $previewAwal->json('biaya_listrik'));

        $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biayaListrik->id}", [
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 2000,
        ])->assertOk();

        $previewBaru = $this->actingAs($pic)
            ->postJson('/internal-api/hpp-calculator/preview', $previewInput)
            ->assertOk();
        $this->assertEquals(1500, $previewBaru->json('biaya_listrik'));
        $this->assertNotEquals($previewAwal->json('biaya_listrik'), $previewBaru->json('biaya_listrik'));
    }

    /**
     * TC-WB-COST-001: Tolak tarif biaya negatif saat update.
     */
    public function test_update_biaya_ditolak_jika_harga_negatif(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $biaya = Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biaya->id}", [
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => -1000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('harga');

        $this->assertDatabaseHas('biaya', [
            'id' => $biaya->id,
            'harga' => 1500,
        ]);
    }

    /**
     * TC-COST-003: Tambah komponen biaya operasional baru.
     */
    public function test_tambah_komponen_biaya_operasional_baru(): void
    {
        // LANGKAH 1: Buat user PIC
        $pic = User::factory()->create(['role' => 'PIC']);

        // LANGKAH 2: PIC kirim request HTTP POST untuk menambah biaya baru 'Gas Roasting' (Rp 25.000)
        $response = $this->actingAs($pic)->postJson('/internal-api/master/biaya', [
            'nama' => 'Gas Roasting',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ]);

        // LANGKAH 3: Pastikan server merespons 200 OK dan membawa data yang diinput
        $response->assertStatus(200)
            ->assertJsonPath('data.nama', 'Gas Roasting')
            ->assertJsonPath('data.harga', 25000);

        // LANGKAH 4: Pastikan data baru masuk ke tabel database 'biaya'
        $this->assertDatabaseHas('biaya', [
            'nama' => 'Gas Roasting',
            'harga' => 25000,
        ]);

        // LANGKAH 5: Pastikan data baru langsung muncul saat mengambil daftar seluruh master biaya
        $listRes = $this->actingAs($pic)->getJson('/internal-api/master/biaya');
        $listRes->assertStatus(200)
            ->assertJsonFragment([
                'nama' => 'Gas Roasting',
                'harga' => 25000,
            ]);
    }

    /**
     * TC-WB-COST-002: Perbarui tarif biaya dengan data valid.
     */
    public function test_perbarui_tarif_biaya_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $biaya = Biaya::create([
            'nama' => 'Listrik Mesin Whitebox',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);

        $this->actingAs($pic)->putJson("/internal-api/master/biaya/{$biaya->id}", [
            'nama' => 'Listrik Mesin Whitebox',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 2000,
        ])->assertOk()
            ->assertJsonPath('data.harga', 2000);

        $this->assertDatabaseHas('biaya', ['id' => $biaya->id, 'harga' => 2000]);
    }

    /**
     * TC-WB-COST-003: Tambah komponen biaya valid.
     */
    public function test_tambah_komponen_biaya_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/biaya', [
            'nama' => 'Gas Whitebox',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ])->assertOk()
            ->assertJsonPath('data.nama', 'Gas Whitebox')
            ->assertJsonPath('data.harga', 25000);

        $this->assertDatabaseHas('biaya', [
            'nama' => 'Gas Whitebox',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ]);
    }

    /**
     * TC-WB-COST-004: Tolak tarif biaya negatif saat tambah.
     */
    public function test_penolakan_input_tarif_biaya_negatif(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $this->actingAs($pic)->postJson('/internal-api/master/biaya', [
            'nama' => 'Gas Roasting',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => -1000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('harga');

        $this->assertDatabaseMissing('biaya', [
            'nama' => 'Gas Roasting',
        ]);
    }

    /**
     * TC-WB-COST-005: Tampilkan daftar seluruh komponen biaya.
     */
    public function test_get_biaya_menampilkan_seluruh_komponen_biaya(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $biaya = Biaya::create([
            'nama' => 'Gas Roasting',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ]);

        $this->actingAs($pic)->getJson('/internal-api/master/biaya')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $biaya->id,
                'nama' => 'Gas Roasting',
                'harga' => 25000,
            ]);
    }

    /**
     * TC-WB-COST-006: Hapus biaya yang tidak digunakan.
     */
    public function test_menghapus_data_biaya_master_yang_tidak_terhubung_dengan_transaksi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $biaya = Biaya::create([
            'nama' => 'Gas Roasting',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 25000,
        ]);

        $this->actingAs($pic)->deleteJson("/internal-api/master/biaya/{$biaya->id}")
            ->assertOk();

        $this->assertDatabaseMissing('biaya', ['id' => $biaya->id]);
    }

    /**
     * TC-WB-COST-007: Tolak hapus biaya yang digunakan log pekerja.
     */
    public function test_biaya_tidak_dapat_dihapus_jika_dipakai_log_pekerja(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Bali',
            'asal_kopi' => 'Kintamani',
        ]);
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'RB Arabika Bali',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4000,
        ]);
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-COST-DELETE',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
            'sisa_stok' => 4000,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);
        $biaya = Biaya::create([
            'nama' => 'Tenaga Kerja',
            'kategori' => 'Tenaga Kerja',
            'satuan' => 'Jam',
            'harga' => 50000,
        ]);
        $log = LogPekerja::create([
            'roasting_batch_id' => $batch->id,
            'nama_pekerja' => 'Tester Biaya',
            'biaya_id' => $biaya->id,
            'jumlah_kerja' => 1,
            'total_upah' => 50000,
        ]);

        $this->actingAs($pic)->deleteJson("/internal-api/master/biaya/{$biaya->id}")
            ->assertStatus(400);

        $this->assertDatabaseHas('biaya', ['id' => $biaya->id]);
        $this->assertDatabaseHas('log_pekerja', ['id' => $log->id, 'biaya_id' => $biaya->id]);
    }
}
