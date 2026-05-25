<?php

namespace Tests\Feature;

use App\Models\Packaging;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pic_can_create_custom_packaging_type(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($user)->postJson('/internal-api/master/packagings', [
            'tipe' => 'Lainnya',
            'custom_tipe' => 'Kantong Kertas',
            'nama_kemasan' => 'Kantong Kertas 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 1200,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.tipe', 'Kantong Kertas')
            ->assertJsonPath('data.nama_kemasan', 'Kantong Kertas 250g');

        $this->assertDatabaseHas('packagings', [
            'tipe' => 'Kantong Kertas',
            'nama_kemasan' => 'Kantong Kertas 250g',
            'kapasitas_gram' => 250,
            'harga_satuan' => 1200,
        ]);
    }

    public function test_duplicate_packaging_type_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);

        Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Alumunium 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 500,
            'stok_pcs' => 0,
        ]);

        $response = $this->actingAs($user)->postJson('/internal-api/master/packagings', [
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Alumunium 500g',
            'kapasitas_gram' => 500,
            'harga_satuan' => 750,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tipe');
    }
}
