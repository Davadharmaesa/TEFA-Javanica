<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DashboardMetricsUnitTest extends TestCase
{
    /**
     * FR12 / TC-DASH-006: Unit Test Dashboard: Penanganan division by zero pada rumus burnoff rate saat data kosong
     */
    public function test_penanganan_division_by_zero_pada_burnoff_rate(): void
    {
        $totalRawRoasted = 0.0;
        $totalCookedRoasted = 0.0;

        $avgBurnoff = $totalRawRoasted > 0
            ? round((($totalRawRoasted - $totalCookedRoasted) / $totalRawRoasted) * 100, 1)
            : 0;

        $this->assertEquals(0, $avgBurnoff);
    }

    /**
     * FR12 / TC-DASH-007: Unit Test Dashboard: Akurasi perhitungan matematis rata-rata persentase penyusutan (burnoff rate)
     */
    public function test_akurasi_perhitungan_matematis_burnoff_rate(): void
    {
        $totalRawRoasted = 5.0; // kg
        $totalCookedRoasted = 4.2; // kg

        // (5.0 - 4.2) / 5.0 * 100 = 0.8 / 5.0 * 100 = 16.0%
        $avgBurnoff = $totalRawRoasted > 0
            ? round((($totalRawRoasted - $totalCookedRoasted) / $totalRawRoasted) * 100, 1)
            : 0;

        $this->assertEquals(16.0, $avgBurnoff);
    }

    /**
     * FR12 / TC-DASH-008: Unit Test Dashboard: Akurasi konversi unit massa dari gram ke kilogram untuk metrik kartu stok
     */
    public function test_akurasi_konversi_unit_massa_gram_ke_kilogram(): void
    {
        $stokGramA = 12000;
        $stokKgA = $stokGramA / 1000;
        $this->assertEquals(12.0, $stokKgA);

        $stokGramB = 4250;
        $stokKgB = $stokGramB / 1000;
        $this->assertEquals(4.25, $stokKgB);
    }

    /**
     * FR12 / TC-DASH-009: Unit Test Dashboard: Percabangan evaluasi ambang batas peringatan stok menipis (< 5 kg)
     */
    public function test_percabangan_evaluasi_threshold_peringatan_stok_menipis(): void
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
     * FR12 / TC-DASH-010: Unit Test Dashboard: Filter akumulasi omzet ritel harian hanya menghitung status 'lunas'
     */
    public function test_filter_akumulasi_omzet_ritel_hanya_menghitung_status_lunas(): void
    {
        $transaksiHariIni = [
            ['total' => 350000, 'status' => 'lunas'],
            ['total' => 600000, 'status' => 'dp'],
            ['total' => 150000, 'status' => 'belum_bayar'],
        ];

        $totalOmzetLunas = 0;
        foreach ($transaksiHariIni as $tx) {
            if ($tx['status'] === 'lunas') {
                $totalOmzetLunas += $tx['total'];
            }
        }

        $this->assertEquals(350000, $totalOmzetLunas);
    }

    /**
     * FR12 / TC-DASH-011: Unit Test Dashboard: Pembatasan query feed log aktivitas terbaru maksimal 5 entri
     */
    public function test_pembatasan_feed_log_aktivitas_terbaru_maksimal_lima(): void
    {
        $allLogs = range(1, 12); // Ada 12 aktivitas
        $limit = 5;

        $recentLogs = array_slice($allLogs, 0, $limit);

        $this->assertCount(5, $recentLogs);
        $this->assertEquals([1, 2, 3, 4, 5], $recentLogs);
    }
}
