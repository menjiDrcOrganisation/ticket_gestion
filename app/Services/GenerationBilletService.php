<?php

namespace App\Services;

use App\Jobs\RegenerateTicketPdfJob;
use App\Models\Billet;
use App\Models\EvenementTypeBillet;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Génération du billet d'une transaction payée : réservation du stock, création du billet
 * puis du PDF. Partagée par le callback de paiement et la génération forcée côté admin.
 */
class GenerationBilletService
{
    public function __construct(private readonly TicketPdfService $ticketPdfService)
    {
    }

    /**
     * Décrémente le stock du type de billet et crée le billet de la transaction.
     * À appeler dans une transaction DB, la transaction étant verrouillée (lockForUpdate).
     *
     * @throws Exception si le stock est introuvable ou insuffisant.
     */
    public function creerBillet(Transaction $transaction): Billet
    {
        $type = EvenementTypeBillet::lockForUpdate()
            ->where('type_billet_id', $transaction->type_billet_id)
            ->where('evenement_id', $transaction->evenement_id)
            ->first();

        if (!$type) {
            throw new Exception('Stock introuvable.');
        }

        if ($type->nombre_billet < $transaction->nombre_billet) {
            throw new Exception('Stock insuffisant au moment de la confirmation.');
        }

        $type->decrement('nombre_billet', (int) $transaction->nombre_billet);

        return Billet::create([
            'nom_auteur' => $transaction->nom_complet_client,
            'numero' => $transaction->numero_telephone,
            'billetImage' => '',
            'code_billet' => 'TCK-' . strtoupper(uniqid()),
            'evenement_id' => $transaction->evenement_id,
            'type_billet_id' => $transaction->type_billet_id,
            'quantite' => $transaction->nombre_billet,
            'quantite_fictif' => $transaction->nombre_billet,
            'statut' => 'valide',
            'date_achat' => now(),
        ]);
    }

    /**
     * Génère le PDF du billet et passe la transaction en « paye ».
     * En cas d'échec : « paye_sans_billet » et nouvelle tentative en file d'attente.
     */
    public function genererPdf(Transaction $transaction, Billet $billet): void
    {
        try {
            $this->ticketPdfService->generate(
                $billet,
                (float) $transaction->montant_unitaire,
                $transaction->devise,
                (float) $transaction->montant,
                $transaction->reference
            );

            $transaction->update([
                'statut' => 'paye',
            ]);
        } catch (Exception $e) {
            $transaction->update([
                'statut' => 'paye_sans_billet',
            ]);

            RegenerateTicketPdfJob::dispatch($transaction->id);

            Log::error('Erreur generation billet apres paiement.', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
