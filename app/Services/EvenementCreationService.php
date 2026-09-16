<?php

namespace App\Services;

use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Organisateur;
use App\Models\Ressource;
use App\Models\Scanneur;
use App\Models\TypeEvenement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EvenementCreationService
{
    /**
     * @return array{evenement: Evenement, organisateur_code: ?string, scanneur_code: ?string, scanneur_email: ?string}
     */
    public function create(array $validated, bool $createScanneur = true): array
    {
        $organisateurCode = null;
        $scanneurCode = null;
        $scanneurEmail = null;

        $evenement = DB::transaction(function () use ($validated, $createScanneur, &$organisateurCode, &$scanneurCode, &$scanneurEmail) {
            $typeEvenementId = $validated['type_evenement_id'] ?? null;
            $typeEvenementNom = trim((string) ($validated['type_evenement_nom'] ?? ''));

            if (!$typeEvenementId && $typeEvenementNom !== '') {
                $typeEvenement = TypeEvenement::query()
                    ->whereRaw('LOWER(nom_type) = ?', [mb_strtolower($typeEvenementNom)])
                    ->first();

                if (!$typeEvenement) {
                    $typeEvenement = TypeEvenement::create([
                        'nom_type' => $typeEvenementNom,
                    ]);
                }

                $typeEvenementId = $typeEvenement->id;
            }

            $organisateur = null;
            if (!empty($validated['email_organisateur']) && !empty($validated['nom_organisateur'])) {
                $organisateurCode = substr((string) Str::uuid(), 0, 10);

                $userOrganisateur = User::create([
                    'email' => $validated['email_organisateur'],
                    'name' => $validated['nom_organisateur'],
                    'password' => Hash::make($organisateurCode),
                    'role' => 'organisateur',
                ]);

                $organisateur = Organisateur::create([
                    'user_id' => $userOrganisateur->id,
                    'telephone' => $validated['telephone'] ?? null,
                ]);
            }

            $scanneur = null;
            if ($createScanneur) {
                $scanneurCode = substr((string) Str::uuid(), 0, 8);
                $scanneurEmail = uniqid('scan_', true) . '@gmail.com';

                $userScanneur = User::create([
                    'email' => $scanneurEmail,
                    'name' => 'Scanneur',
                    'password' => Hash::make($scanneurCode),
                    'role' => 'scanneur',
                ]);

                $scanneur = Scanneur::create([
                    'user_id' => $userScanneur->id,
                ]);
            }

            $evenement = Evenement::create([
                'nom' => $validated['nom_evenement'],
                'url_evenement' => $this->generateUniqueEventSlug($validated['nom_evenement']),
                'organisateur_id' => $organisateur?->id,
                'scanneur_id' => $scanneur?->id,
                'type_evenement_id' => $typeEvenementId,
                'adresse' => $validated['adresse'],
                'salle' => $validated['salle'],
                'date_debut' => Carbon::parse($validated['date_debut'] . ' ' . $validated['heure_debut']),
                'date_fin' => Carbon::parse($validated['date_fin'] . ' ' . $validated['heure_fin']),
                'heure_debut' => $validated['heure_debut'],
                'heure_fin' => $validated['heure_fin'],
                'statut' => 'encours',
            ]);

            if (!empty($validated['photo_affiche'])) {
                $imagePath = $validated['photo_affiche']->store('affiches', 'public');

                Ressource::create([
                    'nom_artiste' => $validated['nom_artiste'] ?? 'Artiste',
                    'phrase_accroche' => $validated['acroche'] ?? null,
                    'a_propos' => $validated['a_propos'] ?? null,
                    'photo_affiche' => $imagePath,
                    'evenement_id' => $evenement->id,
                ]);
            }

            $ticketCount = 0;
            foreach (($validated['ticket_type_id'] ?? []) as $index => $typeId) {
                $typeId = (int) $typeId;
                $quantite = (int) (
                    $validated['quantite'][$typeId]
                    ?? $validated['quantite'][$index]
                    ?? 0
                );
                $prix = (float) (
                    $validated['prix'][$typeId]
                    ?? $validated['prix'][$index]
                    ?? 0
                );
                $devise = strtoupper((string) (
                    $validated['devise'][$typeId]
                    ?? $validated['devise'][$index]
                    ?? 'CDF'
                ));

                if ($quantite > 0 && $prix > 0) {
                    EvenementTypeBillet::create([
                        'evenement_id' => $evenement->id,
                        'type_billet_id' => $typeId,
                        'nombre_billet' => $quantite,
                        'prix_unitaire' => $prix,
                        'devise' => $devise,
                    ]);
                    $ticketCount++;
                }
            }

            if ($ticketCount === 0) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Ajoutez au moins un type de billet avec une quantite et un prix superieurs a 0.',
                ]);
            }

            return $evenement;
        });

        return [
            'evenement' => $evenement,
            'organisateur_code' => $organisateurCode,
            'scanneur_code' => $scanneurCode,
            'scanneur_email' => $scanneurEmail,
        ];
    }

    private function generateUniqueEventSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'evenement';

        $slug = $baseSlug;
        $counter = 2;

        while (Evenement::where('url_evenement', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
