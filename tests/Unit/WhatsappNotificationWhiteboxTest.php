<?php

namespace Tests\Unit;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsappNotificationWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        // Set konfigurasi WhatsApp untuk keperluan testing
        config([
            'services.whatsapp.url' => 'http://whatsapp-mock.test',
            'services.whatsapp.api_key' => 'mock_api_key_123',
            'services.whatsapp.recipient' => '08123456789',
        ]);

        Cache::flush();
    }

    /**
     * TC-WB-NOTIF-001 (Decision: totalStokGram < 5000 is FALSE)
     * Menguji jika total stok green bean >= 5000g (stok aman), sistem tidak memanggil HTTP/API WhatsApp.
     */
    public function test_tc_wb_notif_001_safe_stock_does_not_trigger_notification(): void
    {
        Http::fake();

        $greenBean = GreenBean::create([
            'nama_biji' => 'Arabica Gayo Aman',
            'asal_kopi' => 'Aceh',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'kode_masuk' => 'GB-SAFE-01',
            'stok_gram' => 10000, // 10 kg (aman)
            'harga_per_kg' => 120000,
            'tanggal_beli' => now(),
        ]);

        $service = new WhatsappService();
        $service->checkAndNotifyLowStock($greenBean->id);

        Http::assertNothingSent();
        $this->assertFalse(Cache::has('wa_notif_sent_' . $greenBean->id));
    }

    /**
     * TC-WB-NOTIF-002 (Decision: totalStokGram < 5000 is TRUE & Cache has is FALSE)
     * Menguji jika stok kritis (< 5000g) dan notifikasi belum pernah dikirim hari ini,
     * sistem berhasil memicu HTTP POST ke API WhatsApp dan menyimpan flag di Cache.
     */
    public function test_tc_wb_notif_002_low_stock_triggers_wa_notification_and_caches(): void
    {
        Http::fake([
            'http://whatsapp-mock.test/send-message' => Http::response(['status' => 'success'], 200),
        ]);

        $greenBean = GreenBean::create([
            'nama_biji' => 'Robusta Temanggung Kritis',
            'asal_kopi' => 'Jawa Tengah',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'kode_masuk' => 'GB-LOW-01',
            'stok_gram' => 2500, // 2.5 kg (kritis < 5000g)
            'harga_per_kg' => 90000,
            'tanggal_beli' => now(),
        ]);

        $service = new WhatsappService();
        $service->checkAndNotifyLowStock($greenBean->id);

        Http::assertSent(function ($request) use ($greenBean) {
            return $request->url() === 'http://whatsapp-mock.test/send-message'
                && $request['number'] === '08123456789'
                && str_contains($request['message'], 'Robusta Temanggung Kritis')
                && str_contains($request['message'], '2.5 kg');
        });

        $this->assertTrue(Cache::has('wa_notif_sent_' . $greenBean->id));
    }

    /**
     * TC-WB-NOTIF-003 (Exception Handling / Statement Coverage blok catch)
     * Menguji jika request API mengalami koneksi error / exception, sistem menangkap error
     * dengan aman, mencatat log error, mengembalikan nilai false, dan tidak crash.
     */
    public function test_tc_wb_notif_003_api_failure_handled_safely_without_crash(): void
    {
        Http::fake([
            'http://whatsapp-mock.test/send-message' => function () {
                throw new \Exception('Connection to WhatsApp gateway timed out');
            },
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Connection to WhatsApp gateway timed out');
            });

        $service = new WhatsappService();
        $result = $service->sendMessage('Pesan uji coba');

        $this->assertFalse($result);
    }

    /**
     * TC-WB-NOTIF-004 (Decision: Cache::has($cacheKey) is TRUE - Throttling/Anti-spam)
     * Menguji jika notifikasi sudah pernah dikirim dalam 24 jam terakhir, sistem tidak
     * mengirim ulang pesan WhatsApp (mencegah spam notifikasi berulang).
     */
    public function test_tc_wb_notif_004_duplicate_notification_throttled_by_cache(): void
    {
        Http::fake();

        $greenBean = GreenBean::create([
            'nama_biji' => 'Arabica Kintamani Spam Check',
            'asal_kopi' => 'Bali',
        ]);

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'kode_masuk' => 'GB-SPAM-01',
            'stok_gram' => 1500, // Kritis < 5000g
            'harga_per_kg' => 110000,
            'tanggal_beli' => now(),
        ]);

        // Simulasikan notifikasi sudah pernah dikirim sebelumnya (sudah ada di cache)
        $cacheKey = 'wa_notif_sent_' . $greenBean->id;
        Cache::put($cacheKey, true, now()->addDay());

        $service = new WhatsappService();
        $service->checkAndNotifyLowStock($greenBean->id);

        // Verifikasi tidak ada HTTP request yang dikirimkan
        Http::assertNothingSent();
    }
}
