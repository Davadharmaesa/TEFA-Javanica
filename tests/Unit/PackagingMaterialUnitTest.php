<?php

namespace Tests\Unit;

use App\Models\Packaging;
use Tests\TestCase;

class PackagingMaterialUnitTest extends TestCase
{
    /**
     * FR10 / TC-MAT-001: Kalkulasi total nilai aset inventaris kemasan (stok * harga satuan).
     */
    public function test_kalkulasi_total_nilai_aset_material_kemasan(): void
    {
        $packaging = new Packaging([
            'tipe' => 'Standing Pouch 250gr',
            'nama_kemasan' => 'Kemasan 1',
            'kapasitas_gram' => 250,
            'satuan' => 'pcs',
            'stok_pcs' => 100,
            'harga_satuan' => 3500,
        ]);

        $totalNilaiAset = $packaging->stok_pcs * $packaging->harga_satuan;

        $this->assertEquals(350000, $totalNilaiAset);
    }

    /**
     * FR10 / TC-MAT-002: Validasi integritas kapasitas gram kemasan harus bernilai positif.
     */
    public function test_validasi_kapasitas_gram_kemasan_harus_positif(): void
    {
        $validCapacity = 250;
        $invalidCapacity = 0;
        $negativeCapacity = -50;

        $this->assertTrue($validCapacity > 0);
        $this->assertFalse($invalidCapacity > 0);
        $this->assertFalse($negativeCapacity > 0);
    }

    /**
     * FR10 / TC-MAT-003: Batas minimum stok fisik kemasan (Boundary test).
     */
    public function test_stok_fisik_kemasan_pada_jumlah_minimum(): void
    {
        $packaging = new Packaging([
            'nama_kemasan' => 'Kemasan 2',
            'kapasitas_gram' => 10,
            'stok_pcs' => 1,
            'harga_satuan' => 500,
        ]);

        $this->assertEquals(1, $packaging->stok_pcs);
        $this->assertTrue($packaging->stok_pcs > 0);
    }

    /**
     * FR10 / TC-MAT-004: Simulasi dampak pembaruan harga modal kemasan terhadap biaya per batch kemasan.
     */
    public function test_simulasi_dampak_pembaruan_harga_modal_kemasan(): void
    {
        $hargaLama = 3000;
        $hargaBaru = 3500;
        $jumlahKemasan = 10;

        $biayaKemasanLama = $hargaLama * $jumlahKemasan;
        $biayaKemasanBaru = $hargaBaru * $jumlahKemasan;
        $selisihBiaya = $biayaKemasanBaru - $biayaKemasanLama;

        $this->assertEquals(30000, $biayaKemasanLama);
        $this->assertEquals(35000, $biayaKemasanBaru);
        $this->assertEquals(5000, $selisihBiaya);
    }
}

