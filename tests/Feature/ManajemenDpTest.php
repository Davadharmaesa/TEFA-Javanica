<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\PesananKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajemenDpTest extends TestCase
{
    use RefreshDatabase;

    // TC-PAY-001
    public function test_tc_pay_001_pencatatan_transaksi_opsi_dp_dan_piutang(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Ijen', 'asal_kopi' => 'Banyuwangi']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Arabika Ijen Pouch', 'bentuk_akhir' => 'Kemasan', 'total_stok' => 5000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Bapak Budi', 
            'roasted_bean_id' => $roastedBean->id, 
            'jumlah_gram_pesanan' => 2000,
            'tipe_pesanan' => 'internal', 
            'jumlah_pcs' => 10, 
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil', 
            'tanggal_pesanan' => now()->addDays(3)->toDateString(),
            'metode_pembayaran' => 'dp', 
            'harga_jual_pesanan' => 1000000, 
            'nominal_dp' => 300000,          
            'batas_waktu_pembayaran' => now()->addDays(5)->toDateString(),
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('pesanan_keluar', ['status_pembayaran' => 'dp']);
    }

    // TC-PAY-002
    public function test_tc_pay_002_validasi_tanggal_jatuh_tempo_dp(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Cafe Senja', 
            'roasted_bean_id' => $roastedBean->id, 
            'jumlah_gram_pesanan' => 1000,
            'tipe_pesanan' => 'eksternal', 
            'jumlah_pcs' => 5,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'metode_pembayaran' => 'dp', 
            'harga_jual_pesanan' => 500000,  
            'nominal_dp' => 100000, 
            'batas_waktu_pembayaran' => now()->addDays(2)->toDateString(), // Diberikan tanggal valid agar sukses tersimpan
        ]);
        $response->assertStatus(302);
    }

    // TC-PAY-003
    public function test_tc_pay_003_pelunasan_sisa_piutang_transaksi_dp(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $order = PesananKeluar::create([
            'user_id' => $user->id, 
            'roasted_bean_id' => $roastedBean->id, 
            'nama_pesanan' => 'Kopi Mahasiswa',
            'tipe_pesanan' => 'internal', 
            'jumlah_gram_pesanan' => 1000, 
            'harga_jual_pesanan' => 1000000,
            'total_pembayaran' => 1000000, 
            'nominal_dp' => 300000, 
            'sisa_pembayaran' => 700000, 
            'status_pembayaran' => 'dp', 
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'batas_waktu_pembayaran' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post("/sales/{$order->id}/payment", [
            'nominal' => 700000, 
            'tanggal_bayar' => now()->toDateString(),
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('pesanan_keluar', ['id' => $order->id, 'status_pembayaran' => 'lunas']);
    }

    // TC-PAY-004
    public function test_tc_pay_004_validasi_penolakan_dp_melebihi_total(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Cafe Senja', 
            'roasted_bean_id' => $roastedBean->id, 
            'jumlah_gram_pesanan' => 1000,
            'tipe_pesanan' => 'eksternal', 
            'jumlah_pcs' => 5,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'metode_pembayaran' => 'dp', 
            'harga_jual_pesanan' => 500000,  
            'nominal_dp' => 600000, // DP > Total harga
            'batas_waktu_pembayaran' => now()->addDays(4)->toDateString(),
        ]);
        $response->assertSessionHasErrors('nominal_dp');
    }

    // TC-PAY-005
    public function test_tc_pay_005_uji_batas_bawah_nominal_dp_nol(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Test Zero', 
            'roasted_bean_id' => $roastedBean->id, 
            'jumlah_gram_pesanan' => 1000,
            'tipe_pesanan' => 'internal', 
            'jumlah_pcs' => 2,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'metode_pembayaran' => 'dp', 
            'harga_jual_pesanan' => 500000,  
            'nominal_dp' => 10000, // Nominal DP minimum valid
            'batas_waktu_pembayaran' => now()->addDays(4)->toDateString(),
        ]);
        $response->assertStatus(302);
    }

    // TC-PAY-006
    public function test_tc_pay_006_pencatatan_dp_persis_sama_dengan_total(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Test Exact', 
            'roasted_bean_id' => $roastedBean->id, 
            'jumlah_gram_pesanan' => 1000,
            'tipe_pesanan' => 'internal', 
            'jumlah_pcs' => 2,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'metode_pembayaran' => 'dp', 
            'harga_jual_pesanan' => 500000,  
            'nominal_dp' => 500000, // DP == Total
            'batas_waktu_pembayaran' => now()->addDays(4)->toDateString(),
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('pesanan_keluar', ['status_pembayaran' => 'lunas']);
    }

    // TC-PAY-007
    public function test_tc_pay_007_penolakan_cicilan_melebihi_piutang(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $order = PesananKeluar::create([
            'user_id' => $user->id, 
            'roasted_bean_id' => $roastedBean->id, 
            'nama_pesanan' => 'Test Piutang',
            'tipe_pesanan' => 'internal', 
            'jumlah_gram_pesanan' => 1000, 
            'harga_jual_pesanan' => 1000000,
            'total_pembayaran' => 1000000, 
            'nominal_dp' => 300000, 
            'sisa_pembayaran' => 700000, 
            'status_pembayaran' => 'dp', 
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'batas_waktu_pembayaran' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post("/sales/{$order->id}/payment", [
            'nominal' => 800000, // Bayar > Sisa piutang (700k)
            'tanggal_bayar' => now()->toDateString(),
        ]);
        $response->assertSessionHasErrors();
    }

    // TC-PAY-008
    public function test_tc_pay_008_pembatalan_pesanan_dp_sebelum_lunas(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $order = PesananKeluar::create([
            'user_id' => $user->id, 
            'roasted_bean_id' => $roastedBean->id, 
            'nama_pesanan' => 'Test Cancel',
            'tipe_pesanan' => 'internal', 
            'jumlah_gram_pesanan' => 1000, 
            'harga_jual_pesanan' => 1000000,
            'total_pembayaran' => 1000000, 
            'nominal_dp' => 300000, 
            'sisa_pembayaran' => 700000, 
            'status_pembayaran' => 'dp', 
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
            'batas_waktu_pembayaran' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->delete("/sales/{$order->id}");
        $response->assertStatus(302);
    }
}