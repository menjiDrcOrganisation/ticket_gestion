<?php
namespace App\Services;
use App\Models\Billet;
use App\Models\Tarif;
use App\Models\EvenementTypeBillet;
use App\Models\EvenementBilletTypeBillet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Exception;

class MobileMoneyService
{
    


public static function sendPayment($request)
{
    try {

        // 1. Taux de change
        $taux = app(\App\Services\ExchangeRateService::class)->getUSDtoCDF();

        // 2. Récupérer le type de billet
        $type_billet = EvenementTypeBillet::where('type_billet_id', $request['type_billet'])
            ->where('evenement_id', $request['id_evenement'])
            ->first();

        if (!$type_billet) {
            throw new Exception('Type de billet introuvable.');
        }

        // 3. Calcul du montant
        $montant = $type_billet->prix_unitaire;

        if ($type_billet->devise !== $request['devise']) {
            $montant = $request['devise'] === "CDF"
                ? $montant * $taux
                : $montant / $taux;
        }

        $total = $montant * $request['nombre_reel'];

        // 4. Vérifier stock
        if ($type_billet->nombre_billet < $request['nombre_reel']) {
            throw new Exception('Billets épuisés.');
        }

        // 5. Préparer données paiement
        $transactionReference = 'TX-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

        $payload = [
            'transactionReference' => $transactionReference,
            'gatewayMode' => "1",
            'publicApiKey' => env('MAISHAPAY_PUBLIC_KEY_PROD'),
            'secretApiKey' => env('MAISHAPAY_SECRET_KEY_PROD'),
            'order' => [
                'amount' => $total,
                'currency' => $request['devise'],
                'customerFullName' => $request['nom_complet_client'],
                'customerEmailAdress' => 'menji@example.com',
            ],
            'paymentChannel' => [
                'channel' => 'MOBILEMONEY',
                'provider' => $request['service'],
                'walletID' => $request['numero_client'],
                'callbackUrl' => config('app.url') . '/mobile_callback',
            ],
        ];

        // 6. Appel API
        $response = Http::timeout(400)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(env('MOBILE_MONEY_URL'), $payload);

        $responseData = $response->json();

        // 7. Vérifier succès
        if (
            $response->successful() &&
            ($responseData['transactionStatus'] ?? null) === 'SUCCESS'
        ) {

            $billet = DB::transaction(function () use ($type_billet, $request) {

                // Lock pour éviter conflits
                $type = EvenementTypeBillet::lockForUpdate()->find($type_billet->id);

                if ($type->nombre_billet < $request['nombre_reel']) {
                    throw new Exception('Stock insuffisant.');
                }

                // Décrément stock
                $type->decrement('nombre_billet', $request['nombre_reel']);

                // Génération code billet
                $code = 'TCK-' . strtoupper(uniqid()) . '-' . time();

                // Création billet
                return Billet::create([
                    'nom_auteur' => $request['nom_complet_client'],
                    'numero' => $request['numero_client'],
                    'code_billet' => $code,
                    'evenement_id' => $request['id_evenement'],
                    'type_billet_id' => $request['type_billet'],
                    'quantite_fictif' => $request['nombre_reel'],
                    'quantite' => $request['nombre_reel'],
                    'statut'=>"valide",
                    'date_achat' => Carbon::now()
            ]); });

            return [
                'status' => true,
                'message' => 'Paiement effectué avec succès.',
                'data_sup' => $billet->evenementTypeBillet(),
                'billet' => $billet->load(['evenement.ressource', 'type_billet']),
            ];
        }

        // Paiement échoué
        return [
            'status' => false,
            'message' => 'Paiement refusé ou en attente.',
            'data' => $responseData,
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
