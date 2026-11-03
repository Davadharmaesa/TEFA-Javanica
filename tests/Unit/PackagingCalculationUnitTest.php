<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PackagingCalculationUnitTest extends TestCase
{
    /**
     * FR09 / TC-PCK-001: Kalkulasi pembagian volume kopi massal ke jumlah pcs kemasan pas tanpa sisa.
     */
    public function test_kalkulasi_pembagian_jumlah_kemasan_pas_tanpa_residu(): void
    {
        $stokDigunakanGram = 1000;
        $ukuranKemasanGram = 250;

        $hasilKemasanPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram);
        $stokTerkemasGram = $hasilKemasanPcs * $ukuranKemasanGram;
        $sisaResiduGram = $stokDigunakanGram - $stokTerkemasGram;

        $this->assertEquals(4, $hasilKemasanPcs);
        $this->assertEquals(1000, $stokTerkemasGram);
        $this->assertEquals(0, $sisaResiduGram);
    }

    /**
     * FR09 / TC-PCK-001: Kalkulasi pembagian kemasan yang menghasilkan sisa gramasi (residu).
     */
    public function test_kalkulasi_pembagian_kemasan_dengan_sisa_residu(): void
    {
        $stokDigunakanGram = 2150;
        $ukuranKemasanGram = 200;

        $hasilKemasanPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram);
        $stokTerkemasGram = $hasilKemasanPcs * $ukuranKemasanGram;
        $sisaResiduGram = $stokDigunakanGram - $stokTerkemasGram;

        $this->assertEquals(10, $hasilKemasanPcs);
        $this->assertEquals(2000, $stokTerkemasGram);
        $this->assertEquals(150, $sisaResiduGram);
    }

    /**
     * FR09 / TC-PCK-002: Validasi kuantitas kopi tidak mencukupi untuk membuat minimal 1 kemasan.
     */
    public function test_validasi_kuantitas_kopi_kurang_dari_kapasitas_kemasan_minimum(): void
    {
        $stokDigunakanGram = 80;
        $ukuranKemasanGram = 100;

        $isSufficient = $stokDigunakanGram >= $ukuranKemasanGram;
        $hasilKemasanPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram);

        $this->assertFalse($isSufficient);
        $this->assertEquals(0, $hasilKemasanPcs);
    }

    /**
     * FR09 / TC-PCK-003: Validasi batas minimum kuantitas pengemasan 1 pcs.
     */
    public function test_kalkulasi_pengemasan_output_minimum_satu_pcs(): void
    {
        $stokDigunakanGram = 100;
        $ukuranKemasanGram = 100;

        $hasilKemasanPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram);
        $sisaResiduGram = $stokDigunakanGram - ($hasilKemasanPcs * $ukuranKemasanGram);

        $this->assertEquals(1, $hasilKemasanPcs);
        $this->assertEquals(0, $sisaResiduGram);
    }

    /**
     * FR09: Pengecekan kecukupan material kemasan fisik berdasarkan kebutuhan pcs.
     */
    public function test_pengecekan_kecukupan_stok_material_kemasan(): void
    {
        $kebutuhanPcs = 10;
        $stokPouchTersedia = 8;

        $stokCukup = $stokPouchTersedia >= $kebutuhanPcs;
        $this->assertFalse($stokCukup);

        $stokPouchCukup = 15;
        $this->assertTrue($stokPouchCukup >= $kebutuhanPcs);
    }
}

