<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function getRates()
    {

        return Cache::remember('currency_rates', 3600, function () {
            try {
                $response = Http::get('https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');

                return $response->json();
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function getCourse(string $currencyCode, array $rates): ?float
    {
        foreach ($rates as $rate) {
            if (isset($rate['ccy']) && $rate['ccy'] === $currencyCode) {
                return (float) $rate['sale'];
            }
        }
        return null;
    }
}
