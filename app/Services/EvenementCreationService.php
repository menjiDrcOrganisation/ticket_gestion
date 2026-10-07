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
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Crée un événement avec son organisateur et son scanneur, de façon atomique.
 *
 * - Cas 1 : l'e-mail de l'organisateur est inconnu → création du compte organisateur
 *   avec un mot de passe temporaire (changement obligatoire à la première connexion).
 * - Cas 2 : l'e-mail correspond déjà à un organisateur → le compte existant est réutilisé,
 *   aucune donnée n'est redemandée ni dupliquée.
 *
 * Dans les deux cas, un nouveau compte scanneur (identifiants uniques) est créé pour l'événement.
 */
class EvenementCreationService
{
    /**
     * @return array{
     *     evenement: Evenement,
     *     organisateur: ?Organisateur,
     *     organisateur_existant: bool,
     *     organisateur_code: ?string,
     *     scanneur_code: ?string,
     *     scanneur_email: ?string
     * }
     */
    public function create(array $validated, bool $createScanneur = true): array
    {
        // Le fichier est stocké une seule fois, hors transaction, puis supprimé si la création échoue.
        $imagePath = null;
        if (($validated['photo_affiche'] ?? null) instanceof UploadedFile) {
            $imagePath = $validated['photo_affiche']->store('affiches', 'public');
        }

        try {
            try {
                return $this->createInTransaction($validated, $createScanneur, $imagePath);
            } catch (UniqueConstraintViolationException $e) {
                // Course critique : un autre processus vient de créer le compte avec le même e-mail.
                // Au second passage, le compte existe et sera simplement réutilisé (cas 2).
                return $this->createInTransaction($validated, $createScanneur, $imagePath);
            }
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $e;
        }
    }

    public static function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    /**
     * Recherche l'utilisateur correspondant à un e-mail (insensible à la casse).
     */
    public static function findUserByEmail(?string $email): ?User
    {
        $email = self::normalizeEmail($email);

        if ($email === '') {
            return null;
        }

        return User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
    }

    private function createInTransaction(array $validated, bool $createScanneur, ?string $imagePath): array
    {
        return DB::transaction(function () use ($validated, $createScanneur, $imagePath) {
            $typeEvenementId = $this->resolveTypeEvenementId($validated);

            [$organisateur, $organisateurExistant, $organisateurCode] = $this->resolveOrganisateur($validated);

            $scanneur = null;
            $scanneurCode = null;
            $scanneurEmail = null;
            if ($createScanneur) {
                [$scanneur, $scanneurEmail, $scanneurCode] = $this->createScanneur($validated['nom_evenement']);
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

            if ($imagePath) {
                Ressource::create([
                    'nom_artiste' => $validated['nom_artiste'] ?? 'Artiste',
                    'phrase_accroche' => $validated['acroche'] ?? null,
                    'a_propos' => $validated['a_propos'] ?? null,
                    'photo_affiche' => $imagePath,
                    'evenement_id' => $evenement->id,
                ]);
            }

            $this->createTypeBillets($evenement, $validated);

            return [
                'evenement' => $evenement,
                'organisateur' => $organisateur,
                'organisateur_existant' => $organisateurExistant,
                'organisateur_code' => $organisateurCode,
                'scanneur_code' => $scanneurCode,
                'scanneur_email' => $scanneurEmail,
            ];
        });
    }

    private function resolveTypeEvenementId(array $validated): ?int
    {
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

        return $typeEvenementId ? (int) $typeEvenementId : null;
    }

    /**
     * Identifie l'organisateur par son e-mail, ou crée son compte s'il n'existe pas.
     *
     * @return array{0: ?Organisateur, 1: bool, 2: ?string} [organisateur, existant, mot de passe temporaire]
     */
    private function resolveOrganisateur(array $validated): array
    {
        $email = self::normalizeEmail($validated['email_organisateur'] ?? null);

        if ($email === '') {
            return [null, false, null];
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->lockForUpdate()
            ->first();

        // Cas 2 : organisateur existant → on réutilise son compte.
        if ($user) {
            if ($user->role !== 'organisateur') {
                throw ValidationException::withMessages([
                    'email_organisateur' => 'Cette adresse e-mail est déjà utilisée par un compte qui n\'est pas un organisateur.',
                ]);
            }

            $organisateur = Organisateur::firstOrCreate(
                ['user_id' => $user->id],
                ['telephone' => $validated['telephone'] ?? null]
            );

            // Complète uniquement une information manquante, sans écraser l'existant.
            if (empty($organisateur->telephone) && !empty($validated['telephone'])) {
                $organisateur->update(['telephone' => $validated['telephone']]);
            }

            return [$organisateur, true, null];
        }

        // Cas 1 : nouvel organisateur → création du compte avec mot de passe temporaire.
        $errors = [];
        if (empty($validated['nom_organisateur'])) {
            $errors['nom_organisateur'] = 'Le nom de l\'organisateur est obligatoire pour un nouveau compte.';
        }
        if (empty($validated['telephone'])) {
            $errors['telephone'] = 'Le téléphone de l\'organisateur est obligatoire pour un nouveau compte.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $motDePasseTemporaire = $this->generatePassword(12);

        $user = User::create([
            'email' => $email,
            'name' => $validated['nom_organisateur'],
            'password' => Hash::make($motDePasseTemporaire),
            'role' => 'organisateur',
            'must_change_password' => true,
        ]);

        $organisateur = Organisateur::create([
            'user_id' => $user->id,
            'telephone' => $validated['telephone'],
        ]);

        return [$organisateur, false, $motDePasseTemporaire];
    }

    /**
     * Crée un compte scanneur dédié à l'événement, avec un identifiant unique.
     *
     * @return array{0: Scanneur, 1: string, 2: string} [scanneur, e-mail, mot de passe]
     */
    private function createScanneur(string $nomEvenement): array
    {
        $scanneurCode = $this->generatePassword(10);
        $scanneurEmail = $this->generateUniqueScanneurEmail($nomEvenement);

        $userScanneur = User::create([
            'email' => $scanneurEmail,
            'name' => Str::limit('Scanneur - ' . $nomEvenement, 250, ''),
            'password' => Hash::make($scanneurCode),
            'role' => 'scanneur',
        ]);

        $scanneur = Scanneur::create([
            'user_id' => $userScanneur->id,
        ]);

        return [$scanneur, $scanneurEmail, $scanneurCode];
    }

    private function generateUniqueScanneurEmail(string $nomEvenement): string
    {
        $domain = (string) config('app.scanneur_email_domain', 'scanneur.kimiaticket.com');
        $slug = Str::limit(Str::slug($nomEvenement), 20, '');
        $prefix = 'scan-' . ($slug !== '' ? rtrim($slug, '-') . '-' : '');

        do {
            $email = $prefix . Str::lower(Str::random(6)) . '@' . $domain;
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    private function generatePassword(int $length): string
    {
        return Str::password($length, letters: true, numbers: true, symbols: false);
    }

    private function createTypeBillets(Evenement $evenement, array $validated): void
    {
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
