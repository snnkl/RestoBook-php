<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurboSmsService
{
    protected string $baseUrl = 'https://api.turbosms.ua/message/send.json';
    protected string $token;
    protected string $sender;
    protected bool $isEnabled;

    public function __construct()
    {
        $this->token = (string) config('services.turbosms.token');
        $this->sender = (string) config('services.turbosms.sender');
        $this->isEnabled = (bool) config('services.turbosms.enable');
    }

    public function send($phone, $text): bool
    {
        // Перевіряємо статус, який ми отримали в конструкторі
        if (!$this->isEnabled) {
            Log::info("💰 [SMS MOCK] Імітація відправки на: $phone");
            Log::info("📩 Текст: $text");
            return true;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        try {
            $response = Http::withToken($this->token)->post($this->baseUrl, [
                'recipients' => [$phone],
                'sms' => [
                    'sender' => $this->sender,
                    'text' => $text
                ]
            ]);

            // Перевірка на успішність запиту та код відповіді TurboSMS (0 = успіх)
            if ($response->successful() && isset($response['response_code']) && $response['response_code'] == 0) {
                Log::info("✅ TurboSMS успішно відправлено на $phone");
                return true;
            } else {
                Log::error("❌ TurboSMS помилка: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("❌ TurboSMS Exception: " . $e->getMessage());
            return false;
        }
    }
}
