<?php

namespace Tests\Feature;

use App\Models\GreenBean;
use App\Models\GreenBeanStock;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set fallback environment variables for tests
        config(['services.whatsapp.recipient' => '628123456789']);
    }

    public function test_whatsapp_notification_sent_when_stock_falls_below_5kg(): void
    {
        Http::fake([
            '*/send-message' => Http::response(['success' => true], 200),
        ]);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Bali Kintamani',
            'asal_kopi' => 'Bali',
        ]);

        // Add stock below 5kg (4000g)
        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 4000,
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $waService = new WhatsappService();
        
        // Trigger low stock check
        $waService->checkAndNotifyLowStock($greenBean->id);

        // Assert message was sent
        Http::assertSent(function ($request) {
            return str_contains($request['message'], 'Stok Bahan Baku mentah menipis') &&
                   $request['number'] === '628123456789';
        });

        // Assert cache lock is set
        $this->assertTrue(Cache::has('wa_notif_sent_' . $greenBean->id));
    }

    public function test_whatsapp_notification_does_not_spam_if_already_sent_in_24h(): void
    {
        Http::fake([
            '*/send-message' => Http::response(['success' => true], 200),
        ]);

        $greenBean = GreenBean::create([
            'nama_biji' => 'GB Flores Bajawa',
            'asal_kopi' => 'Flores',
        ]);

        // Mock cache to say notification was already sent today
        Cache::put('wa_notif_sent_' . $greenBean->id, true, now()->addDay());

        GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 3000,
            'harga_beli' => 110000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $waService = new WhatsappService();
        $waService->checkAndNotifyLowStock($greenBean->id);

        // Assert no external HTTP message request was made
        Http::assertNothingSent();
    }
}
