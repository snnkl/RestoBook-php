<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonobankService
{
    protected mixed $token;
    protected string $baseUrl = 'https://api.monobank.ua/api/merchant/invoice/create';

    public function __construct()
    {
        $this->token = config('services.monobank.token');
    }

    public function createInvoice($booking, $amount)
    {
        $webhookUrl = config('services.monobank.app_url') . '/monobank/webhook';
        $redirectUrl = config('services.monobank.app_url') . '/my-bookings?id=' . $booking->id;


        Log::info('Mono URLs', [
            'webhook' => $webhookUrl,
            'redirect' => $redirectUrl,
        ]);


        $response = Http::withHeaders([
            'X-Token' => $this->token
        ])->post($this->baseUrl, [
            'amount' => $amount * 100,
            'ccy' => 980,
            'merchantPaymInfo' => [
                'reference' => (string)$booking->id,
                'destination' => "Оплата бронювання #$booking->id",
                'basketOrder' => [
                    [
                        'name' => "Бронювання столика #{$booking->table->number}",
                        'qty' => 1,
                        'sum' => $amount * 100,
                        'unit' => 'шт.',
                        'code' => 't-' . $booking->table->id
                    ]
                ]
            ],
            'redirectUrl' => $redirectUrl,
            'webHookUrl' => $webhookUrl,
            'validity' => 3600
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error("Monobank Error: " . $response->body());
            return null;
        }
    }

    public function createSubscriptionInvoice($user, $amount)
    {
        $baseUrl = config('services.monobank.app_url');


        $webhookUrl = $baseUrl . '/monobank/subscription/webhook';

        $redirectUrl = route('subscription.index');

        Log::info("👑 [VIP] Створення інвойсу для User ID $user->id", [
            'webhook' => $webhookUrl
        ]);

        $response = Http::withHeaders([
            'X-Token' => $this->token
        ])->post($this->baseUrl, [
            'amount' => $amount * 100,
            'ccy' => 980,
            'merchantPaymInfo' => [
                'reference' => "vip_user_$user->id",
                'destination' => "Оплата VIP-підписки",
            ],
            'redirectUrl' => $redirectUrl,
            'webHookUrl' => $webhookUrl,
            'validity' => 3600
        ]);

        if ($response->successful()) {
            return $response->json('pageUrl');
        } else {
            Log::error("❌ Monobank Error (Sub): " . $response->body());
            return null;
        }
    }
}
