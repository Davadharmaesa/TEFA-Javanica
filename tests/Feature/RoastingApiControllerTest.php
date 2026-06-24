<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\GreenBeanStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoastingApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_batch_requires_suhu_parameter(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Send request without suhu
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-001',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('suhu');
    }

    public function test_store_batch_saves_suhu_successfully(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);
        
        $roastedBean = RoastedBean::create([
            'nama_kopi' => 'Roasted Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 0,
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 10000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        // Send request with suhu
        $response = $this->actingAs($user)->postJson('/internal-api/roasting/batches', [
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'kode_batch' => 'BATCH-001',
            'profil_sangrai' => 'Medium Roast',
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'suhu' => 205,
            'berat_mentah_gudang' => 5.0,
            'berat_mentah_sortasi' => 4.8,
            'berat_matang_kotor' => 4.2,
            'berat_matang_bersih' => 4.0,
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('roasting_batch', [
            'kode_batch' => 'BATCH-001',
            'suhu' => 205,
        ]);
    }
}
