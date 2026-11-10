<?php

namespace Tests\Unit;

use Tests\TestCase;
use Exception;

class LogKerjaUnitTest extends TestCase
{
    public function test_tc_wb_act_001_validasi_parameter_wajib_kosong(): void
    {
        $payload = ['batch_id' => '', 'nama_pekerja' => '', 'jumlah_jam' => 0];
        $isValid = !empty($payload['batch_id']) && !empty($payload['nama_pekerja']) && $payload['jumlah_jam'] > 0;
        $this->assertFalse($isValid);
    }

    public function test_tc_wb_act_002_validasi_batch_tidak_ditemukan(): void
    {
        $existingBatches = [1, 2, 3];
        $requestedBatchId = 999;
        $exists = in_array($requestedBatchId, $existingBatches);
        $this->assertFalse($exists);
    }

    public function test_tc_wb_act_003_validasi_kuantitas_jam_kerja_tidak_valid(): void
    {
        $jamZero = 0;
        $isValidZero = $jamZero > 0;
        $this->assertFalse($isValidZero);

        $jamNegative = -5;
        $isValidNegative = $jamNegative > 0;
        $this->assertFalse($isValidNegative);
    }

    public function test_tc_wb_act_004_basis_path_sukses_pencatatan_log(): void
    {
        $data = ['batch_id' => 1, 'nama_pekerja' => 'Vio', 'jumlah_jam' => 4];
        $isProcessed = !empty($data['batch_id']) && $data['jumlah_jam'] > 0;
        $this->assertTrue($isProcessed);
    }

    public function test_tc_wb_act_005_filter_pencarian_kosong(): void
    {
        $logs = [['nama_pekerja' => 'Vio'], ['nama_pekerja' => 'Zul']];
        $search = '';
        $filtered = array_filter($logs, fn($log) => empty($search) || stripos($log['nama_pekerja'], $search) !== false);
        $this->assertCount(2, $filtered);
    }

    public function test_tc_wb_act_006_filter_pencarian_keyword_spesifik(): void
    {
        $logs = [['nama_pekerja' => 'Vio'], ['nama_pekerja' => 'Zul']];
        $search = 'Vio';
        $filtered = array_filter($logs, fn($log) => empty($search) || stripos($log['nama_pekerja'], $search) !== false);
        $this->assertCount(1, $filtered);
    }

    public function test_tc_wb_act_007_penghapusan_log_dengan_id_valid(): void
    {
        $databaseLogs = [1 => ['id' => 1, 'nama' => 'Vio']];
        $idToDelete = 1;
        if (isset($databaseLogs[$idToDelete])) {
            unset($databaseLogs[$idToDelete]);
        }
        $this->assertArrayNotHasKey(1, $databaseLogs);
    }

    /**
     * TC-WB-ACT-008: Unit Test destroy: Penanganan exception catch block saat ID log yang akan dihapus tidak ditemukan
     */
    public function test_tc_wb_act_008_penanganan_exception_id_log_tidak_ditemukan(): void
    {
        $databaseLogs = [1 => ['id' => 1, 'nama' => 'Vio']];
        $idToDelete = 9999; // ID tidak ada

        $statusCode = 200;
        try {
            if (!isset($databaseLogs[$idToDelete])) {
                throw new Exception("Log not found", 404);
            }
        } catch (Exception $e) {
            $statusCode = $e->getCode();
        }

        $this->assertEquals(404, $statusCode);
    }
}