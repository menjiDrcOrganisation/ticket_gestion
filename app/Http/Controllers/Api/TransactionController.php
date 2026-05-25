<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RegenerateTicketPdfJob;
use App\Models\Billet;
use App\Models\EvenementTypeBillet;
use App\Models\Transaction;
use App\Services\ExchangeRateService;
use App\Services\MobileMoneyService;
use App\Services\TicketPdfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly TicketPdfService $ticketPdfService
    ) {
    }

    public function initier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_billet' => 'required|integer',
            'nombre_reel' => 'required|integer|min:1',
            'nom_complet_client' => 'required|string|max:255',
            'numero_client' => 'required|string|max:25',
            'service' => 'required|string|max:50',
            'id_evenement' => 'required|integer',
            'devise' => 'required|string|in:CDF,USD',
        ]);

        $typeBillet = EvenementTypeBillet::with(['evenement', 'typeBillet'])
            ->where('type_billet_id', $validated['type_billet'])
            ->where('evenement_id', $validated['id_evenement'])
            ->first();

        if (!$typeBillet) {
            return response()->json([
                'status' => false,
                'message' => 'Type de billet introuvable pour cet evenement.',
            ], 404);
        }

        if ($typeBillet->nombre_billet < $validated['nombre_reel']) {
            return response()->json([
                'status' => false,
                'message' => 'Billets insuffisants.',
            ], 422);
        }

        [$montantUnitaireConverti, $total] = $this->calculerMontants(
            (float) $typeBillet->prix_unitaire,
            (string) $typeBillet->devise,
            $validated['devise'],
            (int) $validated['nombre_reel']
        );

        $transaction = DB::transaction(function () use ($validated, $typeBillet, $montantUnitaireConverti, $total): Transaction {
            $reference = $this->generateReadableReference();

            return Transaction::create([
                'reference' => $reference,
                'montant' => $total,
                'montant_unitaire' => $montantUnitaireConverti,
                'nombre_billet' => $validated['nombre_reel'],
                'numero_telephone' => $validated['numero_client'],
                'nom_complet_client' => $validated['nom_complet_client'],
                'statut' => 'en_attente',
                'type' => 'paiement',
                'methode_paiement' => $validated['service'],
                'devise' => $validated['devise'],
                'description' => 'Achat billet evenement',
                'evenement_id' => $validated['id_evenement'],
                'type_billet_id' => $validated['type_billet'],
                'expires_at' => now()->addMinutes(20),
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Transaction creee avec succes.',
            'transaction' => $this->buildRecap($transaction, $typeBillet),
        ], 201);
    }

    public function recapitulatif(string $reference): JsonResponse
    {
        $transaction = Transaction::with(['evenement', 'typeBillet'])
            ->where('reference', $reference)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'transaction' => $this->buildRecap($transaction),
            'can_pay' => in_array($transaction->statut, ['en_attente', 'echoue'], true),
        ]);
    }

    public function validerPaiement(string $reference): JsonResponse
    {
        $transaction = DB::transaction(function () use ($reference): Transaction {
            $locked = Transaction::with(['evenement', 'typeBillet'])
                ->lockForUpdate()
                ->where('reference', $reference)
                ->first();

            if (!$locked) {
                throw new Exception('NOT_FOUND');
            }

            if (in_array($locked->statut, ['paye', 'paye_sans_billet'], true)) {
                throw new Exception('ALREADY_PAID');
            }

            if (
                $locked->statut === 'paiement_en_cours'
                && $locked->payment_started_at
                && $locked->payment_started_at->gt(now()->subMinutes(5))
            ) {
                throw new Exception('ACTIVE_PAYMENT');
            }

            if ($locked->expires_at && $locked->expires_at->isPast()) {
                throw new Exception('EXPIRED');
            }

            $locked->update([
                'statut' => 'paiement_en_cours',
                'payment_started_at' => now(),
            ]);

            return $locked->fresh(['evenement', 'typeBillet']);
        });

        try {
            $payment = MobileMoneyService::initiatePayment([
                'transaction_reference' => $transaction->reference,
                'total' => (float) $transaction->montant,
                'devise' => $transaction->devise,
                'nom_complet_client' => $transaction->nom_complet_client,
                'numero_client' => $transaction->numero_telephone,
                'service' => $transaction->methode_paiement,
                'callback_url' => $this->resolveCallbackUrl(),
            ]);

            if (!$payment['status']) {
                $transaction->update([
                    'statut' => 'echoue',
                    'failed_at' => now(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Echec du lancement du paiement.',
                    'error' => $payment['message'] ?? null,
                ], 502);
            }

            $transaction->update([
                'provider_reference' => $payment['data']['reference']
                    ?? $payment['data']['transactionReference']
                    ?? null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Paiement lance. En attente du callback fournisseur.',
                'transaction_reference' => $transaction->reference,
                'gateway_response' => $payment['data'] ?? null,
            ]);
        } catch (Exception $exception) {
            if ($exception->getMessage() === 'NOT_FOUND') {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction introuvable.',
                ], 404);
            }

            if ($exception->getMessage() === 'ALREADY_PAID') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cette transaction est deja payee.',
                ], 409);
            }

            if ($exception->getMessage() === 'ACTIVE_PAYMENT') {
                return response()->json([
                    'status' => false,
                    'message' => 'Un paiement actif est deja en cours.',
                ], 409);
            }

            if ($exception->getMessage() === 'EXPIRED') {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction expiree. Veuillez recommencer.',
                ], 422);
            }

            Log::error('Erreur validerPaiement', ['error' => $exception->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur lors de la validation de paiement.',
            ], 500);
        }
    }

    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Callback paiement recu.', [
            'transactionReference' => $payload['transactionReference'] ?? null,
            'status' => $payload['transactionStatus'] ?? $payload['status'] ?? null,
            'has_signature_header' => $request->header('X-Signature') !== null,
        ]);

        if (!$this->isSignatureValid($request, $payload)) {
            return response()->json([
                'status' => false,
                'message' => 'Signature callback invalide.',
            ], 401);
        }

        $reference = (string) (
            $payload['transactionReference']
                ?? $payload['reference']
                ?? $payload['transaction_ref']
                ?? ''
        );

        if ($reference === '') {
            return response()->json([
                'status' => false,
                'message' => 'Reference transaction absente.',
            ], 422);
        }

        $transaction = Transaction::with(['evenement', 'typeBillet', 'billet'])
            ->where('reference', $reference)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction introuvable.',
            ], 404);
        }

        if (in_array($transaction->statut, ['paye', 'paye_sans_billet'], true)) {
            return response()->json([
                'status' => true,
                'message' => 'Transaction deja traitee.',
            ]);
        }

        $paidAmount = (float) ($payload['amount'] ?? $payload['order']['amount'] ?? 0);

        if (round($paidAmount, 2) !== round((float) $transaction->montant, 2)) {
            $transaction->update([
                'statut' => 'echoue',
                'failed_at' => now(),
                'callback_payload' => $payload,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Montant callback invalide.',
            ], 422);
        }

        $verified = MobileMoneyService::verifyPayment($transaction->reference, (float) $transaction->montant);

        if (
            !$verified['status']
            && $this->isLocalCallbackRelaxed()
            && $this->isSuccessfulStatus($payload['transactionStatus'] ?? $payload['status'] ?? null)
        ) {
            $verified = [
                'status' => true,
                'verified' => true,
                'data' => ['source' => 'local-relaxed-callback'],
            ];
        }

        if (!$verified['status']) {
            return response()->json([
                'status' => false,
                'message' => 'Verification serveur fournisseur indisponible.',
            ], 503);
        }

        if (!($verified['verified'] ?? false)) {
            $transaction->update([
                'statut' => 'echoue',
                'failed_at' => now(),
                'callback_payload' => $payload,
                'gateway_reference' => $payload['gatewayReference'] ?? null,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Paiement non confirme par le fournisseur.',
            ], 422);
        }

        $finalized = $this->finaliserPaiement($transaction, $payload);

        return response()->json($finalized);
    }

    public function confirmation(string $reference): JsonResponse
    {
        $transaction = Transaction::with('billet')
            ->where('reference', $reference)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'numero_commande' => $transaction->reference,
            'statut' => $transaction->statut,
            'download_url' => $transaction->billet && $transaction->billet->billetImage
                ? route('transactions.download', ['reference' => $transaction->reference])
                : null,
        ]);
    }

    public function telechargerBillet(string $reference)
    {
        $transaction = Transaction::with('billet')
            ->where('reference', $reference)
            ->first();

        if (!$transaction || !$transaction->billet || !$transaction->billet->billetImage) {
            return response()->json([
                'status' => false,
                'message' => 'Billet non disponible.',
            ], 404);
        }

        if (!in_array($transaction->statut, ['paye', 'paye_sans_billet'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Paiement non valide.',
            ], 422);
        }

        if (!Storage::disk('public')->exists($transaction->billet->billetImage)) {
            return response()->json([
                'status' => false,
                'message' => 'Fichier billet introuvable.',
            ], 404);
        }

        return Storage::disk('public')->download(
            $transaction->billet->billetImage,
            basename($transaction->billet->billetImage)
        );
    }

    private function buildRecap(Transaction $transaction, ?EvenementTypeBillet $typeBillet = null): array
    {
        $typeBillet ??= EvenementTypeBillet::with(['evenement', 'typeBillet'])
            ->where('type_billet_id', $transaction->type_billet_id)
            ->where('evenement_id', $transaction->evenement_id)
            ->first();

        return [
            'reference' => $transaction->reference,
            'statut' => $transaction->statut,
            'expires_at' => $transaction->expires_at?->toIso8601String(),
            'billet' => [
                'evenement' => $typeBillet?->evenement?->nom,
                'type' => $typeBillet?->typeBillet?->nom_type,
                'quantite' => $transaction->nombre_billet,
                'prix_unitaire' => (float) $transaction->montant_unitaire,
                'devise' => $transaction->devise,
                'montant_total' => (float) $transaction->montant,
            ],
        ];
    }

    private function calculerMontants(float $montantUnitaire, string $deviseBillet, string $deviseClient, int $quantite): array
    {
        $montantConverti = $montantUnitaire;

        if ($deviseBillet !== $deviseClient) {
            $taux = $this->exchangeRateService->getUSDtoCDF();

            if ($taux <= 0) {
                throw new Exception('Taux de change invalide.');
            }

            if ($deviseClient === 'CDF') {
                $montantConverti = $montantUnitaire * $taux;
            } else {
                $montantConverti = $montantUnitaire / $taux;
            }
        }

        $total = $montantConverti * $quantite;

        return [round($montantConverti, 2), round($total, 2)];
    }

    private function generateReadableReference(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = 'CMD-' . $datePart . '-';

        $lastReference = Transaction::whereDate('created_at', Carbon::today())
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('reference');

        $sequence = 1;

        if ($lastReference && str_starts_with($lastReference, $prefix)) {
            $sequence = ((int) substr($lastReference, -6)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    private function isSignatureValid(Request $request, array $payload): bool
    {
        if ($this->isLocalCallbackRelaxed()) {
            return true;
        }

        $signature = (string) ($request->header('X-Signature') ?? $payload['signature'] ?? '');
        $secret = (string) env('MOBILE_MONEY_CALLBACK_SECRET', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $rawPayload = $request->getContent();
        $expected = hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expected, $signature);
    }

    private function isLocalCallbackRelaxed(): bool
    {
        return app()->environment('local') && filter_var(env('MOBILE_MONEY_RELAXED_LOCAL_CALLBACK', false), FILTER_VALIDATE_BOOL);
    }

    private function isSuccessfulStatus(mixed $status): bool
    {
        return in_array(strtoupper((string) $status), ['SUCCESS', 'PAID', 'COMPLETED'], true);
    }

    private function resolveCallbackUrl(): string
    {
        $baseUrl = rtrim((string) env('MOBILE_MONEY_CALLBACK_BASE_URL', ''), '/');

        if ($baseUrl !== '') {
            return $baseUrl . '/api/transactions/callback';
        }

        return route('transactions.callback');
    }

    private function finaliserPaiement(Transaction $transaction, array $payload): array
    {
        $result = DB::transaction(function () use ($transaction, $payload): array {
            $locked = Transaction::with(['billet'])
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if (in_array($locked->statut, ['paye', 'paye_sans_billet'], true) && $locked->billet) {
                return [
                    'status' => true,
                    'message' => 'Transaction deja finalisee.',
                    'numero_commande' => $locked->reference,
                    'statut' => $locked->statut,
                    'download_url' => $locked->billet->billetImage
                        ? route('transactions.download', ['reference' => $locked->reference])
                        : null,
                ];
            }

            $type = EvenementTypeBillet::lockForUpdate()
                ->where('type_billet_id', $locked->type_billet_id)
                ->where('evenement_id', $locked->evenement_id)
                ->first();

            if (!$type) {
                throw new Exception('Stock introuvable.');
            }

            if ($type->nombre_billet < $locked->nombre_billet) {
                throw new Exception('Stock insuffisant au moment de la confirmation.');
            }

            $type->decrement('nombre_billet', (int) $locked->nombre_billet);

            $billet = Billet::create([
                'nom_auteur' => $locked->nom_complet_client,
                'numero' => $locked->numero_telephone,
                'code_billet' => 'TCK-' . strtoupper(uniqid()),
                'evenement_id' => $locked->evenement_id,
                'type_billet_id' => $locked->type_billet_id,
                'quantite' => $locked->nombre_billet,
                'quantite_fictif' => $locked->nombre_billet,
                'statut' => 'valide',
                'date_achat' => now(),
            ]);

            $locked->update([
                'billet_id' => $billet->id,
                'gateway_reference' => $payload['gatewayReference'] ?? $payload['transactionReference'] ?? null,
                'callback_payload' => $payload,
                'paid_at' => now(),
            ]);

            return [
                'billet' => $billet,
                'transaction' => $locked->fresh(),
            ];
        });

        if (isset($result['billet'])) {
            /** @var Billet $billet */
            $billet = $result['billet'];
            /** @var Transaction $updatedTransaction */
            $updatedTransaction = $result['transaction'];

            try {
                $this->ticketPdfService->generate(
                    $billet,
                    (float) $updatedTransaction->montant_unitaire,
                    $updatedTransaction->devise,
                    (float) $updatedTransaction->montant,
                    $updatedTransaction->reference
                );

                $updatedTransaction->update([
                    'statut' => 'paye',
                ]);
            } catch (Exception $e) {
                $updatedTransaction->update([
                    'statut' => 'paye_sans_billet',
                ]);

                RegenerateTicketPdfJob::dispatch($updatedTransaction->id);

                Log::error('Erreur generation billet apres paiement.', [
                    'transaction_id' => $updatedTransaction->id,
                    'reference' => $updatedTransaction->reference,
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'status' => true,
                'message' => 'Paiement confirme.',
                'numero_commande' => $updatedTransaction->reference,
                'statut' => $updatedTransaction->fresh()->statut,
                'download_url' => $billet->fresh()->billetImage
                    ? route('transactions.download', ['reference' => $updatedTransaction->reference])
                    : null,
            ];
        }

        return $result;
    }
}
