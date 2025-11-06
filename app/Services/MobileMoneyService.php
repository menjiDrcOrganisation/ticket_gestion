<?php
namespace App\Services;

use App\Models\Billet;
use App\Models\Tarif;
use App\Models\Type_billet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class MobileMoneyService
{
    public  static function sendPayment($request)
    {
        try {
            // 1️⃣ Vérifier le type de billet
            $type_billet = Type_billet::where('nom_type_billet', $request->type_billet)->first();
            if (!$type_billet) {
                throw new Exception('Type de billet introuvable.');
            }

            if ($type_billet->quantite_disponible < $request->nombre_reel) {
                throw new Exception('Quantité insuffisante de billets disponibles.');
            }

            // 2️⃣ Vérifier le tarif
            $tarif = Tarif::where('type_billet_id', $type_billet->id)
                        ->where('devise', $request->devise)
                        ->first();

            if (!$tarif) {
                throw new Exception('Tarif introuvable pour cette devise.');
            }

            // 3️⃣ Préparer les données de transaction
            $data = [
                'transactionReference' => 'TX-' . date('YmdHis') . '-' . rand(1000, 9999),
                'amount'               => $tarif->prix * $request->nombre_reel,
                'currency'             => $request->devise,
                'customerFullName'     => $request->nom_complet_client,
                'customerEmailAdress'  => 'menji@example.com',
                'provider'             => $request->service,
                'walletID'             => $request->numero_client,
                'callbackUrl'          => 'https://tondomaine.com/mobile_callback',
            ];

            // 4️⃣ Préparer le payload Maishapay
            $payload = [
                'transactionReference' => $data['transactionReference'],
                'gatewayMode' => "1",
                'publicApiKey' => env('MAISHAPAY_PUBLIC_KEY_PROD'),
                'secretApiKey' => env('MAISHAPAY_SECRET_KEY_PROD'),
                'order' => [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'customerFullName' => $data['customerFullName'],
                    'customerEmailAdress' => $data['customerEmailAdress'],
                ],
                'paymentChannel' => [
                    'channel' => 'MOBILEMONEY',
                    'provider' => $data['provider'],
                    'walletID' => $data['walletID'],
                    'callbackUrl' => $data['callbackUrl'],
                ],
            ];

            // 5️⃣ Envoi de la requête à Maishapay
            $response = Http::timeout(120)->withHeaders([
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->post(env('MOBILE_MONEY_URL'), $payload);

            $responseData = $response->json();

            // 6️⃣ Vérifier la réponse
            if (
                $response->status() === 200 &&
                isset($responseData['transactionStatus']) &&
                $responseData['transactionStatus'] === 'SUCCESS'
            ) {
                // Générer un code de billet unique
                $now = time();
                $start1978 = strtotime('1978-01-01 00:00:00');
                $secondsSince1978 = $now - $start1978;
                $raw_code = 'Ticket-' . $request->nom_complet_client . '-' . $secondsSince1978 . '-' . uniqid();
                $code = Hash::make($raw_code);

                // Enregistrer le billet
                $billet = Billet::create([
                    'nom_complet_client' => $request->nom_complet_client,
                    'numero_client'      => $request->numero_client,
                    'code_bilet'         => $code,
                    'occurance_billet'   => $request->nombre_reel,
                    'nombre_reel'        => $request->nombre_reel,
                    'type_billet_id'     => $type_billet->id,
                    'tarif_id'           => $tarif->id,
                    'moyen_achat'        => 'en_ligne'
                ]);

                // Diminuer le stock
                $type_billet->quantite_disponible -= $request->nombre_reel;
                $type_billet->save();

                return [
                    'status' => true,
                    'message' => 'Paiement effectué avec succès.',
                    'data' => $responseData,
                    'billet' => $billet,
                ];
            }

            return [
                'status' => false,
                'message' => 'Paiement refusé ou en attente.',
                'data' => $responseData,
            ];

        } catch (Exception $e) {
            Log::error("Erreur MobileMoneyService: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
