<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


class ExchangeRateService
{
    public function getUSDtoCDF()
    {try {
    $response = Http::get('https://api.exchangerate-api.com/v4/latest/USD');
    $data = $response->json();

    // Vérifier si la devise CDF existe
    if (!isset($data['rates']['CDF'])) {
        throw new \Exception("Taux pour CDF introuvable dans la réponse de l'API.");
    }

    return floatval($data['rates']['CDF']); // renvoie toujours un nombre
} catch (\Exception $e) {
    \Log::error("Erreur ExchangeRateService: " . $e->getMessage());
    dd($e->getMessage()); // ou simplement Log si tu ne veux pas stopper le code
    return 0; // valeur de secours
}
    }
}