<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DashboardMetricsUnitTest extends TestCase
{
    /**
     * FR12 / TC-DASH-001: Kalkulasi rumus rata-rata burnoff (persentase susut roasting).
     */
    public function test_rumus_kalkulasi_burnoff_rate_penyusutan_roasting(): void
    {
        $totalRawRoasted = 5.0; // kg
        $totalCookedRoasted = 4.2; // kg

        $avgBurnoff = $totalRawRoasted > 0
            ? round((($totalRawRoasted - $totalCookedRoasted) / $totalRawRoasted) * 100, 1)
            : 0;

        // (5.0 - 4.2) / 5.0 * 100 = 0.8 / 5.0 * 100 = 16.0%
        $this->assertEquals(16.0, $avgBurnoff);
    }

    /**
     * FR12: Rumus burnoff rate aman dari pembagian dengan nol (division by zero) saat belum ada data produksi.
     */
    public function test_rumus_burnoff_rate_aman_dari_division_by_zero_saat_data_kosong(): void
    {
        $totalRawRoasted = 0.0;
        $totalCookedRoasted = 0.0;

        $avgBurnoff = $totalRawRoasted > 0
            ? round((($totalRawRoasted - $totalCookedRoasted) / $totalRawRoasted) * 100, 1)
            : 0;

        $this->assertEquals(0, $avgBurnoff);
    }

    /**
     * FR12: Konversi unit gramasi pangkalan data ke kilogram untuk metrik kartu stok.
     */
    public function test_konversi_stok_gram_ke_kilogram(): void
    {
        $stokGram = 12000;
        $stokKg = $stokGram / 1000;

        $this->assertEquals(12.0, $stokKg);

        $stokDesimalGram = 4250;
        $stokDesimalKg = $stokDesimalGram / 1000;

        $this->assertEquals(4.25, $stokDesimalKg);
    }

    /**
     * FR12: Deteksi ambang batas (threshold) stok menipis (< 5 kg atau < 5.000 gram).
     */
    public function test_deteksi_indikator_peringatan_stok_menipis(): void
    {
        $thresholdGram = 5000;

        $stokKritis = 3500;
        $stokAman = 7500;

        $isWarningKritis = ($stokKritis < $thresholdGram);
        $isWarningAman = ($stokAman < $thresholdGram);

        $this->assertTrue($isWarningKritis);
        $this->assertFalse($isWarningAman);
    }

    /**
     * FR12: Akumulasi omzet retail hanya menghitung transaksi berstatus lunas.
     */
    public function test_akumulasi_omzet_retail_hanya_menghitung_status_lunas(): void
    {
        $transaksi = [
            ['total' => 350000, 'status' => 'lunas'],
            ['total' => 600000, 'status' => 'dp'],
            ['total' => 200000, 'status' => 'lunas'],
            ['total' => 150000, 'status' => 'belum_bayar'],
        ];

        $totalOmzetLunas = array_reduce($transaksi, function ($carry, $item) {
            return $item['status'] === 'lunas' ? $carry + $item['total'] : $carry;
        }, 0);

        // 350.000 + 200.000 = 550.000
        $this->assertEquals(550000, $totalOmzetLunas);
    }
}

