<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\SearchController;
use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class SearchNavigationWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * TC-WB-NAV-001 (Decision: empty($query) is TRUE)
     * Menguji jika parameter query 'q' kosong, controller hanya mengembalikan daftar navigasi halaman
     * dan tidak mengeksekusi pencarian ke tabel database.
     */
    public function test_tc_wb_nav_001_empty_query_returns_default_navigation_pages(): void
    {
        $user = User::factory()->create(['role' => 'pic']);
        $this->actingAs($user);

        $controller = new SearchController();
        $request = Request::create('/api/search', 'GET', ['q' => '']);

        $response = $controller->search($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        // Memastikan tipe hasil yang kembali semuanya bertipe navigasi 'Halaman'
        foreach ($data as $item) {
            $this->assertEquals('Halaman', $item['type']);
        }
    }

    /**
     * TC-WB-NAV-002 (Decision: role filtering logic pada $page['role'] === 'pic')
     * Menguji pembatasan hak akses navigasi: User dengan role non-pic (operator)
     * tidak boleh melihat menu bertanda role pic (seperti Kelola Pengguna).
     */
    public function test_tc_wb_nav_002_role_based_navigation_filtering_for_operator(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $this->actingAs($operator);

        $controller = new SearchController();
        $request = Request::create('/api/search', 'GET', ['q' => '']);

        $response = $controller->search($request);
        $data = $response->getData(true);

        $labels = array_column($data, 'label');

        // Operator tidak boleh melihat halaman khusus PIC
        $this->assertNotContains('Kelola Pengguna', $labels);
        $this->assertNotContains('Ekspor Laporan Keuangan', $labels);
        $this->assertNotContains('Kalkulator HPP', $labels);

        // Halaman umum harus tetap bisa dilihat
        $this->assertContains('Beranda', $labels);
        $this->assertContains('Roasting & Produksi', $labels);
    }

    /**
     * TC-WB-NAV-003 (Decision: !empty($query) is TRUE & Database Match Found)
     * Menguji pencarian data dinamis di database berdasarkan keyword.
     * Sistem harus mengembalikan record yang cocok dengan format DTO yang tepat.
     */
    public function test_tc_wb_nav_003_query_matches_database_green_bean_record(): void
    {
        $user = User::factory()->create(['role' => 'pic']);
        $this->actingAs($user);

        $greenBean = GreenBean::create([
            'nama_biji' => 'Mandheling Sumatera Super',
            'asal_kopi' => 'Sumatera Utara',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'kode_masuk' => 'GB-MAND-01',
            'stok_gram' => 5000,
            'harga_per_kg' => 135000,
            'tanggal_beli' => now(),
        ]);

        $controller = new SearchController();
        $request = Request::create('/api/search', 'GET', ['q' => 'Mandheling']);

        $response = $controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());

        // Cari item bertipe Bahan Baku
        $found = false;
        foreach ($data as $item) {
            if ($item['type'] === 'Bahan Baku' && str_contains($item['label'], 'Mandheling Sumatera Super')) {
                $found = true;
                $this->assertEquals('/inventory', $item['url']);
                break;
            }
        }

        $this->assertTrue($found, 'Data Green Bean hasil pencarian tidak ditemukan pada respon JSON');
    }

    /**
     * TC-WB-NAV-004 (Decision: !empty($query) is TRUE & Database Match NOT Found)
     * Menguji pencarian dengan kata kunci yang tidak ada di database sama sekali.
     * Sistem tetap mengembalikan HTTP 200 dan tidak mengandung hasil data entitas.
     */
    public function test_tc_wb_nav_004_query_not_found_returns_empty_entity_results(): void
    {
        $user = User::factory()->create(['role' => 'pic']);
        $this->actingAs($user);

        $controller = new SearchController();
        $request = Request::create('/api/search', 'GET', ['q' => 'keyword_pasti_tidak_ada_xyz123']);

        $response = $controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());

        // Tidak boleh ada entitas database yang cocok
        $entityTypes = ['Bahan Baku', 'Batch Roasting', 'Stok Kemasan', 'Produk Jadi', 'Penjualan', 'Pengguna'];
        foreach ($data as $item) {
            $this->assertNotContains($item['type'], $entityTypes);
        }
    }
}
