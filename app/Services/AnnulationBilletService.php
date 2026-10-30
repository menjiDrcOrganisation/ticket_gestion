<?php

namespace App\Services;

use App\Models\Billet;
use App\Models\EvenementTypeBillet;
use Illuminate\Support\Facades\DB;

/**
 * Annule un billet vendu : les places reviennent dans le stock de l'événement,
 * ce qui met à jour le taux de remplissage.
 */
class AnnulationBilletService
{
    /**
     * @return bool false si le billet était déjà annulé (rien n'est restitué deux fois).
     */
    public function annuler(Billet $billet): bool
    {
        return DB::transaction(function () use ($billet) {
            $billet = Billet::whereKey($billet->getKey())->lockForUpdate()->firstOrFail();

            if ($billet->estAnnule()) {
                return false;
            }

            $this->restituerStock($billet);

            $billet->update([
                'statut' => Billet::STATUT_ANNULE,
                'quantite_fictif' => 0,
            ]);

            return true;
        });
    }

    /** Remet les places du billet dans le stock, sans changer son statut (utilisé avant une suppression). */
    public function restituerStock(Billet $billet): void
    {
        if ($billet->estAnnule() || (int) $billet->quantite <= 0) {
            return;
        }

        EvenementTypeBillet::where('evenement_id', $billet->evenement_id)
            ->where('type_billet_id', $billet->type_billet_id)
            ->lockForUpdate()
            ->first()
            ?->increment('nombre_billet', (int) $billet->quantite);
    }
}
