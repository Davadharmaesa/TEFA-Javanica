<?php

namespace Tests\Feature;

use App\Models\FinishedGood;
use App\Models\GreenBean;
use App\Models\HppCalculation;
use App\Models\Packaging;
use App\Models\PesananKeluar;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreOrderSalesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-SLS-001: Pencatatan pre-order berhasil dengan jumlah pesanan valid
     */
    public function test_pencatatan_pre_order_berhasil_dengan_jumlah_pesanan_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Aceh Gayo',
            'asal_kopi' => 'Aceh',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica 250gr',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 5000,
            'satuan_dasar' => 'gram',
        ]);

        // Batch 1 (FIFO batch lama): 2.500 g
        $batchOld = RoastingBatch::create([
            'kode_batch' => 'BATCH-SLS-001A',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 3.0,
            'berat_mentah_sortasi' => 2.9,
            'berat_matang_kotor' => 2.6,
            'berat_matang_bersih' => 2.5,
            'sisa_stok' => 2500,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => '2026-05-01',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 250000,
        ]);

        // Batch 2 (Batch baru): 2.500 g
        $batchNew = RoastingBatch::create([
            'kode_batch' => 'BATCH-SLS-001B',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 3.0,
            'berat_mentah_sortasi' => 2.9,
            'berat_matang_kotor' => 2.6,
            'berat_matang_bersih' => 2.5,
            'sisa_stok' => 2500,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => '2026-05-10',
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 250000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Standing Pouch 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3500,
            'stok_pcs' => 100,
        ]);

        $finishedGood = FinishedGood::create([
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 20,
        ]);

        // Tetapkan kalkulasi HPP dengan harga jual final Rp 25.000 / pcs
        HppCalculation::create([
            'roasting_batch_id' => $batchOld->id,
            'packaging_id' => $packaging->id,
            'hasil_kemasan_pcs' => 10,
            'biaya_bahan_baku' => 100000,
            'biaya_listrik' => 10000,
            'biaya_penyusutan' => 5000,
            'biaya_gaji' => 15000,
            'biaya_kemasan' => 35000,
            'total_hpp' => 165000,
            'harga_jual_final' => 25000,
        ]);

        // Transaksi Pre-Order: Nama: Budi, Produk: Arabica 250gr, Qty: 15 pcs (3.750 g), Lunas
        $response = $this->actingAs($pic)->post('/sales/store', [
            'customer_name' => 'Budi',
            'roasted_bean_id' => $roastedBean->id,
            'finished_good_id' => "{$roastedBean->id}-250",
            'jumlah_pcs' => 15,
            'jumlah_gram_pesanan' => 3750,
            'harga_jual_pesanan' => 375000,
            'status_pengiriman' => 'Diproses',
            'metode_pembayaran' => 'lunas',
            'tipe_pesanan' => 'eksternal',
        ]);

        $response->assertRedirect(route('sales.index', ['tab' => 'external']));

        // Verifikasi pesanan tersimpan di database
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Budi',
            'roasted_bean_id' => $roastedBean->id,
            'jumlah_pcs' => 15,
            'jumlah_gram_pesanan' => 3750,
            'total_pembayaran' => 375000,
            'status_pembayaran' => 'lunas',
        ]);

        // Verifikasi pengurangan FIFO pada batch:
        // Batch lama (2.500g) harus habis (0 kg)
        $batchOld->refresh();
        $this->assertEquals(0, (float) $batchOld->berat_matang_bersih);

        // Batch baru berkurang 1.250g (sisa 1.250g / 1.25 kg)
        $batchNew->refresh();
        $this->assertEquals(1.25, (float) $batchNew->berat_matang_bersih);

        // Verifikasi stok fisik finished goods berkurang (20 - 15 = 5 pcs)
        $finishedGood->refresh();
        $this->assertEquals(5, $finishedGood->stok_pcs);
    }

    /**
     * TC-SLS-002: Sistem menolak submit saat jumlah pesanan bernilai minus atau 0
     */
    public function test_sistem_menolak_submit_saat_jumlah_pesanan_bernilai_minus_atau_nol(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta Dampit',
            'asal_kopi' => 'Malang',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Robusta Dampit',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 2000,
            'satuan_dasar' => 'gram',
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2000,
            'stok_pcs' => 50,
        ]);

        FinishedGood::create([
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 10,
        ]);

        // Submit dengan Qty minus (-5 pcs)
        $response = $this->actingAs($pic)->post('/sales/store', [
            'customer_name' => 'Doni',
            'roasted_bean_id' => $roastedBean->id,
            'finished_good_id' => "{$roastedBean->id}-200",
            'jumlah_pcs' => -5,
            'jumlah_gram_pesanan' => -1000,
            'harga_jual_pesanan' => 100000,
            'status_pengiriman' => 'Diproses',
            'metode_pembayaran' => 'lunas',
        ]);

        $response->assertSessionHasErrors(['jumlah_pcs', 'jumlah_gram_pesanan']);

        // Pastikan tidak ada data yang tersimpan
        $this->assertDatabaseMissing('pesanan_keluar', [
            'nama_pesanan' => 'Doni',
        ]);
    }

    /**
     * TC-SLS-003: Pengujian batas bawah kuantitas pesanan (0 vs 1)
     */
    public function test_pengujian_batas_bawah_kuantitas_pesanan_nol_vs_satu(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Toraja',
            'asal_kopi' => 'Sulawesi',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Toraja Kalosi',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-SLS-003',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
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
            'biaya_bahan_baku' => 100000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 2500,
            'stok_pcs' => 10,
        ]);

        FinishedGood::create([
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 10,
        ]);

        // Uji A: Qty = 0 (harus ditolak validasi min:1)
        $responseA = $this->actingAs($pic)->post('/sales/store', [
            'customer_name' => 'Siti',
            'roasted_bean_id' => $roastedBean->id,
            'finished_good_id' => "{$roastedBean->id}-250",
            'jumlah_pcs' => 0,
            'jumlah_gram_pesanan' => 0,
            'harga_jual_pesanan' => 0,
            'status_pengiriman' => 'Diproses',
            'metode_pembayaran' => 'lunas',
        ]);

        $responseA->assertSessionHasErrors(['jumlah_pcs', 'jumlah_gram_pesanan']);

        // Uji B: Qty = 1 (berhasil diproses dan memotong stok FIFO)
        $responseB = $this->actingAs($pic)->post('/sales/store', [
            'customer_name' => 'Siti Sukses',
            'roasted_bean_id' => $roastedBean->id,
            'finished_good_id' => "{$roastedBean->id}-250",
            'jumlah_pcs' => 1,
            'jumlah_gram_pesanan' => 250,
            'harga_jual_pesanan' => 35000,
            'status_pengiriman' => 'Diproses',
            'metode_pembayaran' => 'lunas',
        ]);

        $responseB->assertRedirect(route('sales.index', ['tab' => 'external']));

        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Siti Sukses',
            'jumlah_pcs' => 1,
            'jumlah_gram_pesanan' => 250,
        ]);
    }

    /**
     * TC-SLS-004: Notifikasi peringatan jual rugi saat harga jual di bawah HPP
     */
    public function test_notifikasi_peringatan_jual_rugi_saat_harga_jual_di_bawah_hpp(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Java',
            'asal_kopi' => 'Jawa Barat',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Java Premium',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 2000,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-SLS-004',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 2.5,
            'berat_mentah_sortasi' => 2.4,
            'berat_matang_kotor' => 2.1,
            'berat_matang_bersih' => 2.0,
            'sisa_stok' => 2000,
            'persentase_susut' => 20.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 200000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3000,
            'stok_pcs' => 50,
        ]);

        FinishedGood::create([
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 8,
        ]);

        // Simpan data HPP dengan HPP per pcs = Rp 50.000 (total_hpp 400.000 / 8 pcs)
        HppCalculation::create([
            'roasting_batch_id' => $batch->id,
            'packaging_id' => $packaging->id,
            'hasil_kemasan_pcs' => 8,
            'biaya_bahan_baku' => 200000,
            'biaya_listrik' => 15000,
            'biaya_penyusutan' => 10000,
            'biaya_gaji' => 25000,
            'biaya_kemasan' => 150000,
            'total_hpp' => 400000,
            'harga_jual_final' => 60000,
        ]);

        // Akses halaman pembuatan penjualan (/sales/create)
        $response = $this->actingAs($pic)->get('/sales/create');

        $response->assertStatus(200);

        // Verifikasi bahwa antarmuka form memuat indikator peringatan jual rugi
        $response->assertSee('Peringatan Jual Rugi: Total HPP/Modal adalah', false);
        $response->assertSee('PERINGATAN KERUGIAN!', false);
        $response->assertSee('Apakah Anda YAKIN ingin tetap memproses transaksi ini dan menjual rugi?', false);

        // Verifikasi data finished goods yang dikirim ke view memiliki hpp_per_pcs Rp 50.000
        $finishedGoods = $response->viewData('finishedGoods');
        $item = $finishedGoods->firstWhere('roasted_bean_id', $roastedBean->id);
        $this->assertNotNull($item);
        $this->assertEquals(50000, $item['hpp_per_pcs']);
    }

    /**
     * TC-SLS-005: Sinkronisasi pemotongan fisik stok barang jadi
     */
    public function test_sinkronisasi_pemotongan_fisik_stok_barang_jadi(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Sindoro',
            'asal_kopi' => 'Jawa Tengah',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Kopi Arabica 250g',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 5000,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-SLS-005',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 6.0,
            'berat_mentah_sortasi' => 5.8,
            'berat_matang_kotor' => 5.2,
            'berat_matang_bersih' => 5.0,
            'sisa_stok' => 5000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 3000,
            'stok_pcs' => 50,
        ]);

        // Precondition: Terdapat stok Kopi Arabica 250g sebanyak 20 pcs
        $finishedGood = FinishedGood::create([
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'stok_pcs' => 20,
        ]);

        // Input penjualan sebanyak 5 pcs (1.250 g)
        $response = $this->actingAs($pic)->post('/sales/store', [
            'customer_name' => 'Ahmad',
            'roasted_bean_id' => $roastedBean->id,
            'finished_good_id' => "{$roastedBean->id}-250",
            'jumlah_pcs' => 5,
            'jumlah_gram_pesanan' => 1250,
            'harga_jual_pesanan' => 150000,
            'status_pengiriman' => 'Diproses',
            'metode_pembayaran' => 'lunas',
        ]);

        $response->assertRedirect(route('sales.index', ['tab' => 'external']));

        // Verifikasi stok fisik Finished Good otomatis berkurang dari 20 menjadi 15 pcs
        $finishedGood->refresh();
        $this->assertEquals(15, $finishedGood->stok_pcs);

        $this->assertDatabaseHas('finished_goods', [
            'id' => $finishedGood->id,
            'stok_pcs' => 15,
        ]);
    }
}
