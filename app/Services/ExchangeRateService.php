<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function getUSDtoCDF(): float
    {
        try {
            $response = Http::timeout(5)->get('https://api.exchangerate-api.com/v4/latest/USD');

            if (!$response->successful()) {
                throw new \Exception("API exchange rate inaccessible");
            }

            $data = $response->json();

            if (!isset($data['rates']['CDF'])) {
                throw new \Exception("Taux CDF introuvable");
            }

            return (float) $data['rates']['CDF'];

        } catch (\Exception $e) {

            Log::error("ExchangeRateService error: " . $e->getMessage());

            // ⚠️ fallback sécurisé pour RDC (tu peux ajuster)
            return 2800.0;
        }
    }
}