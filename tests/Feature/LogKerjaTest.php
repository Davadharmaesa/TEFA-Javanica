<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\Biaya;
use App\Models\LogPekerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogKerjaTest extends TestCase
{
    use RefreshDatabase;

    // TC-ACT-001
    public function test_tc_act_001_input_rekam_aktivitas_harian_pada_batch_aktif(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Ijen', 'asal_kopi' => 'Banyuwangi']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Arabika Ijen Special', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 5000]);
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-001', 'user_id' => $user->id, 'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id, 'profil_sangrai' => 'Medium Roast', 'durasi_roasting' => 12,
            'waktu_first_crack' => 10.00, 'waktu_development' => 120, 'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00, 'berat_mentah_sortasi' => 9.50, 'berat_matang_kotor' => 8.00,
            'berat_matang_bersih' => 7.80, 'persentase_susut' => 17.89, 'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'active', 'biaya_bahan_baku' => 1000000
        ]);
        $tugas = Biaya::create(['nama' => 'Upah Roaster', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Batch', 'harga' => 25000]);

        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => $batch->id, 'nama_pekerja' => 'Viona', 'komponen_id' => $tugas->id, 'jumlah' => 2.0,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('log_pekerja', ['roasting_batch_id' => $batch->id, 'nama_pekerja' => 'Viona']);
    }

    // TC-ACT-002
    public function test_tc_act_002_hak_akses_halaman_terminal_catatan_kerja(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->get('/log-kerja');
        $response->assertStatus(200);
        $response->assertSee('Terminal Catatan Kerja');
    }

    // TC-ACT-003
    public function test_tc_act_003_validasi_penolakan_sistem_jika_form_kosong(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => '', 'nama_pekerja' => '', 'komponen_id' => '', 'jumlah' => '',
        ]);
        $response->assertStatus(422); 
    }

    // TC-ACT-004
    public function test_tc_act_004_penghapusan_data_riwayat_aktivitas_kerja(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $greenBean = GreenBean::create(['nama_biji' => 'GB Robusta', 'asal_kopi' => 'Jember']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 2000]);
        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-002', 'user_id' => $user->id, 'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id, 'profil_sangrai' => 'Dark Roast', 'durasi_roasting' => 15,
            'waktu_first_crack' => 11.00, 'waktu_development' => 150, 'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.00, 'berat_mentah_sortasi' => 4.80, 'berat_matang_kotor' => 4.00,
            'berat_matang_bersih' => 3.80, 'persentase_susut' => 20.83, 'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'active', 'biaya_bahan_baku' => 500000
        ]);
        $tugas = Biaya::create(['nama' => 'Penyortiran', 'kategori' => 'Tenaga Kerja', 'satuan' => 'Kg', 'harga' => 5000]);
        $log = LogPekerja::create([
            'roasting_batch_id' => $batch->id, 'nama_pekerja' => 'Viona', 'biaya_id' => $tugas->id, 'jumlah_kerja' => 3.5, 'total_upah' => 17500,
        ]);

        $response = $this->actingAs($user)->deleteJson("/internal-api/log-kerja/{$log->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('log_pekerja', ['id' => $log->id]);
    }

    // TC-ACT-005
    public function test_tc_act_005_uji_batas_bawah_kuantitas_nol(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => 1, 'nama_pekerja' => 'Vio', 'komponen_id' => 1, 'jumlah' => 0,
        ]);
        $response->assertStatus(422);
    }

    // TC-ACT-006
    public function test_tc_act_006_uji_batas_negatif_kuantitas_kerja(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => 1, 'nama_pekerja' => 'Vio', 'komponen_id' => 1, 'jumlah' => -5,
        ]);
        $response->assertStatus(422);
    }

    // TC-ACT-007
    public function test_tc_act_007_uji_filter_pencarian_berdasarkan_nama(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->get('/log-kerja?search=Viona');
        $response->assertStatus(200);
    }

    // TC-ACT-008
    public function test_tc_act_008_submit_log_dengan_batch_id_tidak_valid(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => 9999, 'nama_pekerja' => 'Vio', 'komponen_id' => 1, 'jumlah' => 2,
        ]);
        $response->assertStatus(422);
    }
}