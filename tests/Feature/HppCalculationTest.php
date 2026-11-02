<?php

namespace Tests\Feature;

use App\Models\Biaya;
use App\Models\GreenBean;
use App\Models\HppCalculation;
use App\Models\Packaging;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HppCalculationTest extends TestCase
{
    use RefreshDatabase;

    private function createStandardHppSetup(): array
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Ijen',
            'asal_kopi' => 'Bondowoso',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Ijen',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 7700,
        ]);

        // Batch BATCH-001 dengan berat matang bersih 7.70 Kg (7.700 Gram)
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180, // 180 detik = 3 menit
            'durasi_pre_heating' => 5,  // 5 menit
            'berat_mentah_gudang' => 10.00,
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 7.70, // 7.70 Kg = 7700 Gram
            'sisa_stok' => 7700,
            'persentase_susut' => 16.33,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1200000, // Rp 1.200.000
        ]);

        // Biaya komponen operasional
        Biaya::create(['nama' => 'Listrik Mesin', 'kategori' => 'Overhead', 'satuan' => 'Kwh', 'harga' => 1500]);
        Biaya::create(['nama' => 'Penyusutan Mesin', 'kategori' => 'Overhead', 'satuan' => 'Batch', 'harga' => 3500]);
        Biaya::create(['nama' => 'Upah Roaster', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Batch', 'harga' => 15000]);

        // Kemasan: Pouch Standard 100g, harga Rp 1.500/pcs
        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Standard 100g',
            'kapasitas_gram' => 100,
            'satuan' => 'pcs',
            'harga_satuan' => 1500,
            'stok_pcs' => 100,
        ]);

        return compact('pic', 'greenBean', 'roastedBean', 'batch', 'pouch');
    }

    /**
     * TC-HPP-001: Kalkulasi total HPP batch berbasis biaya bahan, operasional, upah
     */
    public function test_kalkulasi_total_hpp_batch_berbasis_biaya_bahan_operasional_upah(): void
    {
        $setup = $this->createStandardHppSetup();

        $response = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 77,
            'pouch_id' => $setup['pouch']->id,
        ]);

        $response->assertStatus(200);

        $data = $response->json();
        // Validasi 5 komponen biaya
        $this->assertEquals(1200000, $data['biaya_bahan_baku']);
        $this->assertEquals(1375, $data['biaya_listrik']);
        $this->assertEquals(3500, $data['biaya_penyusutan']);
        $this->assertEquals(15000, $data['biaya_gaji']);
        $this->assertEquals(115500, $data['biaya_kemasan']); // 77 pcs * Rp 1.500

        // Total akumulasi 5 variabel biaya: 1.200.000 + 1.375 + 3.500 + 15.000 + 115.500 = 1.335.375
        $this->assertEquals(1335375, $data['total_hpp']);
    }

    /**
     * TC-HPP-002: Perhitungan HPP per pcs berdasarkan jumlah kemasan jadi
     */
    public function test_perhitungan_hpp_per_pcs_berdasarkan_jumlah_kemasan_jadi(): void
    {
        $setup = $this->createStandardHppSetup();

        $response = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 77,
            'pouch_id' => $setup['pouch']->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $totalHpp = $data['total_hpp']; // 1.335.375
        $outputPcs = 77;
        $hppPerPcs = $data['hpp_per_pcs']; // 1.335.375 / 77 = 17342.5324...

        $this->assertEquals(1335375, $totalHpp);
        // Hasil pembagian pembulatan terdekat adalah Rp 17.343
        $this->assertEquals(17343, round($hppPerPcs));
    }

    /**
     * TC-HPP-003: Validasi tombol Simpan HPP non-aktif (disabled) saat parameter form belum lengkap
     */
    public function test_validasi_tombol_simpan_hpp_non_aktif_disabled_saat_parameter_form_belum_lengkap(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. Verifikasi dari sisi UI bahwa tombol Simpan terkunci (:disabled="!canSubmit")
        $viewRes = $this->actingAs($pic)->get('/hpp-calculator');
        $viewRes->assertStatus(200);
        $viewRes->assertSee(':disabled="!canSubmit"', false);

        // 2. Verifikasi dari sisi backend bahwa submit form dengan parameter kosong ditolak
        $response = $this->actingAs($pic)->post('/hpp-calculator/store', [
            'roasting_batch_id' => null,
            'packaging_id' => null,
            'hasil_kemasan_pcs' => 0,
        ]);

        $response->assertSessionHasErrors(['roasting_batch_id', 'packaging_id', 'hasil_kemasan_pcs']);
        $this->assertEquals(0, HppCalculation::count());
    }

    /**
     * TC-HPP-004: Validasi batas maksimum Pcs (maxOutputPcs) berdasarkan kapasitas berat batch kopi dan ukuran kemasan
     */
    public function test_validasi_batas_maksimum_pcs_berdasarkan_kapasitas_berat_batch_kopi_dan_ukuran_kemasan(): void
    {
        $setup = $this->createStandardHppSetup();

        // Berat batch: 7.70 Kg (7.700 Gram), Ukuran pouch: 100 gram
        $response = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 10,
            'pouch_id' => $setup['pouch']->id,
        ]);

        $response->assertStatus(200);
        // 7.700g / 100g = 77 pcs
        $this->assertEquals(77, $response->json('max_output_pcs'));

        // Jika kemasan diganti ukuran 250 gram: 7.700g / 250g = 30 pcs
        $pouch250 = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 250g',
            'kapasitas_gram' => 250,
            'satuan' => 'pcs',
            'harga_satuan' => 2000,
            'stok_pcs' => 50,
        ]);

        $response250 = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 10,
            'pouch_id' => $pouch250->id,
        ]);

        $this->assertEquals(30, $response250->json('max_output_pcs'));
    }

    /**
     * TC-HPP-005: Rekomendasi otomatis Saran Harga Jual berbasis Margin 50%
     */
    public function test_rekomendasi_otomatis_saran_harga_jual_berbasis_margin_50_persen(): void
    {
        $setup = $this->createStandardHppSetup();

        $response = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 77,
            'pouch_id' => $setup['pouch']->id,
        ]);

        $response->assertStatus(200);
        $hppPerPcs = $response->json('hpp_per_pcs'); // 17342.532...

        // Formula tampilan pada Blade: Math.ceil((hpp_per_pcs * 1.5) / 500) * 500
        $saranHargaJual = ceil(($hppPerPcs * 1.5) / 500) * 500;
        $this->assertEquals(26500, (int) $saranHargaJual);

        // Verifikasi kartu rekomendasi ada pada view
        $viewRes = $this->actingAs($setup['pic'])->get('/hpp-calculator');
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Saran Harga Jual (Margin 50%)');
    }

    /**
     * TC-HPP-006: Akurasi Rumus Biaya Listrik Dinamis (KW per Jam)
     */
    public function test_akurasi_rumus_biaya_listrik_dinamis_kw_per_jam(): void
    {
        $setup = $this->createStandardHppSetup();

        // Durasi mesin: Pre-heat 5m, First crack 12m, Dev 180s (3m) => Total = 20m
        // Daya mesin sangrai = 0.75 kW (750 Watt)
        // Tarif listrik = Rp 1.500 / kWh
        // Perhitungan: (20 / 60) * 0.75 * 1.500 = 0.25 * 1.500 = 375
        // Ditambah biaya tetap pre-heating = 1.000 => Total Biaya Listrik = 1.375
        $response = $this->actingAs($setup['pic'])->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $setup['batch']->id]),
            'hasil_kemasan_pcs' => 77,
            'pouch_id' => $setup['pouch']->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1375, $response->json('biaya_listrik'));
    }

    /**
     * TC-HPP-007: Menampilkan riwayat kalkulasi HPP yang tersimpan pada tabel Riwayat HPP
     */
    public function test_menampilkan_riwayat_kalkulasi_hpp_yang_tersimpan_pada_tabel_riwayat_hpp(): void
    {
        $setup = $this->createStandardHppSetup();

        // Simpan data riwayat kalkulasi HPP
        $hpp = HppCalculation::create([
            'roasting_batch_id' => $setup['batch']->id,
            'packaging_id' => $setup['pouch']->id,
            'hasil_kemasan_pcs' => 80,
            'biaya_bahan_baku' => 1200000,
            'biaya_listrik' => 1375,
            'biaya_penyusutan' => 3500,
            'biaya_gaji' => 15000,
            'biaya_kemasan' => 120000,
            'total_hpp' => 1310146,
            'harga_jual_final' => 25000,
        ]);

        $response = $this->actingAs($setup['pic'])->get('/hpp-calculator');
        $response->assertStatus(200);

        // Verifikasi tabel riwayat HPP memuat kode batch, hasil pcs, dan status badge hijau Tersimpan
        $response->assertSee('Riwayat Kalkulasi HPP');
        $response->assertSee('BATCH-001');
        $response->assertSee('Tersimpan');

        $historyData = collect($response->viewData('hppHistory'));
        $item = $historyData->firstWhere('roasting_batch_id', $setup['batch']->id);
        $this->assertNotNull($item);
        $this->assertEquals(80, $item['hasil_kemasan_pcs']);
        $this->assertEquals(1310146, $item['total_hpp']);
    }

    /**
     * TC-UAT-002: PIC menghitung HPP, memvalidasi hasil perhitungan, dan menyimpan hasilnya
     */
    public function test_pic_menghitung_hpp_memvalidasi_hasil_perhitungan_dan_menyimpan_hasilnya(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $gb = GreenBean::create(['nama_biji' => 'GB Arabica Mandailing', 'asal_kopi' => 'Sumatera']);
        $rb = RoastedBean::create(['nama_kopi' => 'Roasted Mandailing', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 4000]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-UAT-002',
            'user_id' => $pic->id,
            'green_bean_id' => $gb->id,
            'roasted_bean_id' => $rb->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 10,
            'waktu_development' => 60,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 5.0,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0, // 4 Kg = 4000 Gram
            'sisa_stok' => 4000,
            'persentase_susut' => 20,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 600000,
        ]);

        Biaya::create(['nama' => 'Listrik Mesin', 'kategori' => 'Overhead', 'satuan' => 'Kwh', 'harga' => 1500]);
        Biaya::create(['nama' => 'Penyusutan Mesin', 'kategori' => 'Overhead', 'satuan' => 'Batch', 'harga' => 10000]);
        Biaya::create(['nama' => 'Upah Roaster', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Batch', 'harga' => 15000]);

        $pouch = Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch 100g',
            'kapasitas_gram' => 100,
            'satuan' => 'pcs',
            'harga_satuan' => 1000,
            'stok_pcs' => 100,
        ]);

        // 1. PIC melakukan kalkulasi preview untuk 40 pcs kemasan (40 * 100g = 4000g = 100% batch)
        $previewRes = $this->actingAs($pic)->postJson('/internal-api/hpp-calculator/preview', [
            'batch' => json_encode(['id' => $batch->id]),
            'hasil_kemasan_pcs' => 40,
            'pouch_id' => $pouch->id,
        ]);

        $previewRes->assertStatus(200);
        $previewData = $previewRes->json();
        $this->assertEquals(40, $previewData['max_output_pcs']);
        $this->assertGreaterThan(0, $previewData['total_hpp']);

        // 2. PIC memvalidasi dan menyimpan hasil kalkulasi HPP ke database
        $storeRes = $this->actingAs($pic)->post('/hpp-calculator/store', [
            'roasting_batch_id' => $batch->id,
            'packaging_id' => $pouch->id,
            'hasil_kemasan_pcs' => 40,
            'biaya_bahan_baku' => $previewData['biaya_bahan_baku'],
            'biaya_listrik' => $previewData['biaya_listrik'],
            'biaya_penyusutan' => $previewData['biaya_penyusutan'],
            'biaya_gaji' => $previewData['biaya_gaji'],
            'biaya_kemasan' => $previewData['biaya_kemasan'],
            'total_hpp' => $previewData['total_hpp'],
            'harga_jual_final' => 20000,
        ]);

        $storeRes->assertRedirect(route('hpp.index'));
        $storeRes->assertSessionHas('success');

        $this->assertDatabaseHas('hpp_calculations', [
            'roasting_batch_id' => $batch->id,
            'packaging_id' => $pouch->id,
            'hasil_kemasan_pcs' => 40,
            'harga_jual_final' => 20000,
        ]);

        // 3. Pastikan hasil perhitungan tampil di riwayat HPP
        $historyRes = $this->actingAs($pic)->get('/hpp-calculator');
        $historyRes->assertStatus(200);
        $historyData = collect($historyRes->viewData('hppHistory'));
        $saved = $historyData->firstWhere('roasting_batch_id', $batch->id);
        $this->assertNotNull($saved);
        $this->assertEquals(40, $saved['hasil_kemasan_pcs']);
    }
}
