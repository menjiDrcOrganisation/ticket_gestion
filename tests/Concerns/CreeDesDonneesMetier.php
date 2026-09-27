<?php

namespace Tests\Concerns;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Organisateur;
use App\Models\Scanneur;
use App\Models\TypeBillet;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Fabriques de données métier partagées par les tests.
 */
trait CreeDesDonneesMetier
{
    protected function creerAdmin(array $attributs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin'], $attributs));
    }

    protected function creerOrganisateur(array $attributsUser = []): Organisateur
    {
        $user = User::factory()->create(array_merge(['role' => 'organisateur'], $attributsUser));

        return Organisateur::create(['user_id' => $user->id, 'telephone' => '0990000001']);
    }

    protected function creerScanneur(): Scanneur
    {
        $user = User::factory()->create(['role' => 'scanneur']);

        return Scanneur::create(['user_id' => $user->id]);
    }

    protected function creerTypeBillet(string $nom = 'Standard'): TypeBillet
    {
        return TypeBillet::create(['nom_type' => $nom]);
    }

    protected function creerEvenement(?Organisateur $organisateur = null, ?Scanneur $scanneur = null, array $attributs = []): Evenement
    {
        return Evenement::factory()->create(array_merge([
            'organisateur_id' => ($organisateur ?? $this->creerOrganisateur())->id,
            'scanneur_id' => $scanneur?->id,
            'url_evenement' => 'event-' . Str::lower(Str::random(8)),
            'statut' => 'encours',
        ], $attributs));
    }

    protected function ajouterStock(Evenement $evenement, TypeBillet $typeBillet, int $quantite, int $prix = 10, string $devise = 'USD'): EvenementTypeBillet
    {
        return EvenementTypeBillet::create([
            'evenement_id' => $evenement->id,
            'type_billet_id' => $typeBillet->id,
            'nombre_billet' => $quantite,
            'prix_unitaire' => $prix,
            'devise' => $devise,
        ]);
    }

    protected function vendreBillet(Evenement $evenement, TypeBillet $typeBillet, int $quantite, array $attributs = []): Billet
    {
        return Billet::create(array_merge([
            'date_achat' => now(),
            'nom_auteur' => 'Client Test',
            'numero' => '0990000002',
            'code_billet' => 'TCK-' . Str::upper(Str::random(10)),
            'billetImage' => '',
            'quantite' => $quantite,
            'quantite_fictif' => $quantite,
            'statut' => 'valide',
            'evenement_id' => $evenement->id,
            'type_billet_id' => $typeBillet->id,
        ], $attributs));
    }

    /**
     * Données valides du formulaire de création d'événement.
     */
    protected function donneesEvenement(TypeBillet $typeBillet, array $surcharges = []): array
    {
        $id = $typeBillet->id;

        return array_merge([
            'nom_evenement' => 'Concert de test',
            'type_evenement_nom' => 'Concert',
            'nom_organisateur' => 'Jean Organisateur',
            'email_organisateur' => 'orga@example.com',
            'telephone' => '0990000000',
            'adresse' => '1 avenue du Test',
            'salle' => 'Salle A',
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-01',
            'heure_debut' => '18:00',
            'heure_fin' => '23:00',
            'ticket_type_id' => [$id],
            'quantite' => [$id => 100],
            'prix' => [$id => 10],
            'devise' => [$id => 'USD'],
            'nom_artiste' => 'Artiste',
            'acroche' => 'Accroche',
            'a_propos' => 'A propos',
            'photo_affiche' => UploadedFile::fake()->image('affiche.jpg'),
        ], $surcharges);
    }
}
