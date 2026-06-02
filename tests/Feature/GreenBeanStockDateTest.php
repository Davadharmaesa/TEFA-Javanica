<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenBeanStockDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_add_green_bean_stock_with_future_date(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);

        $futureDate = now()->addDay()->format('d/m/Y');

        $response = $this->actingAs($user)->post('/inventory/green-bean', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'tanggal_beli' => $futureDate,
            'total_harga' => 150000,
        ]);

        $response->assertSessionHas('error', 'Tanggal barang masuk tidak boleh melebihi hari ini!');
        $this->assertDatabaseEmpty('green_bean_stock');
    }

    public function test_can_add_green_bean_stock_with_today_date(): void
    {
        $user = User::factory()->create(['role' => 'PIC']);
        
        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);

        $todayDate = now()->format('d/m/Y');

        $response = $this->actingAs($user)->post('/inventory/green-bean', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
            'tanggal_beli' => $todayDate,
            'total_harga' => 150000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Stok Green Bean berhasil ditambahkan!');
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 5000,
        ]);
    }
}
