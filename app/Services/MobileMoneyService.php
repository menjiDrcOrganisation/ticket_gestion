<?php
namespace App\Services;

use App\Models\Billet;
use App\Models\Tarif;
use App\Models\EvenementTypeBillet;
use App\Models\EvenementBilletTypeBillet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MobileMoneyService
{
    public static function sendPayment($request)
    {
        try {
            // 1️⃣ Vérifier le type de billet
            $type_billet = EvenementTypeBillet::where('type_billet_id', $request['type_billet'])
                ->where('evenement_id', $request['id_evenement'])
                ->first();

            if (!$type_billet) {
                throw new Exception('Type de billet introuvable.');
            }

            if ($type_billet->nombre_billet < $request['nombre_reel']) {
                throw new Exception('Quantité insuffisante de billets disponibles.');
            }

            // 2️⃣ Préparer les données de transaction
            $data = [
                'transactionReference' => 'TX-' . date('YmdHis') . '-' . rand(1000, 9999),
                'amount'               => $type_billet->prix_unitaire * $request['nombre_reel'],
                'currency'             => $request['devise'],
                'customerFullName'     => $request['nom_complet_client'],
                'customerEmailAdress'  => 'menji@example.com',
                'provider'             => $request['service'],
                'walletID'             => $request['numero_client'],
                'callbackUrl'          => 'https://tondomaine.com/mobile_callback',
            ];

            // 3️⃣ Préparer le payload Maishapay
            $payload = [
                'transactionReference' => $data['transactionReference'],
                'gatewayMode' => "0",
                'publicApiKey' => env('MAISHAPAY_PUBLIC_KEY_TEST'),
                'secretApiKey' => env('MAISHAPAY_SECRET_KEY_TEST'),
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

            $response = Http::timeout(120)->withHeaders([
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->post(env('MOBILE_MONEY_URL'), $payload);

            $responseData = $response->json();

            if (
                $response->status() === 200 &&
                isset($responseData['transactionStatus']) &&
                $responseData['transactionStatus'] === 'SUCCESS'
            ) {
                // Générer un code de billet unique
                $now = time();
                $start1978 = strtotime('1978-01-01 00:00:00');
                $secondsSince1978 = $now - $start1978;
                $raw_code = 'Ticket-' . $request['nom_complet_client'] . '-' . $secondsSince1978 . '-' . uniqid();
                $code = Hash::make($raw_code);

                // Enregistrer le billet
                $billet = Billet::create([
                    'nom_auteur' => $request['nom_complet_client'],
                    'numero' => $request['numero_client'],
                    'code_billet' => $code,
                    'date_achat' => Carbon::now(),
                ]);

                // Diminuer le stock
                $type_billet->nombre_billet -= $request['nombre_reel'];
                $type_billet->save();

                // Lier billet et événement
                EvenementBilletTypeBillet::create([
                    'statut'=>"paye",
                    'billet_id' => $billet->id,
                    'evenement_id' => $request['id_evenement'],
                    'type_billet_id' => $request['type_billet'],
                    'quantite_fictif' => $request['nombre_reel'],
                    'quantite' => $request['nombre_reel']
                ]);

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
