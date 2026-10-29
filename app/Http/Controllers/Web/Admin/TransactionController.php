<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\GenerationBilletService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Liste des transactions avec filtres avancés
     */
    public function index(Request $request)
    {
        $query = Transaction::query();

        // Recherche par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par email utilisateur
        if ($request->filled('email')) {
            $query->whereHas('billet.user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }

        // Recherche par référence / numéro de commande
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->reference . '%');
        }

        // Recherche par téléphone
        if ($request->filled('numero_telephone')) {
            $query->where(
                'numero_telephone',
                'like',
                '%' . $request->numero_telephone . '%'
            );
        }

        // Filtre date début
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        // Filtre date fin
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $transactions = $query
            ->with(['billet'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Voir les détails d'une transaction
     */
    public function show($id)
    {
        $transaction = Transaction::with([
            'billet',
        ])->findOrFail($id);

        // Timeline simulée
        $timeline = [
            [
                'titre' => 'Transaction créée',
                'date' => $transaction->created_at,
            ],
            [
                'titre' => 'Paiement initié',
                'date' => $transaction->created_at->addMinute(),
            ],
            [
                'titre' => 'Statut : ' . $transaction->statut,
                'date' => $transaction->updated_at,
            ],
        ];

        // Logs techniques simulés
        $logs = [
            'Gateway Response: SUCCESS',
            'API Payment ID: PAY_' . Str::random(8),
            'IP Client: 127.0.0.1',
            'Canal: ' . $transaction->methode_paiement,
        ];

        return view(
            'transactions.show',
            compact('transaction', 'timeline', 'logs')
        );
    }

    /**
     * Forcer la génération du billet d'une transaction restée sans billet
     * (ex. callback fournisseur jamais reçu) : même logique que le callback de paiement.
     */
    public function forceGenerate($id, GenerationBilletService $generationBilletService)
    {
        $transaction = null;
        $billet = null;

        try {
            // Renvoie [type, message] si la génération est refusée, null sinon.
            $refus = DB::transaction(function () use ($id, $generationBilletService, &$transaction, &$billet) {
                $transaction = Transaction::lockForUpdate()->findOrFail($id);

                // Vérifier si billet déjà généré
                if ($transaction->billet_id) {
                    return ['warning', 'Le billet existe déjà.'];
                }

                if ($transaction->type === 'remboursement' || $transaction->statut === 'annulee') {
                    return ['error', 'Impossible de générer un billet pour une transaction annulée ou remboursée.'];
                }

                if (!$transaction->evenement_id || !$transaction->type_billet_id || !$transaction->nombre_billet) {
                    return ['error', 'Transaction incomplète : événement, type ou nombre de billets manquant.'];
                }

                $billet = $generationBilletService->creerBillet($transaction);

                $transaction->update([
                    'billet_id' => $billet->id,
                    'paid_at' => $transaction->paid_at ?? now(),
                ]);

                return null;
            });
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error(
                'Erreur génération billet',
                [
                    'transaction_id' => $id,
                    'message' => $e->getMessage(),
                ]
            );

            return back()->with(
                'error',
                'Erreur lors de la génération : ' . $e->getMessage()
            );
        }

        if ($refus) {
            return back()->with(...$refus);
        }

        // Hors transaction DB : un échec du PDF ne doit pas annuler le billet (statut paye_sans_billet + relance).
        $generationBilletService->genererPdf($transaction, $billet);

        Log::info(
            'Billet généré manuellement',
            [
                'transaction_id' => $transaction->id,
                'billet_id' => $billet->id,
            ]
        );

        return back()->with(
            'success',
            'Billet généré avec succès.'
        );
    }

    /**
     * Marquer comme remboursé
     */
    public function markAsRefunded($id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update([
            'type' => 'remboursement',
            'statut' => 'annulee',
        ]);

        Log::info(
            'Transaction remboursée',
            [
                'transaction_id' => $transaction->id,
            ]
        );

        return back()->with(
            'success',
            'Transaction marquée comme remboursée.'
        );
    }
}




