<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MobileMoneyService;
use App\Services\ExchangeRateService;
use App\Models\EvenementTypeBillet;
use App\Models\Billet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class BilletController extends Controller
{
    private ExchangeRateService $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function achatbillet(Request $request)
    {
        try {

            // =========================
            // VALIDATION
            // =========================
            $validated = $request->validate([
                'type_billet' => 'required|string',
                'nombre_reel' => 'required|integer|min:1',
                'nom_complet_client' => 'required|string',
                'numero_client' => 'required|string',
                'service' => 'required|string',
                'id_evenement' => 'required|string',
                'devise' => 'required|string' // CDF ou USD
            ]);

            // =========================
            // GET TYPE BILLET
            // =========================
            $type_billet = EvenementTypeBillet::where('type_billet_id', $validated['type_billet'])
                ->where('evenement_id', $validated['id_evenement'])
                ->first();

            if (!$type_billet) {
                throw new Exception("Type de billet introuvable.");
            }

            if ($type_billet->nombre_billet < $validated['nombre_reel']) {
                throw new Exception("Billets épuisés.");
            }

            // =========================
            // MONTANT BASE
            // =========================
            $montantUnitaire = (float) $type_billet->prix_unitaire;
            $deviseBillet = $type_billet->devise;
            $deviseClient = $validated['devise'];

            // =========================
            // TAUX DE CHANGE (USD -> CDF)
            // =========================
            $taux = $this->exchangeRateService->getUSDtoCDF();

            if ($taux <= 0) {
                throw new Exception("Taux de change invalide.");
            }

            // =========================
            // CONVERSION
            // =========================
            $montantConverti = $montantUnitaire;

            if ($deviseBillet !== $deviseClient) {

                if ($deviseClient === "CDF") {
                    $montantConverti = $montantUnitaire * $taux; // USD → CDF
                } else {
                    $montantConverti = $montantUnitaire / $taux; // CDF → USD
                }
            }

            // =========================
            // TOTAL
            // =========================
            $total = $montantConverti * $validated['nombre_reel'];

            // =========================
            // PAIEMENT (IMPORTANT : utiliser montant)
            // =========================
            $paymentPayload = array_merge($validated, [
                'total' => $total
            ]);

            $payment = MobileMoneyService::sendPayment($paymentPayload);

            if (!$payment['status']) {
                return response()->json($payment, 400);
            }

            $transactionStatus = $payment['data']['transactionStatus'] ?? null;

            if ($transactionStatus !== 'SUCCESS') {
                return response()->json([
                    'status' => false,
                    'message' => 'Paiement non validé.',
                    'data' => $payment['data']
                ], 400);
            }

            // =========================
            // DB TRANSACTION
            // =========================
            $billet = DB::transaction(function () use ($validated) {

                $type = EvenementTypeBillet::lockForUpdate()
                    ->where('type_billet_id', $validated['type_billet'])
                    ->where('evenement_id', $validated['id_evenement'])
                    ->first();

                if ($type->nombre_billet < $validated['nombre_reel']) {
                    throw new Exception("Stock insuffisant.");
                }

                $type->decrement('nombre_billet', $validated['nombre_reel']);

                $code = 'TCK-' . strtoupper(uniqid());

                return Billet::create([
                    'nom_auteur' => $validated['nom_complet_client'],
                    'numero' => $validated['numero_client'],
                    'code_billet' => $code,
                    'evenement_id' => $validated['id_evenement'],
                    'type_billet_id' => $validated['type_billet'],
                    'quantite' => $validated['nombre_reel'],
                    'quantite_fictif' => $validated['nombre_reel'],
                    'statut' => "valide",
                    'date_achat' => Carbon::now()
                ]);
            });

            $billet->load(['evenement.ressource', 'type_billet']);

            // =========================
            // PDF DATA
            // =========================
            $data = [
                'ticket' => [
                    'user_name' => $billet->nom_auteur,
                    'event_name' => $billet->evenement->nom ?? 'Evenement',
                    'location' => $billet->evenement->adresse ?? 'Non définie',

                    'type' => $billet->type_billet->nom_type ?? 'Standard',
                    'quantity' => $billet->quantite,

                    'price' => number_format($montantConverti, 2, ',', ' '),
                    'devise' => $deviseClient,
                    'total' => number_format($total, 2, ',', ' '),

                    'qrcode_url' => 'https://quickchart.io/qr?text=' . $billet->code_billet,
                    'purchase_date' => Carbon::parse($billet->date_achat)->format('d/m/Y H:i'),
                    'event_date' => Carbon::parse($billet->evenement->date_debut)->format('d/m/Y'),
                    'event_time' => Carbon::parse($billet->evenement->heure_debut)->format('H:i'),

                    'photo_affiche' => $billet->evenement->ressource[0]->photo_affiche ?? null,

                    'ticket_id' => strtoupper(substr(md5($billet->code_billet), 0, 8)),
                    'transaction_ref' => $billet->code_billet
                ]
            ];

            // =========================
            // PDF GENERATION
            // =========================
            $pdf = Pdf::loadView('billetPdf.billet', $data);

            $fileName = 'billets/' . $billet->nom_auteur . '.pdf';

            Storage::disk('public')->put($fileName, $pdf->output());

            $billet->update([
                'billetImage' => $fileName
            ]);

            // =========================
            // RESPONSE
            // =========================
            return response()->json([
                'status' => true,
                'message' => 'Paiement effectué avec succès.',
                'billet' => $billet,
                'pdf_url' => asset('storage/' . $fileName),
                'reference' => $payment['reference'] ?? null
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}