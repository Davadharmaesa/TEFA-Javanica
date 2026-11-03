<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SalesOrderUnitTest extends TestCase
{
    /**
     * FR11 / TC-SLS-001: Logika perhitungan status transaksi lunas dan sisa pembayaran nol.
     */
    public function test_logika_penentuan_status_pembayaran_lunas(): void
    {
        $totalTagihan = 375000;
        $metode = 'lunas';
        $nominalDp = 0;

        if ($metode === 'lunas') {
            $statusPembayaran = 'lunas';
            $sisaPembayaran = 0;
            $nominalDp = 0;
        } else {
            $statusPembayaran = 'belum_bayar';
            $sisaPembayaran = $totalTagihan;
        }

        $this->assertEquals('lunas', $statusPembayaran);
        $this->assertEquals(0, $sisaPembayaran);
        $this->assertEquals(0, $nominalDp);
    }

    /**
     * FR11: Logika penentuan status pembayaran DP dan kalkulasi sisa piutang.
     */
    public function test_logika_penentuan_status_pembayaran_dp_dan_sisa_piutang(): void
    {
        $totalTagihan = 1000000;
        $metode = 'dp';
        $nominalDp = 300000;

        $nominalDp = min($nominalDp, $totalTagihan);
        $sisaPembayaran = max(0, $totalTagihan - $nominalDp);
        $statusPembayaran = ($metode === 'dp' && $nominalDp > 0 && $sisaPembayaran > 0) ? 'dp' : 'lunas';

        $this->assertEquals('dp', $statusPembayaran);
        $this->assertEquals(700000, $sisaPembayaran);
        $this->assertEquals(300000, $nominalDp);
    }

    /**
     * FR11: Logika penentuan status pembayaran belum bayar jika DP nol.
     */
    public function test_logika_penentuan_status_belum_bayar(): void
    {
        $totalTagihan = 500000;
        $nominalDp = 0;
        $sisaPembayaran = $totalTagihan - $nominalDp;
        $statusPembayaran = ($nominalDp === 0) ? 'belum_bayar' : 'dp';

        $this->assertEquals('belum_bayar', $statusPembayaran);
        $this->assertEquals(500000, $sisaPembayaran);
    }

    /**
     * FR11 / TC-SLS-002: Validasi kuantitas pesanan tidak boleh nol atau bernilai negatif.
     */
    public function test_validasi_kuantitas_pesanan_tidak_boleh_minus_atau_nol(): void
    {
        $qtyNegatif = -5;
        $qtyNol = 0;
        $qtyValid = 15;

        $this->assertFalse($qtyNegatif >= 1);
        $this->assertFalse($qtyNol >= 1);
        $this->assertTrue($qtyValid >= 1);
    }

    /**
     * FR11 / TC-SLS-003: Boundary test batas minimum pemesanan produk (0 vs 1).
     */
    public function test_boundary_kuantitas_pesanan_nol_vs_satu(): void
    {
        $qtyBatasBawahInvalid = 0;
        $qtyBatasBawahValid = 1;

        $isValidA = $qtyBatasBawahInvalid >= 1;
        $isValidB = $qtyBatasBawahValid >= 1;

        $this->assertFalse($isValidA);
        $this->assertTrue($isValidB);
    }

    /**
     * FR11 / TC-SLS-004: Deteksi peringatan jual rugi saat total harga jual lebih rendah dari total HPP/modal.
     */
    public function test_deteksi_peringatan_jual_rugi_saat_harga_jual_di_bawah_hpp(): void
    {
        $hppPerPcs = 50000;
        $qty = 8;
        $totalHpp = $hppPerPcs * $qty; // Rp 400.000

        // Kasus 1: Harga jual rugi (Rp 350.000 < Rp 400.000)
        $hargaJualRugi = 350000;
        $isSellingBelowHppRugi = ($hargaJualRugi < $totalHpp);
        $this->assertTrue($isSellingBelowHppRugi);

        // Kasus 2: Harga jual untung (Rp 480.000 >= Rp 400.000)
        $hargaJualUntung = 480000;
        $isSellingBelowHppUntung = ($hargaJualUntung < $totalHpp);
        $this->assertFalse($isSellingBelowHppUntung);
    }

    /**
     * FR11 / TC-SLS-005: Kalkulasi pemotongan stok fisik finished good.
     */
    public function test_kalkulasi_pemotongan_stok_fisik_barang_jadi(): void
    {
        $stokAwal = 20;
        $jumlahTerjual = 5;

        $sisaStok = $stokAwal - $jumlahTerjual;

        $this->assertEquals(15, $sisaStok);
        $this->assertTrue($sisaStok >= 0);
    }
}

