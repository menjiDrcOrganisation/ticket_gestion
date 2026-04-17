<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MobileMoneyService
{
    public static function sendPayment($data)
    {
        try {

            //Génération référence
            $transactionReference = 'TX-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

            // Payload propre
            $payload = [
                'transactionReference' => $transactionReference,
                'gatewayMode' => "1",
                'publicApiKey' => env('MAISHAPAY_PUBLIC_KEY_PROD'),
                'secretApiKey' => env('MAISHAPAY_SECRET_KEY_PROD'),
                'order' => [
                    'amount' => $data['total'],
                    'currency' => $data['devise'],
                    'customerFullName' => $data['nom_complet_client'],
                    'customerEmailAdress' => 'menji@example.com',
                ],
                'paymentChannel' => [
                    'channel' => 'MOBILEMONEY',
                    'provider' => $data['service'],
                    'walletID' => $data['numero_client'],
                    'callbackUrl' => config('app.url') . '/mobile_callback',
                ],
            ];

            //Appel API
            $response = Http::timeout(1000)
                ->retry(3, 2000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(env('MOBILE_MONEY_URL'), $payload);

            $responseData = $response->json();

            //Retour standard
            return [
                'status' => $response->successful(),
                'data' => $responseData,
                'reference' => $transactionReference
            ];

        } catch (Exception $e) {

            Log::error("Erreur MobileMoneyService: " . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}