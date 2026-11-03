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

    /**
     * Uji apakah Operator/PIC dapat mengakses halaman terminal catatan kerja.
     */
    public function test_user_can_access_log_kerja_page(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->get('/log-kerja');

        $response->assertStatus(200);
        $response->assertSee('Terminal Catatan Kerja');
    }

    /**
     * Uji apakah sistem berhasil menyimpan log aktivitas kerja harian dan terikat ke batch aktif.
     */
    public function test_user_can_submit_log_kerja_for_active_batch(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Siapkan data pendukung untuk relasi batch produksi
        $greenBean = GreenBean::create(['nama_biji' => 'GB Arabika Ijen', 'asal_kopi' => 'Banyuwangi']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Arabika Ijen Special', 'bentuk_akhir' => 'Biji Utuh', 'total_stok' => 5000]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 12,
            'waktu_first_crack' => 10.00,
            'waktu_development' => 120,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00,
            'berat_mentah_sortasi' => 9.50,
            'berat_matang_kotor' => 8.00,
            'berat_matang_bersih' => 7.80,
            'persentase_susut' => 17.89,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'active',
            'biaya_bahan_baku' => 1000000
        ]);

        $tugas = Biaya::create([
            'nama' => 'Upah Roaster',
            'kategori' => 'Tenaga Kerja',
            'satuan' => 'Batch',
            'harga' => 25000
        ]);

        // Kirim request POST ke endpoint internal-api log-kerja
        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => $batch->id,
            'nama_pekerja' => 'Habib',
            'komponen_id' => $tugas->id,
            'jumlah' => 2.0,
        ]);

        $response->assertStatus(200);

        // Validasi data masuk ke database tabel log_pekerja
        $this->assertDatabaseHas('log_pekerja', [
            'roasting_batch_id' => $batch->id,
            'nama_pekerja' => 'Habib',
            'biaya_id' => $tugas->id,
            'jumlah_kerja' => 2.0,
        ]);
    }

    /**
     * Uji apakah sistem menolak input log kerja jika field wajib dikosongkan.
     */
    public function test_log_kerja_validation_fails_if_fields_empty(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->postJson('/internal-api/log-kerja', [
            'batch_id' => '',
            'nama_pekerja' => '',
            'komponen_id' => '',
            'jumlah' => '',
        ]);

        $response->assertStatus(422); // Unprocessable Entity / Gagal Validasi
    }

    /**
     * Uji apakah user dapat menghapus data log aktivitas kerja.
     */
    public function test_user_can_delete_log_kerja(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create(['nama_biji' => 'GB Robusta', 'asal_kopi' => 'Jember']);
        $roastedBean = RoastedBean::create(['nama_kopi' => 'Robusta Tubruk', 'bentuk_akhir' => 'Bubuk', 'total_stok' => 2000]);

        $batch = RoastingBatch::create([
            'kode_batch' => 'BATCH-002',
            'user_id' => $user->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Dark Roast',
            'durasi_roasting' => 15,
            'waktu_first_crack' => 11.00,
            'waktu_development' => 150,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.00,
            'berat_mentah_sortasi' => 4.80,
            'berat_matang_kotor' => 4.00,
            'berat_matang_bersih' => 3.80,
            'persentase_susut' => 20.83,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'active',
            'biaya_bahan_baku' => 500000
        ]);

        $tugas = Biaya::create([
            'nama' => 'Penyortiran',
            'kategori' => 'Tenaga Kerja',
            'satuan' => 'Kg',
            'harga' => 5000
        ]);

        $log = LogPekerja::create([
            'roasting_batch_id' => $batch->id,
            'nama_pekerja' => 'Viona',
            'biaya_id' => $tugas->id,
            'jumlah_kerja' => 3.5,
            'total_upah' => 17500,
        ]);

        $response = $this->actingAs($user)->deleteJson("/internal-api/log-kerja/{$log->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('log_pekerja', [
            'id' => $log->id,
        ]);
    }
}