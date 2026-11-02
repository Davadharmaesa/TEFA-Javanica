<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
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

        $response = $this->actingAs($user)->postJson('/sales', [
            'customer_name' => 'Bapak Budi (Dosen)',
            'tipe_pesanan' => 'internal',
            'finished_good_id' => $roastedBean->id . '-200',
            'jumlah_pcs' => 10,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->addDays(3)->toDateString(),
            'metode_pembayaran' => 'belum_bayar', // DP / Belum Lunas
            'harga_jual_pesanan' => 1000000,
            'nominal_dp' => 300000,
            'batas_waktu_pembayaran' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect('/sales');
        
        // Memastikan data tersimpan dengan sisa piutang (total - dp)
        $this->assertDatabaseHas('pesanan_keluar', [
            'nama_pesanan' => 'Bapak Budi (Dosen)',
            'total_price' => 1000000,
            'paid_amount' => 300000,
            'remaining_amount' => 700000,
            'payment_status' => 'Belum Lunas (DP)',
        ]);
    }

    /**
     * Uji validasi penolakan jika nominal DP melebihi total harga transaksi.
     */
    public function test_dp_cannot_exceed_total_price(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 3000]);

        $response = $this->actingAs($user)->postJson('/sales', [
            'customer_name' => 'Cafe Senja',
            'tipe_pesanan' => 'eksternal',
            'finished_good_id' => $roastedBean->id . '-200',
            'jumlah_pcs' => 5,
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->addDays(2)->toDateString(),
            'metode_pembayaran' => 'belum_bayar',
            'harga_jual_pesanan' => 500000,
            'nominal_dp' => 600000, // DP melebihi total transaksi
            'batas_waktu_pembayaran' => now()->addDays(4)->toDateString(),
        ]);

        // Harus session error atau validasi gagal
        $response->assertSessionHasErrors();
    }

    /**
     * Uji pelunasan sisa piutang pada transaksi DP.
     */
    public function test_can_pay_remaining_receivable_to_complete_payment(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $order = PesananKeluar::create([
            'user_id' => $user->id,
            'nama_pesanan' => 'Kopi Mahasiswa',
            'tipe_pesanan' => 'internal',
            'total_price' => 1000000,
            'paid_amount' => 300000,
            'remaining_amount' => 700000,
            'payment_status' => 'Belum Lunas (DP)',
            'status_pengiriman' => 'Menunggu Konfirmasi',
            'status_pengambilan' => 'Belum Diambil',
            'tanggal_pesanan' => now()->toDateString(),
        ]);

        // Lakukan pembayaran pelunasan sisa piutang (Rp 700.000)
        $response = $this->actingAs($user)->postJson("/sales/{$order->id}/payment", [
            'nominal' => 700000,
            'tanggal_bayar' => now()->toDateString(),
        ]);

        $response->assertStatus(302);

        // Memastikan status berubah jadi Lunas dan sisa piutang jadi 0
        $this->assertDatabaseHas('pesanan_keluar', [
            'id' => $order->id,
            'paid_amount' => 1000000,
            'remaining_amount' => 0,
            'payment_status' => 'Lunas',
        ]);
    }
}