<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected mixed $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->baseUrl = "https://api.telegram.org/bot$this->token/";
    }

    public function sendMessage($chatId, $message): bool
    {
        $response = Http::post($this->baseUrl . 'sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            Log::error("❌ TELEGRAM ERROR: " . $response->body());
            return false;
        }

        return true;
    }

    public function getUpdates()
    {
        $response = Http::get($this->baseUrl . 'getUpdates');
        return $response->json()['result'] ?? [];
    }
}
