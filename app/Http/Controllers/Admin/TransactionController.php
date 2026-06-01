<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Billet;
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
     * Forcer la génération du billet
     */
    public function forceGenerate($id)
    {
        DB::beginTransaction();

        try {

            $transaction = Transaction::findOrFail($id);

            // Vérifier si billet déjà généré
            if ($transaction->billet_id) {

                return back()->with(
                    'warning',
                    'Le billet existe déjà.'
                );
            }

            // Génération manuelle du billet
            $billet = Billet::create([
                'reference' => 'BLT-' . strtoupper(Str::random(10)),
                'transaction_id' => $transaction->id,
            ]);

            // Mise à jour transaction
            $transaction->update([
                'billet_id' => $billet->id,
                'statut' => 'completee',
            ]);

            Log::info(
                'Billet généré manuellement',
                [
                    'transaction_id' => $transaction->id,
                    'billet_id' => $billet->id,
                ]
            );

            DB::commit();

            return back()->with(
                'success',
                'Billet généré avec succès.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error(
                'Erreur génération billet',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return back()->with(
                'error',
                'Erreur lors de la génération.'
            );
        }
    }

    /**
     * Marquer comme remboursé
     */
    public function markAsRefunded($id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update([
            'type' => 'remboursement',
            'statut' => 'completee',
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