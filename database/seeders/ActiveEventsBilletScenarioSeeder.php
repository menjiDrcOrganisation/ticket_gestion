<?php

namespace Database\Seeders;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use Illuminate\Database\Seeder;

class ActiveEventsBilletScenarioSeeder extends Seeder
{
    /**
     * Cree un billet seed pour chaque type de billet de chaque evenement actif.
     */
    public function run(): void
    {
        $activeEvents = Evenement::query()
            ->whereIn('statut', ['encours', 'actif'])
            ->with('typeBillets')
            ->get();

        foreach ($activeEvents as $event) {
            foreach ($event->typeBillets as $typeBillet) {
                $stock = (int) ($typeBillet->pivot->nombre_billet ?? 1);
                $quantite = max(1, min(3, $stock));

                $seedCode = sprintf('SEED-ACTIF-%d-%d', $event->id, $typeBillet->id);
                $buyerSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $typeBillet->nom_type));
                $buyerSlug = trim($buyerSlug, '.');

                $billet = Billet::updateOrCreate(
                    ['code_billet' => $seedCode],
                    [
                        'date_achat' => now()->subHours(2),
                        'nom_auteur' => 'Client Seed ' . $typeBillet->nom_type,
                        'numero' => '+243970000' . str_pad((string) $typeBillet->id, 3, '0', STR_PAD_LEFT),
                        'email' => sprintf('seed.%s.event%d@example.com', $buyerSlug ?: 'client', $event->id),
                        'statut' => 'valide',
                        'quantite' => $quantite,
                        'quantite_fictif' => $quantite,
                        'evenement_id' => $event->id,
                        'type_billet_id' => $typeBillet->id,
                    ]
                );

                EvenementBilletTypeBillet::updateOrCreate(
                    [
                        'evenement_id' => $event->id,
                        'billet_id' => $billet->id,
                        'type_billet_id' => $typeBillet->id,
                    ],
                    [
                        'statut' => 'valide',
                        'quantite' => $quantite,
                        'quantite_fictif' => $quantite,
                    ]
                );
            }
        }
    }
}
