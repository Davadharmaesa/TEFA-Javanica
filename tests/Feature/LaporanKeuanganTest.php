<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PesananKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKeuanganTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji apakah PIC dapat mengakses halaman laporan keuangan dengan filter periode.
     */
    public function test_pic_can_access_financial_reports_with_date_filter(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $roastedBean = \App\Models\RoastedBean::create([
            'nama_kopi' => 'Robusta Tubruk',
            'bentuk_akhir' => 'Bubuk',
            'total_stok' => 1000,
        ]);

        // Buat data transaksi contoh untuk diuji pada rentang tanggal
        PesananKeluar::create([
            'user_id' => $user->id,
            'roasted_bean_id' => $roastedBean->id,
            'nama_pesanan' => 'Kopi Robusta Tubruk',
            'tipe_pesanan' => 'internal',
            'jumlah_gram_pesanan' => 1000,
            'harga_jual_pesanan' => 250000,
            'total_pembayaran' => 250000,
            'nominal_dp' => 0,
            'sisa_pembayaran' => 0,
            'status_pembayaran' => 'lunas',
            'status_pengiriman' => 'Selesai',
            'status_pengambilan' => 'Sudah Diambil',
            'tanggal_pesanan' => '2026-10-10',
        ]);

        $response = $this->actingAs($user)->get('/reports?start_date=2026-10-01&end_date=2026-10-31');

        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan');
        $response->assertSee('250.000'); // Memastikan angka transaksi muncul di laporan
    }

    /**
     * Uji apakah sistem berhasil memproses ekspor laporan keuangan ke format Excel.
     */
    public function test_pic_can_export_financial_reports_to_excel(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($user)->get('/reports/export?start_date=2026-10-01&end_date=2026-10-31');

        // Memastikan request ekspor berhasil mengembalikan file / download response
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}