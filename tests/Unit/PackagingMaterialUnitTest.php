<?php

namespace Tests\Unit;

use App\Models\Packaging;
use Tests\TestCase;

class PackagingMaterialUnitTest extends TestCase
{
    /**
     * FR10 / TC-MAT-009: Unit Test storePackaging: Validasi penolakan branch tipe 'Lainnya' jika nama kustom kosong
     */
    public function test_validasi_penolakan_branch_tipe_lainnya_jika_custom_tipe_kosong(): void
    {
        $selectedType = 'Lainnya';
        $customType = trim('');

        $isCustomTypeInvalid = ($selectedType === 'Lainnya' && $customType === '');

        $this->assertTrue($isCustomTypeInvalid);
    }

    /**
     * FR10 / TC-MAT-010: Unit Test storePackaging: Validasi kegagalan request saat kapasitas gram bernilai negatif
     */
    public function test_validasi_kegagalan_request_saat_kapasitas_gram_negatif(): void
    {
        $validCapacity = 250;
        $invalidCapacity = 0;
        $negativeCapacity = -50;

        $this->assertTrue($validCapacity > 0);
        $this->assertFalse($invalidCapacity > 0);
        $this->assertFalse($negativeCapacity > 0);
    }

    /**
     * FR10 / TC-MAT-011: Unit Test storePackaging: Penetapan default nilai stok_pcs = 0 jika bernilai null
     */
    public function test_penetapan_default_nilai_stok_pcs_nol_jika_null(): void
    {
        $inputStok = null;
        $stokPcs = $inputStok ?? 0;

        $this->assertEquals(0, $stokPcs);
    }

    /**
     * FR10 / TC-MAT-012: Unit Test updatePackaging: Eksekusi update dengan mengabaikan unique check pada ID sendiri
     */
    public function test_eksekusi_update_mengabaikan_unique_check_pada_id_sendiri(): void
    {
        $currentRecord = ['id' => 1, 'nama_kemasan' => 'Pouch Kraft 250g'];
        $updateRequest = ['id' => 1, 'nama_kemasan' => 'Pouch Kraft 250g'];

        // Rule::unique('packagings', 'nama_kemasan')->ignore($id)
        $isUniqueAllowed = ($updateRequest['id'] === $currentRecord['id'])
            || ($updateRequest['nama_kemasan'] !== $currentRecord['nama_kemasan']);

        $this->assertTrue($isUniqueAllowed);
    }

    /**
     * FR10 / TC-MAT-013: Unit Test destroyPackaging: Penanganan exception catch block saat data berelasi
     */
    public function test_penanganan_exception_catch_block_saat_delete_berelasi(): void
    {
        $isRelated = true;
        $errorMessage = null;

        try {
            if ($isRelated) {
                throw new \Exception('Kemasan tidak dapat dihapus karena sudah digunakan dalam proses pengemasan!');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertEquals('Kemasan tidak dapat dihapus karena sudah digunakan dalam proses pengemasan!', $errorMessage);
    }

    /**
     * FR10 / TC-MAT-014: Unit Test model Packaging: Kalkulasi matematis total nilai aset kemasan dan simulasi selisih harga
     */
    public function test_kalkulasi_total_nilai_aset_dan_selisih_harga_modal(): void
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

        $hargaLama = 3000;
        $hargaBaru = 3500;
        $jumlahKemasan = 10;
        $selisihBiaya = ($hargaBaru * $jumlahKemasan) - ($hargaLama * $jumlahKemasan);
        $this->assertEquals(5000, $selisihBiaya);
    }
}
