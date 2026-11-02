<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\Packaging;
use App\Models\PesananKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajemenDpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji pencatatan pre-order dengan metode pembayaran DP dan sisa piutang tercatat.
     */
    public function test_can_record_pesanan_with_dp_and_remaining_receivable(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Ijen', 'asal_kopi' => 'Banyuwangi']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Arabika Ijen Pouch', 'bentuk_akhir' => 'Kemasan', 'total_stok' => 5000]);

        $response = $this->actingAs($user)->post('/sales/store', [
            'customer_name' => 'Bapak Budi (Dosen)',
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

        $response->assertRedirect('/sales?tab=internal');
        
        // Memastikan data tersimpan dengan sisa piutang (total - dp)
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Bapak Budi (Dosen)',
            'harga_jual_pesanan' => 1000000,
            'nominal_dp' => 300000,
            'sisa_pembayaran' => 700000,
            'status_pembayaran' => 'dp',
        ]);
    }

    /**
     * Uji validasi penolakan jika nominal DP melebihi total harga transaksi.
     */
    public function test_dp_cannot_exceed_total_price(): void
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
            'tanggal_pesanan' => now()->addDays(2)->toDateString(),
            'metode_pembayaran' => 'dp',
            'harga_jual_pesanan' => 500000,
            'nominal_dp' => 600000, // DP melebihi total transaksi
            'batas_waktu_pembayaran' => now()->addDays(4)->toDateString(),
        ]);

        // Harus session error atau validasi gagal pada nominal_dp
        $response->assertSessionHasErrors('nominal_dp');
    }

    /**
     * Uji pelunasan sisa piutang pada transaksi DP.
     */
    public function test_can_pay_remaining_receivable_to_complete_payment(): void
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

        // Lakukan pembayaran pelunasan sisa piutang (Rp 700.000)
        $response = $this->actingAs($user)->post("/sales/{$order->id}/payment", [
            'nominal' => 700000,
            'tanggal_bayar' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        // Memastikan status berubah jadi Lunas dan sisa piutang jadi 0
        $this->assertDatabaseHas('pesanan_keluar', [
            'id' => $order->id,
            'nominal_dp' => 1000000,
            'sisa_pembayaran' => 0,
            'status_pembayaran' => 'lunas',
        ]);
    }
}
