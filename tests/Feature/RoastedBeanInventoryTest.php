<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RoastedBeanInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function test_roasted_bean_can_be_stored(): void
    {
        // 1. Buat user dummy dengan role Operator
        $user = User::factory()->create([
            'role' => 'Operator'
        ]);

        // 2. Data payload lengkap sesuai validasi controller
        $payload = [
            'nama_kopi' => 'Arabica Gayo',
            'bentuk_akhir' => 'Beans',
            'total_stok' => 1000,
            'satuan_dasar' => 'gram',
        ];

        // 3. Tembak menggunakan post
        $response = $this->actingAs($user)
                         ->post('/inventory/roasted-bean', $payload);

        // 4. Pastikan berhasil redirect sukses dan masuk ke database
        $response->assertRedirect();

        $this->assertDatabaseHas('roasted_bean', [
            'nama_kopi' => 'Arabica Gayo',
        ]);
    }
}
