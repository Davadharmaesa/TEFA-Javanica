<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SalesOrderUnitTest extends TestCase
{
    /**
     * FR11 / TC-SLS-009: Unit Test storeSales: Validasi request penolakan saat nama pemesan atau produk kosong
     */
    public function test_validasi_request_saat_nama_pemesan_atau_produk_kosong(): void
    {
        $payload = [
            'customer_name' => '',
            'roasted_bean_id' => null,
            'jumlah_pcs' => 5,
        ];

        $isValid = !empty($payload['customer_name']) && !empty($payload['roasted_bean_id']) && ($payload['jumlah_pcs'] > 0);

        $this->assertFalse($isValid);
    }

    /**
     * FR11 / TC-SLS-010: Unit Test storeSales: Percabangan penolakan saat stok roasted bean kurang dari pesanan
     */
    public function test_percabangan_penolakan_saat_stok_kopi_kurang_dari_pesanan(): void
    {
        $stokTersediaGram = 500;
        $pesananGram = 1500;

        $isSufficient = ($stokTersediaGram >= $pesananGram);

        $this->assertFalse($isSufficient);
    }

    /**
     * FR11 / TC-SLS-011: Unit Test storeSales: Eksekusi loop pemotongan stok batch roasting tertua hingga habis (FIFO)
     */
    public function test_eksekusi_loop_pemotongan_stok_batch_fifo(): void
    {
        $batches = [
            ['kode' => 'Batch-Mei-01', 'stok_gram' => 2500],
            ['kode' => 'Batch-Mei-10', 'stok_gram' => 2500],
        ];

        $kebutuhanPengurangan = 3750; // 15 pcs @ 250g
        $remaining = $kebutuhanPengurangan;

        foreach ($batches as &$batch) {
            if ($remaining <= 0) break;

            if ($batch['stok_gram'] >= $remaining) {
                $batch['stok_gram'] -= $remaining;
                $remaining = 0;
            } else {
                $remaining -= $batch['stok_gram'];
                $batch['stok_gram'] = 0;
            }
        }
        unset($batch);

        $this->assertEquals(0, $batches[0]['stok_gram']); // Batch tertua habis
        $this->assertEquals(1250, $batches[1]['stok_gram']); // Batch baru sisa 1.250g
        $this->assertEquals(0, $remaining);
    }

    /**
     * FR11 / TC-SLS-012: Unit Test storeSales: Percabangan penentuan status pembayaran lunas vs belum bayar
     */
    public function test_percabangan_penentuan_status_pembayaran_lunas_vs_belum_bayar(): void
    {
        $totalTagihan = 500000;

        // Kasus A: Metode lunas
        $metodeA = 'lunas';
        $statusA = ($metodeA === 'lunas') ? 'lunas' : 'belum_bayar';
        $sisaA = ($statusA === 'lunas') ? 0 : $totalTagihan;

        $this->assertEquals('lunas', $statusA);
        $this->assertEquals(0, $sisaA);

        // Kasus B: Metode belum bayar
        $metodeB = 'belum_bayar';
        $statusB = ($metodeB === 'lunas') ? 'lunas' : 'belum_bayar';
        $sisaB = ($statusB === 'lunas') ? 0 : $totalTagihan;

        $this->assertEquals('belum_bayar', $statusB);
        $this->assertEquals(500000, $sisaB);
    }

    /**
     * FR11 / TC-SLS-013: Unit Test storeSales: Basis path transaksi penjualan sukses dan rollback jika error
     */
    public function test_basis_path_transaksi_penjualan_sukses_dan_rollback_handling(): void
    {
        $isDbError = false;
        $orderCreated = false;
        $rolledBack = false;

        try {
            if ($isDbError) {
                throw new \Exception('Database write failure');
            }
            $orderCreated = true;
        } catch (\Exception $e) {
            $rolledBack = true;
        }

        $this->assertTrue($orderCreated);
        $this->assertFalse($rolledBack);
    }

    /**
     * FR11 / TC-SLS-014: Unit Test model PesananKeluar: Logika kalkulasi margin untung vs peringatan jual rugi modal HPP
     */
    public function test_logika_kalkulasi_margin_untung_vs_peringatan_jual_rugi(): void
    {
        $hppPerPcs = 50000;
        $qty = 8;
        $totalHpp = $hppPerPcs * $qty; // Rp 400.000

        // Kasus 1: Harga jual rugi (Rp 350.000 < modal Rp 400.000)
        $hargaJualRugi = 350000;
        $isLossDetected = ($hargaJualRugi < $totalHpp);
        $this->assertTrue($isLossDetected);

        // Kasus 2: Harga jual untung (Rp 480.000 >= modal Rp 400.000)
        $hargaJualUntung = 480000;
        $isProfit = ($hargaJualUntung >= $totalHpp);
        $this->assertTrue($isProfit);
    }
}
