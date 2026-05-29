<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MobileMoneyService
{
    private static function gatewayMode(): string
    {
        return (string) env('MOBILE_MONEY_GATEWAY_MODE', app()->environment('local') ? '0' : '1');
    }

    private static function credentialSet(): array
    {
        $mode = self::gatewayMode();

        if ($mode === '0') {
            return [
                'public' => env('MAISHAPAY_PUBLIC_KEY_TEST'),
                'secret' => env('MAISHAPAY_SECRET_KEY_TEST'),
            ];
        }

        return [
            'public' => env('MAISHAPAY_PUBLIC_KEY_PROD'),
            'secret' => env('MAISHAPAY_SECRET_KEY_PROD'),
        ];
    }

    public static function initiatePayment(array $data): array
    {
        try {
            $credentials = self::credentialSet();
            $transactionReference = $data['transaction_reference']
                ?? ('TX-' . now()->format('YmdHis') . '-' . rand(1000, 9999));

            $payload = [
                'transactionReference' => $transactionReference,
                'gatewayMode' => self::gatewayMode(),
                'publicApiKey' => $credentials['public'],
                'secretApiKey' => $credentials['secret'],
                'order' => [
                    'amount' => $data['total'],
                    'currency' => $data['devise'],
                    'customerFullName' => $data['nom_complet_client'],
                    'customerEmailAdress' => $data['customer_email'] ?? 'client@example.com',
                ],
                'paymentChannel' => [
                    'channel' => 'MOBILEMONEY',
                    'provider' => $data['service'],
                    'walletID' => $data['numero_client'],
                    'callbackUrl' => $data['callback_url'] ?? (config('app.url') . '/api/transactions/callback'),
                ],
            ];

            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(env('MOBILE_MONEY_URL'), $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $responseData,
                    'reference' => $transactionReference,
                    'uncertain' => false,
                ];
            }

            $httpCode = $response->status();
            $isDefinitiveClientError =
                $httpCode >= 400
                && $httpCode < 500
                && !in_array($httpCode, [408, 429], true);
            $providerMessage = $responseData['errors']['message']
                ?? $responseData['title']
                ?? 'Erreur fournisseur lors de l\'initiation du paiement.';

            return [
                'status' => false,
                'data' => $responseData,
                'reference' => $transactionReference,
                'message' => is_array($providerMessage) ? json_encode($providerMessage) : (string) $providerMessage,
                'http_code' => $httpCode,
                'uncertain' => !$isDefinitiveClientError,
            ];
        } catch (Exception $e) {
            Log::error("Erreur MobileMoneyService initiation: " . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'uncertain' => true,
            ];
        }
    }

    public static function sendPayment($data)
    {
        return self::initiatePayment($data);
    }

    public static function verifyPayment(string $transactionReference, ?float $expectedAmount = null): array
    {
        $verifyUrl = env('MOBILE_MONEY_VERIFY_URL');

        if (empty($verifyUrl)) {
            if (app()->environment('local')) {
                return [
                    'status' => false,
                    'message' => 'MOBILE_MONEY_VERIFY_URL not configured (local).',
                ];
            }

            return [
                'status' => false,
                'message' => 'MOBILE_MONEY_VERIFY_URL not configured.',
            ];
        }

        try {
            $credentials = self::credentialSet();
            $payload = [
                'transactionReference' => $transactionReference,
                'publicApiKey' => $credentials['public'],
                'secretApiKey' => $credentials['secret'],
            ];

            if ($expectedAmount !== null) {
                $payload['amount'] = $expectedAmount;
            }

            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($verifyUrl, $payload);

            $responseData = $response->json();
            $status = $responseData['transactionStatus']
                ?? $responseData['status']
                ?? null;

            return [
                'status' => $response->successful(),
                'verified' => in_array(strtoupper((string) $status), ['SUCCESS', 'PAID', 'COMPLETED'], true),
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error("Erreur MobileMoneyService verification: " . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}