<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'mail_send_attempts',
        'mail_sent_at',
        'mail_queued_at',
        'last_mail_error',
    ];

    protected function casts(): array
    {
        return [
            'mail_sent_at' => 'datetime',
            'mail_queued_at' => 'datetime',
        ];
    }

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
     * Nombre de billets vendus, hors billets annulés ou remboursés.
     */
    public function billetsVendus(): int
    {
        if ($this->relationLoaded('billets')) {
            return (int) $this->billets
                ->reject(fn (Billet $billet) => $billet->estAnnule())
                ->sum('quantite');
        }

        return (int) $this->billets()->nonAnnules()->sum('quantite');
    }

    /**
     * Capacité totale = stock restant + billets vendus.
     */
    public function capaciteTotale(): int
    {
        $stockRestant = $this->relationLoaded('typeBillets')
            ? (int) $this->typeBillets->sum('pivot.nombre_billet')
            : (int) EvenementTypeBillet::where('evenement_id', $this->id)
                ->sum('nombre_billet');

        return $stockRestant + $this->billetsVendus();
    }

    /**
     * Indique si une capacité a été définie pour l'événement.
     */
    public function aUneCapacite(): bool
    {
        return $this->capaciteTotale() > 0;
    }

    public function tauxRemplissage(): float
    {
        return self::calculerTauxRemplissage(
            $this->billetsVendus(),
            $this->capaciteTotale()
        );
    }

    /**
     * Retourne les ventes non annulées par jour, avec leur cumul et leur taux.
     *
     * @return array<int, array{date: Carbon, vendus: int, cumul: int, taux: float}>
     */
    public function evolutionRemplissage(): array
    {
        $billetsParJour = $this->billets()
            ->nonAnnules()
            ->whereNotNull('date_achat')
            ->get()
            ->groupBy(
                fn (Billet $billet) => Carbon::parse($billet->date_achat)->toDateString()
            )
            ->sortKeys();

        $capacite = $this->capaciteTotale();
        $cumul = 0;
        $evolution = [];

        foreach ($billetsParJour as $jour => $billets) {
            $vendus = (int) $billets->sum('quantite');
            $cumul += $vendus;

            $evolution[] = [
                'date' => Carbon::parse($jour),
                'vendus' => $vendus,
                'cumul' => $cumul,
                'taux' => self::calculerTauxRemplissage($cumul, $capacite),
            ];
        }

        return $evolution;
    }
}
