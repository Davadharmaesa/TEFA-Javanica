<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\GreenBean;
use App\Models\GreenBeanStock;

class WhatsappService
{
    protected $baseUrl;
    protected $apiKey;
    protected $recipient;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:3000');
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->recipient = config('services.whatsapp.recipient', '');
    }

    /**
     * Send a general text message to the configured notification recipient.
     */
    public function sendMessage($message)
    {
        if (empty($this->recipient)) {
            \Log::info("WA Notification skipped: Recipient phone number is not configured in .env");
            return false;
        }

        try {
            $response = Http::withHeaders(array_filter([
                'X-API-KEY' => $this->apiKey,
                'apikey' => $this->apiKey,
            ]))->post("{$this->baseUrl}/send-message", [
                'number' => $this->recipient,
                'message' => $message
            ]);

            if ($response->successful()) {
                return true;
            }

            \Log::warning("WA Notification response failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            \Log::error("WA Notification failed to send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if total green bean stock is below 5kg (5000g) and send a warning.
     * Uses a cache lock to prevent sending multiple notifications in a 24-hour window.
     */
    public function checkAndNotifyLowStock($greenBeanId)
    {
        $greenBean = GreenBean::find($greenBeanId);
        if (!$greenBean) return;

        // Sum the total stock of this green bean in grams
        $totalStokGram = GreenBeanStock::where('green_bean_id', $greenBeanId)->sum('stok_gram');

        // Check if stock is below 5kg (5000g)
        if ($totalStokGram < 5000) {
            $cacheKey = 'wa_notif_sent_' . $greenBeanId;

            // Check cache to prevent spamming
            if (!Cache::has($cacheKey)) {
                $pesan = "⚠️ *PERINGATAN STOK TEFA JAVANICA* ⚠️\n\n" .
                         "Stok Bahan Baku mentah menipis!\n" .
                         "Nama Green Bean: *{$greenBean->nama_biji}*\n" .
                         "Asal Kopi: *{$greenBean->asal_kopi}*\n" .
                         "Sisa Stok Saat Ini: *" . ($totalStokGram / 1000) . " kg*\n\n" .
                         "Mohon segera lakukan pengisian/pembelian ulang.";

                if ($this->sendMessage($pesan)) {
                    // Cache the sent flag for 24 hours
                    Cache::put($cacheKey, true, now()->addDay());
                }
            }
        }
    }
}
