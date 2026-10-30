<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TypeEvenement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Evenement extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementFactory> */
    use HasFactory;

     protected $fillable = [
        'organisateur_id',
        'scanneur_id',
          'type_evenement_id',
        'nom',
        'url_evenement',
        'date_debut',
        'date_fin',
        'adresse',
        'salle',
        'statut',
        'heure_debut',
        'heure_fin',
        'url_evenement',
        'mail_send_attempts',
        'mail_sent_at',
        'last_mail_error',

    ];

    public function organisateur()
    {
        return $this->belongsTo(Organisateur::class);
    }

    public function typeBillets()
    {
        return $this->belongsToMany(TypeBillet::class, 'evenement_type_billets')
                    ->withPivot('nombre_billet')
                    ->withPivot('prix_unitaire')
                    ->withPivot('devise')
                    ->withTimestamps();
    }

     public function ressource()
    {
        return $this->hasMany(Ressource::class);
    }

    public function billets()
    {
        return $this->hasMany(Billet::class, 'evenement_id');
    }


    public function scanneur()
    {
        return $this->belongsTo(Scanneur::class);
    }

    public function typeEvenement()
    {
        return $this->belongsTo(TypeEvenement::class, 'type_evenement_id');
    }

    /**
     * Taux de remplissage en pourcentage (arrondi à 0,1), borné entre 0 et 100.
     */
    public static function calculerTauxRemplissage(int $billetsVendus, int $capaciteTotale): float
    {
        if ($capaciteTotale <= 0 || $billetsVendus <= 0) {
            return 0.0;
        }

        return round(min(100, ($billetsVendus / $capaciteTotale) * 100), 1);
    }

    /**
     * Billets vendus, hors billets annulés (remboursés).
     * Utilise la relation « billets » si elle est déjà chargée, pour éviter une requête par événement dans les listes.
     */
    public function billetsVendus(): int
    {
        if ($this->relationLoaded('billets')) {
            return (int) $this->billets->reject->estAnnule()->sum('quantite');
        }

        return (int) $this->billets()->nonAnnules()->sum('quantite');
    }

    /**
     * Capacité totale = stock restant + billets vendus
     * (le stock evenement_type_billets.nombre_billet est décrémenté à chaque vente
     * et réincrémenté à chaque annulation).
     */
    public function capaciteTotale(): int
    {
        $stockRestant = $this->relationLoaded('typeBillets')
            ? (int) $this->typeBillets->sum('pivot.nombre_billet')
            : (int) EvenementTypeBillet::where('evenement_id', $this->id)->sum('nombre_billet');

        return $stockRestant + $this->billetsVendus();
    }

    /** Faux tant qu'aucune billetterie (stock) n'a été définie pour l'événement. */
    public function aUneCapacite(): bool
    {
        return $this->capaciteTotale() > 0;
    }

    public function tauxRemplissage(): float
    {
        return self::calculerTauxRemplissage($this->billetsVendus(), $this->capaciteTotale());
    }

    /**
     * Évolution du remplissage jour par jour : ventes du jour, cumul et taux cumulé.
     *
     * @return Collection<int, array{date: Carbon, vendus: int, cumul: int, taux: float}>
     */
    public function evolutionRemplissage(): Collection
    {
        $billets = $this->relationLoaded('billets')
            ? $this->billets
            : $this->billets()->get(['quantite', 'statut', 'date_achat', 'created_at']);

        return self::construireEvolution($billets, $this->capaciteTotale());
    }

    /**
     * @param  iterable<Billet>  $billets
     * @return Collection<int, array{date: Carbon, vendus: int, cumul: int, taux: float}>
     */
    public static function construireEvolution(iterable $billets, int $capaciteTotale): Collection
    {
        $cumul = 0;

        return collect($billets)
            ->reject(fn (Billet $billet) => $billet->estAnnule())
            ->groupBy(fn (Billet $billet) => Carbon::parse($billet->date_achat ?? $billet->created_at)->toDateString())
            ->sortKeys()
            ->map(function (Collection $billetsDuJour, string $jour) use (&$cumul, $capaciteTotale) {
                $vendus = (int) $billetsDuJour->sum('quantite');
                $cumul += $vendus;

                return [
                    'date' => Carbon::parse($jour),
                    'vendus' => $vendus,
                    'cumul' => $cumul,
                    'taux' => self::calculerTauxRemplissage($cumul, $capaciteTotale),
                ];
            })
            ->values();
    }




}
