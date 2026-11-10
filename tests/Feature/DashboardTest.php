<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\Packaging;
use App\Models\PesananKeluar;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-DASH-001: Verifikasi sinkronisasi grafik stok, produksi aktif, dan omzet
     */
    public function test_verifikasi_sinkronisasi_grafik_stok_produksi_aktif_dan_omzet(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        // 1. Simulasikan stok bahan baku (Green Bean)
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Java Preanger',
            'asal_kopi' => 'Jawa Barat',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 12000, // 12 kg
            'harga_beli' => 100000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // 2. Simulasikan data produksi roasting aktif yang telah selesai
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Preanger Medium',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 4200,
            'satuan_dasar' => 'gram',
        ]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-DASH-001',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 90,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.3,
            'berat_matang_bersih' => 4.2,
            'sisa_stok' => 4200,
            'persentase_susut' => 16.0,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 500000,
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2000,
            'stok_pcs' => 100,
        ]);

        // 3. Simulasikan transaksi penjualan retail hari ini yang berstatus lunas
        PesananKeluar::create([
            'user_id' => $pic->id,
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'nama_pesanan' => 'Pelanggan Cafe A',
            'jumlah_gram_pesanan' => 1000,
            'jumlah_pcs' => 5,
            'harga_jual_pesanan' => 350000,
            'tanggal_pesanan' => now()->toDateString(),
            'total_pembayaran' => 350000,
            'status_pembayaran' => 'lunas',
            'status_pengiriman' => 'Selesai',
            'metode_pembayaran' => 'lunas',
            'created_at' => Carbon::now(),
        ]);

        // 4. Simulasikan antrean pesanan MTO aktif
        $antreanOrder = PesananKeluar::create([
            'user_id' => $pic->id,
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'nama_pesanan' => 'Pelanggan Pre-Order B',
            'jumlah_gram_pesanan' => 2000,
            'jumlah_pcs' => 10,
            'harga_jual_pesanan' => 600000,
            'tanggal_pesanan' => now()->addDay()->toDateString(),
            'total_pembayaran' => 600000,
            'status_pembayaran' => 'dp',
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'metode_pembayaran' => 'dp',
            'nominal_dp' => 300000,
            'sisa_pembayaran' => 300000,
            'created_at' => Carbon::now(),
        ]);

        // 5. Buka menu Dashboard utama sebagai PIC
        $response = $this->actingAs($pic)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');

        // Verifikasi sinkronisasi nilai agregat pada view dashboard
        $response->assertViewHas('total_stok_biji_mentah', 12);
        $response->assertViewHas('total_kopi_matang', 4.2);
        $response->assertViewHas('penjualan_retail', 350000);

        // Verifikasi antrean pesanan MTO tampil
        $antrean = $response->viewData('antrean_mto');
        $this->assertTrue($antrean->contains('id', $antreanOrder->id));

        // Verifikasi log aktivitas roasting terbaru sinkron dengan batch yang baru selesai
        $recentLogs = $response->viewData('recent_logs');
        $this->assertNotEmpty($recentLogs);
        $this->assertStringContainsString('GB Arabika Java Preanger', $recentLogs->first()['description']);

        // Verifikasi elemen teks ter-render di antarmuka HTML
        $response->assertSee('12', false);
        $response->assertSee('4.2', false);
        $response->assertSee('350.000', false);
    }

    /**
     * TC-DASH-002: Penanganan tampilan antarmuka saat pangkalan data masih kosong (Empty State)
     */
    public function test_penanganan_tampilan_antarmuka_saat_database_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('total_stok_biji_mentah', 0);
        $response->assertViewHas('total_kopi_matang', 0);
        $response->assertViewHas('penjualan_retail', 0);
        $response->assertViewHas('avg_burnoff', 0);
        $response->assertViewHas('has_warning', false);
    }

    /**
     * TC-DASH-003: Penampilan indikator visual badge peringatan saat stok bahan baku menipis (< 5 Kg)
     */
    public function test_penampilan_indikator_peringatan_saat_stok_menipis(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Robusta Menipis',
            'asal_kopi' => 'Dampit',
        ]);

        // Simulasikan stok hanya 3.500 gram (3.5 kg < 5 kg threshold)
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 3500,
            'harga_beli' => 80000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $response = $this->actingAs($pic)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('has_warning', true);

        $stokMenipis = $response->viewData('stok_menipis');
        $this->assertTrue($stokMenipis->contains('name', 'GB Robusta Menipis'));
    }

    /**
     * TC-DASH-004: Filter tabel antrean pesanan MTO hanya menampilkan pesanan berstatus aktif
     */
    public function test_filter_tabel_antrean_mto_hanya_menampilkan_status_aktif(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Arabica Test',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        $packaging = Packaging::create([
            'tipe' => 'Pouch',
            'nama_kemasan' => 'Pouch 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 2000,
            'stok_pcs' => 10,
        ]);

        // Order aktif (Menunggu Konfirmasi)
        $orderAktif = PesananKeluar::create([
            'user_id' => $pic->id,
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'nama_pesanan' => 'Pelanggan Aktif',
            'jumlah_gram_pesanan' => 500,
            'jumlah_pcs' => 2,
            'harga_jual_pesanan' => 60000,
            'total_pembayaran' => 60000,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pembayaran' => 'lunas',
            'tanggal_pesanan' => now()->toDateString(),
        ]);

        // Order selesai
        $orderSelesai = PesananKeluar::create([
            'user_id' => $pic->id,
            'roasted_bean_id' => $roastedBean->id,
            'packaging_id' => $packaging->id,
            'nama_pesanan' => 'Pelanggan Selesai',
            'jumlah_gram_pesanan' => 500,
            'jumlah_pcs' => 2,
            'harga_jual_pesanan' => 60000,
            'total_pembayaran' => 60000,
            'status_pengiriman' => 'Selesai',
            'status_pembayaran' => 'lunas',
            'tanggal_pesanan' => now()->toDateString(),
        ]);

        $response = $this->actingAs($pic)->get('/dashboard');

        $antrean = $response->viewData('antrean_mto');
        $this->assertTrue($antrean->contains('id', $orderAktif->id));
        $this->assertFalse($antrean->contains('id', $orderSelesai->id));
    }

    /**
     * TC-DASH-005: Sinkronisasi feed log aktivitas terbaru setelah ada proses roasting baru selesai
     */
    public function test_sinkronisasi_feed_log_aktivitas_terbaru(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Toraja Sapan',
            'asal_kopi' => 'Sulawesi',
        ]);

        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Toraja',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ]);

        RoastingBatch::create([
            'kode_batch' => 'BATCH-LOG-TEST',
            'user_id' => $pic->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Light to Medium',
            'durasi_roasting' => 11,
            'waktu_first_crack' => 8.0,
            'waktu_development' => 80,
            'durasi_pre_heating' => 5,
            'suhu' => 202,
            'berat_mentah_gudang' => 1.2,
            'berat_matang_bersih' => 1.0,
            'sisa_stok' => 1000,
            'persentase_susut' => 16.67,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 120000,
        ]);

        $response = $this->actingAs($pic)->get('/dashboard');

        $recentLogs = $response->viewData('recent_logs');
        $this->assertNotEmpty($recentLogs);
        $this->assertStringContainsString('GB Toraja Sapan', $recentLogs->first()['description']);
    }
}

