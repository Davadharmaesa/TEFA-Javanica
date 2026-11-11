<?php

namespace Tests\Unit;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Http\Controllers\InventoryController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GreenBeanStockWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    // =========================================================================
    // WB-GBSTK-01: storeGreenBean() — Basis Paths P1 s.d. P5 (V(G)=5)
    // =========================================================================

    /**
     * TC-WB-GBSTK-001 (Path 1: N1 -> N2)
     * Menguji decision D1 True: input stok bernilai minus atau field required kosong.
     */
    public function test_store_path_1_validation_error(): void
    {
        $controller = new InventoryController();
        $request = Request::create('/inventory/green-bean', 'POST', [
            'green_bean_id' => 1,
            'stok_gram' => -500, // minus
            'tanggal_beli' => now()->format('d/m/Y'),
            'total_harga' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $controller->storeGreenBean($request);
    }

    /**
     * TC-WB-GBSTK-002 (Path 2: N1 -> N3 -> N4 -> N5 -> N6 -> N8 -> N9)
     * Menguji decision D4 True: tanggal pembelian masa depan (> today) ditolak sistem.
     */
    public function test_store_path_2_future_date_rejected(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Arabika Bali', 'asal_kopi' => 'Bali']);

        $controller = new InventoryController();
        $tomorrow = now()->addDay()->format('d/m/Y'); // besok
        $request = Request::create('/inventory/green-bean', 'POST', [
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000,
            'tanggal_beli' => $tomorrow,
            'total_harga' => 500000,
        ]);

        $response = $controller->storeGreenBean($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('melebihi hari ini', session('error'));
    }

    /**
     * TC-WB-GBSTK-003 (Path 3: N1 -> N3 -> N4 -> N5 -> N6 -> N8 -> N10)
     * Menguji kondisi normal: stok 5kg, total Rp 500.000, harga beli terhitung Rp 100.000/kg.
     */
    public function test_store_path_3_normal_calculation_and_store(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Robusta Dampit', 'asal_kopi' => 'Malang']);

        $controller = new InventoryController();
        $today = now()->format('d/m/Y');
        $request = Request::create('/inventory/green-bean', 'POST', [
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000, // 5 kg
            'tanggal_beli' => $today,
            'total_harga' => 500000, // Rp 500.000
        ]);

        $response = $controller->storeGreenBean($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000,
            'total_harga' => 500000,
            'harga_beli' => 100000, // 500.000 / 5 kg = 100.000
        ]);
    }

    /**
     * TC-WB-GBSTK-004 (Path 4: N1 -> N3 -> N4 -> N6 -> N8 -> N10)
     * Menguji decision D2 False: total_harga = 0 (stok gratis/sampel) -> harga_beli tetap 0.
     */
    public function test_store_path_4_zero_total_price_free_sample(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Sampel', 'asal_kopi' => 'Aceh']);

        $controller = new InventoryController();
        $today = now()->format('d/m/Y');
        $request = Request::create('/inventory/green-bean', 'POST', [
            'green_bean_id' => $gb->id,
            'stok_gram' => 1000,
            'tanggal_beli' => $today,
            'total_harga' => 0, // gratis
        ]);

        $response = $controller->storeGreenBean($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $gb->id,
            'harga_beli' => 0,
        ]);
    }

    /**
     * TC-WB-GBSTK-005 (Path 5: N6 -> N7 -> N8)
     * Menguji decision D3 False: tanggal berformat ISO (Y-m-d) tertangkap catch block Carbon::parse.
     */
    public function test_store_path_5_fallback_carbon_date_format(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB ISO Test', 'asal_kopi' => 'Jawa']);

        $controller = new InventoryController();
        $isoDate = now()->toDateString(); // Y-m-d
        $request = Request::create('/inventory/green-bean', 'POST', [
            'green_bean_id' => $gb->id,
            'stok_gram' => 2000,
            'tanggal_beli' => $isoDate,
            'total_harga' => 200000,
        ]);

        $response = $controller->storeGreenBean($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'green_bean_id' => $gb->id,
            'tanggal_beli' => $isoDate,
        ]);
    }

    // =========================================================================
    // WB-GBSTK-02: updateGreenBean() — Basis Paths P1 s.d. P5 (V(G)=5)
    // =========================================================================

    /**
     * TC-WB-GBSTK-006 (Path 1: N1 -> N2)
     * Menguji decision D1 True: validasi update gagal jika stok minus.
     */
    public function test_update_path_1_validation_error(): void
    {
        $controller = new InventoryController();
        $request = Request::create('/inventory/green-bean/1', 'PUT', [
            'stok_gram' => -500,
            'tanggal_beli' => now()->format('d/m/Y'),
            'total_harga' => 100000,
        ]);

        $this->expectException(ValidationException::class);
        $controller->updateGreenBean($request, 1);
    }

    /**
     * TC-WB-GBSTK-007 (Path 2: N8 -> N9)
     * Menguji decision D4 True: update tanggal ke masa depan ditolak sistem.
     */
    public function test_update_path_2_future_date_rejected(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Update Test', 'asal_kopi' => 'Bali']);
        $stock = GreenBeanStock::create([
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000,
            'harga_beli' => 100000,
            'total_harga' => 500000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new InventoryController();
        $tomorrow = now()->addDay()->format('d/m/Y');
        $request = Request::create('/inventory/green-bean/' . $stock->id, 'PUT', [
            'stok_gram' => 5000,
            'tanggal_beli' => $tomorrow,
            'total_harga' => 500000,
        ]);

        $response = $controller->updateGreenBean($request, $stock->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
    }

    /**
     * TC-WB-GBSTK-008 (Path 3: N1 -> N3 -> N4 -> N5 -> N6 -> N8 -> N10 -> N11 -> N12)
     * Menguji kondisi normal update data dan eksekusi try/catch notifikasi WA.
     */
    public function test_update_path_3_normal_calculation_and_update(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Normal Update', 'asal_kopi' => 'Toraja']);
        $stock = GreenBeanStock::create([
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000,
            'harga_beli' => 100000,
            'total_harga' => 500000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new InventoryController();
        $today = now()->format('d/m/Y');
        // Update stok jadi 8 kg (8000g), total Rp 800.000
        $request = Request::create('/inventory/green-bean/' . $stock->id, 'PUT', [
            'stok_gram' => 8000,
            'tanggal_beli' => $today,
            'total_harga' => 800000,
        ]);

        $response = $controller->updateGreenBean($request, $stock->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'id' => $stock->id,
            'stok_gram' => 8000,
            'total_harga' => 800000,
            'harga_beli' => 100000,
        ]);
    }

    /**
     * TC-WB-GBSTK-009 (Path 4: N4 -> N6 -> N8 -> N10)
     * Menguji update dengan total_harga = 0.
     */
    public function test_update_path_4_zero_total_price_sample(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB Free Update', 'asal_kopi' => 'Ijen']);
        $stock = GreenBeanStock::create([
            'green_bean_id' => $gb->id,
            'stok_gram' => 5000,
            'harga_beli' => 100000,
            'total_harga' => 500000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new InventoryController();
        $today = now()->format('d/m/Y');
        $request = Request::create('/inventory/green-bean/' . $stock->id, 'PUT', [
            'stok_gram' => 2000,
            'tanggal_beli' => $today,
            'total_harga' => 0,
        ]);

        $response = $controller->updateGreenBean($request, $stock->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'id' => $stock->id,
            'stok_gram' => 2000,
            'harga_beli' => 0,
        ]);
    }

    /**
     * TC-WB-GBSTK-010 (Path 5: N6 -> N7 -> N8)
     * Menguji update tanggal dengan format ISO tertangkap catch Carbon::parse.
     */
    public function test_update_path_5_fallback_carbon_date_format(): void
    {
        $gb = GreenBean::create(['nama_biji' => 'GB ISO Update', 'asal_kopi' => 'Flores']);
        $stock = GreenBeanStock::create([
            'green_bean_id' => $gb->id,
            'stok_gram' => 3000,
            'harga_beli' => 100000,
            'total_harga' => 300000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $controller = new InventoryController();
        $isoDate = now()->toDateString();
        $request = Request::create('/inventory/green-bean/' . $stock->id, 'PUT', [
            'stok_gram' => 3000,
            'tanggal_beli' => $isoDate,
            'total_harga' => 300000,
        ]);

        $response = $controller->updateGreenBean($request, $stock->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('green_bean_stock', [
            'id' => $stock->id,
            'tanggal_beli' => $isoDate,
        ]);
    }
}

