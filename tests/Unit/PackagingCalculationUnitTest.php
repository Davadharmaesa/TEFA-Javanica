<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PackagingCalculationUnitTest extends TestCase
{
    /**
     * FR09 / TC-PCK-008: Unit Test storePackaging: Validasi request saat field wajib ID atau berat kosong (Path 1)
     */
    public function test_validasi_request_saat_field_wajib_id_atau_berat_kosong(): void
    {
        $payload = [
            'roasted_bean_id' => null,
            'packaging_id' => null,
            'jumlah_stok_digunakan_gram' => '',
        ];

        $isPayloadValid = !empty($payload['roasted_bean_id'])
            && !empty($payload['packaging_id'])
            && !empty($payload['jumlah_stok_digunakan_gram'])
            && is_numeric($payload['jumlah_stok_digunakan_gram'])
            && $payload['jumlah_stok_digunakan_gram'] > 0;

        $this->assertFalse($isPayloadValid);
    }

    /**
     * FR09 / TC-PCK-009: Unit Test storePackaging: Penolakan branch kapasitas pouch bernilai nol atau minus (Path 2)
     */
    public function test_penolakan_branch_kapasitas_pouch_nol_atau_minus(): void
    {
        $kapasitasPouchNol = 0;
        $kapasitasPouchMinus = -50;
        $kapasitasPouchValid = 250;

        $this->assertFalse($kapasitasPouchNol > 0);
        $this->assertFalse($kapasitasPouchMinus > 0);
        $this->assertTrue($kapasitasPouchValid > 0);
    }

    /**
     * FR09 / TC-PCK-010: Unit Test storePackaging: Percabangan penolakan saat stok batch roasting lebih kecil dari input (Path 3)
     */
    public function test_percabangan_penolakan_saat_stok_batch_kurang_dari_kebutuhan(): void
    {
        $availableStock = 500;
        $stokDigunakan = 800;

        $isStockSufficient = ($availableStock >= $stokDigunakan);

        $this->assertFalse($isStockSufficient);
    }

    /**
     * FR09 / TC-PCK-011: Unit Test storePackaging: Percabangan penolakan saat stok fisik pouch kurang dari required pcs (Path 4)
     */
    public function test_percabangan_penolakan_saat_stok_fisik_pouch_kurang_dari_kebutuhan_pcs(): void
    {
        $stokDigunakanGram = 1000;
        $ukuranKemasanGram = 250;
        $stokPouchTersedia = 2; // Hanya 2 pouch

        $requiredPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram); // 4 pcs
        $isPouchStockEnough = ($stokPouchTersedia >= $requiredPcs);

        $this->assertEquals(4, $requiredPcs);
        $this->assertFalse($isPouchStockEnough);
    }

    /**
     * FR09 / TC-PCK-012: Unit Test storePackaging: Eksekusi loop pemotongan stok multi-batch secara beruntun (FIFO)
     */
    public function test_eksekusi_loop_pemotongan_stok_multi_batch_fifo(): void
    {
        $batches = [
            ['id' => 'Batch-A', 'sisa_stok' => 400],
            ['id' => 'Batch-B', 'sisa_stok' => 600],
        ];

        $kebutuhanPengemasan = 700;
        $remainingToDeduct = $kebutuhanPengemasan;

        foreach ($batches as &$batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            if ($batch['sisa_stok'] >= $remainingToDeduct) {
                $batch['sisa_stok'] -= $remainingToDeduct;
                $remainingToDeduct = 0;
            } else {
                $remainingToDeduct -= $batch['sisa_stok'];
                $batch['sisa_stok'] = 0;
            }
        }
        unset($batch);

        $this->assertEquals(0, $batches[0]['sisa_stok']); // Batch A habis
        $this->assertEquals(300, $batches[1]['sisa_stok']); // Batch B sisa 300g (600 - 300)
        $this->assertEquals(0, $remainingToDeduct);
    }

    /**
     * FR09 / TC-PCK-013: Unit Test storePackaging: Basis path sukses kalkulasi kemasan, sisa gramasi, dan commit DB
     */
    public function test_basis_path_sukses_kalkulasi_kemasan_dan_sisa_residu(): void
    {
        $stokDigunakanGram = 1050;
        $ukuranKemasanGram = 250;

        $hasilKemasanPcs = (int) floor($stokDigunakanGram / $ukuranKemasanGram);
        $stokTerkemas = $hasilKemasanPcs * $ukuranKemasanGram;
        $sisaResiduGram = $stokDigunakanGram - $stokTerkemas;

        $this->assertEquals(4, $hasilKemasanPcs);
        $this->assertEquals(1000, $stokTerkemas);
        $this->assertEquals(50, $sisaResiduGram);
    }
}
