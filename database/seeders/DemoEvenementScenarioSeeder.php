<?php

namespace Database\Seeders;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use App\Models\EvenementTypeBillet;
use App\Models\Organisateur;
use App\Models\Ressource;
use App\Models\Scanneur;
use App\Models\Transaction;
use App\Models\TypeBillet;
use App\Models\TypeEvenement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoEvenementScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $this->seedTypeEvenements();

        $organisateurs = $this->seedOrganisateurs();
        $scanneurs = $this->seedScanneurs();
        $ticketTypes = $this->seedTicketTypes();

        $events = [
            [
                'nom' => 'Kin Music Festival',
                'ville' => 'Kinshasa',
                'salle' => 'Palais du Peuple',
                'phrase' => 'La plus grande scene live de la capitale',
                'a_propos' => 'Festival multi-artistes avec performances live, DJ set et experience VIP.',
                'jours' => 5,
            ],
            [
                'nom' => 'Tech Summit RDC',
                'ville' => 'Kinshasa',
                'salle' => 'Centre Financier',
                'phrase' => 'Innovation, IA et entrepreneuriat',
                'a_propos' => 'Conference business et tech avec ateliers pratiques et sessions networking.',
                'jours' => 8,
            ],
            [
                'nom' => 'Urban Gospel Night',
                'ville' => 'Lubumbashi',
                'salle' => 'Stade Kibassa',
                'phrase' => 'Une nuit de louange et de celebration',
                'a_propos' => 'Concert gospel urbain avec line-up d artistes nationaux et internationaux.',
                'jours' => 12,
            ],
            [
                'nom' => 'Afro Food Experience',
                'ville' => 'Goma',
                'salle' => 'Esplanade du Lac',
                'phrase' => 'Cuisine, culture et ambiance',
                'a_propos' => 'Parcours gastronomique avec stands culinaires, shows live et masterclass chefs.',
                'jours' => 15,
            ],
        ];

        foreach ($events as $index => $eventData) {
            $organisateur = $organisateurs[$index % count($organisateurs)];
            $scanneur = $scanneurs[$index % count($scanneurs)];
            $startAt = Carbon::now()->addDays($eventData['jours'])->setTime(18, 0);
            $endAt = (clone $startAt)->addHours(6);

            $slug = Str::slug($eventData['nom']) . '-' . $startAt->format('Ymd');

            $event = Evenement::updateOrCreate(
                ['url_evenement' => $slug],
                [
                    'organisateur_id' => $organisateur->id,
                    'scanneur_id' => $scanneur->id,
                    'nom' => $eventData['nom'],
                    'date_debut' => $startAt,
                    'date_fin' => $endAt,
                    'adresse' => $eventData['ville'],
                    'salle' => $eventData['salle'],
                    'heure_debut' => $startAt->format('H:i:s'),
                    'heure_fin' => $endAt->format('H:i:s'),
                    'statut' => 'encours',
                ]
            );

            $ressource = Ressource::updateOrCreate(
                ['evenement_id' => $event->id],
                [
                    'nom_artiste' => 'Line-up ' . $eventData['nom'],
                    'phrase_accroche' => $eventData['phrase'],
                    'photo_affiche' => 'affiches/' . Str::slug($eventData['nom']) . '.jpg',
                    'a_propos' => $eventData['a_propos'],
                ]
            );

            DB::table('photo_ressources')->updateOrInsert(
                [
                    'ressource_id' => $ressource->id,
                    'nom' => 'cover-' . Str::slug($eventData['nom']) . '.jpg',
                ],
                [
                    'contenu' => 'galerie/' . Str::slug($eventData['nom']) . '/cover.jpg',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('photo_ressources')->updateOrInsert(
                [
                    'ressource_id' => $ressource->id,
                    'nom' => 'stage-' . Str::slug($eventData['nom']) . '.jpg',
                ],
                [
                    'contenu' => 'galerie/' . Str::slug($eventData['nom']) . '/stage.jpg',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            foreach ($ticketTypes as $ticketType) {
                $devise = $ticketType->nom_type === 'VIP' ? 'USD' : 'CDF';
                $prixUnitaire = match ($ticketType->nom_type) {
                    'VIP' => 60,
                    'Premium' => 45000,
                    default => 20000,
                };

                $stock = match ($ticketType->nom_type) {
                    'VIP' => 120,
                    'Premium' => 500,
                    default => 1500,
                };

                EvenementTypeBillet::updateOrCreate(
                    [
                        'evenement_id' => $event->id,
                        'type_billet_id' => $ticketType->id,
                    ],
                    [
                        'nombre_billet' => $stock,
                        'devise' => $devise,
                        'prix_unitaire' => $prixUnitaire,
                    ]
                );
            }

            $standardType = $ticketTypes->firstWhere('nom_type', 'Standard');

            if ($standardType) {
                $billetCode = 'SEED-' . $event->id . '-' . $standardType->id;

                $billet = Billet::updateOrCreate(
                    ['code_billet' => $billetCode],
                    [
                        'date_achat' => Carbon::now()->subDays(1),
                        'nom_auteur' => 'Client Demo',
                        'numero' => '+243990000000',
                        'email' => 'client.demo@example.com',
                        'statut' => 'valide',
                        'quantite' => 1,
                        'quantite_fictif' => 1,
                        'evenement_id' => $event->id,
                        'type_billet_id' => $standardType->id,
                    ]
                );

                EvenementBilletTypeBillet::updateOrCreate(
                    [
                        'evenement_id' => $event->id,
                        'billet_id' => $billet->id,
                        'type_billet_id' => $standardType->id,
                    ],
                    [
                        'statut' => 'valide',
                        'quantite' => 1,
                        'quantite_fictif' => 1,
                    ]
                );

                Transaction::updateOrCreate(
                    ['reference' => sprintf('CMD-SEED-%06d', $event->id)],
                    [
                        'montant' => 20000,
                        'montant_unitaire' => 20000,
                        'nombre_billet' => 1,
                        'numero_telephone' => '+243990000000',
                        'nom_complet_client' => 'Client Demo',
                        'statut' => 'en_attente',
                        'type' => 'paiement',
                        'methode_paiement' => 'AIRTEL',
                        'devise' => 'CDF',
                        'description' => 'Transaction seed de demonstration',
                        'billet_id' => $billet->id,
                        'evenement_id' => $event->id,
                        'type_billet_id' => $standardType->id,
                        'expires_at' => Carbon::now()->addMinutes(30),
                    ]
                );
            }
        }
    }

    private function seedTypeEvenements(): void
    {
        foreach (['Concert', 'Conference', 'Festival', 'Salon'] as $typeNom) {
            TypeEvenement::updateOrCreate([
                'nom_type' => $typeNom,
            ]);
        }
    }

    private function seedOrganisateurs()
    {
        $items = [
            ['name' => 'Organisateur Kin', 'email' => 'orga.kin@example.com', 'telephone' => '+243970000001'],
            ['name' => 'Organisateur Lshi', 'email' => 'orga.lshi@example.com', 'telephone' => '+243970000002'],
        ];

        $organisateurs = collect();

        foreach ($items as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'role' => 'organisateur',
                    'password' => Hash::make('password'),
                ]
            );

            $organisateurs->push(
                Organisateur::updateOrCreate(
                    ['user_id' => $user->id],
                    ['telephone' => $item['telephone']]
                )
            );
        }

        return $organisateurs;
    }

    private function seedScanneurs()
    {
        $items = [
            ['name' => 'Scanneur 1', 'email' => 'scan.1@example.com'],
            ['name' => 'Scanneur 2', 'email' => 'scan.2@example.com'],
        ];

        $scanneurs = collect();

        foreach ($items as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'role' => 'scanneur',
                    'password' => Hash::make('password'),
                ]
            );

            $scanneurs->push(
                Scanneur::updateOrCreate(
                    ['user_id' => $user->id],
                    []
                )
            );
        }

        return $scanneurs;
    }

    private function seedTicketTypes()
    {
        $types = collect(['Standard', 'Premium', 'VIP']);

        return $types->map(function (string $typeNom) {
            return TypeBillet::updateOrCreate(
                ['nom_type' => $typeNom],
                []
            );
        });
    }
}
