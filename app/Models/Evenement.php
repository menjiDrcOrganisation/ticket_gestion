<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementFactory> */
    use HasFactory;

    public const STATUT_ENCOURS = 'encours';
    public const STATUT_FERME = 'ferme';

    protected $fillable = [
        'organisateur_id',
        'scanneur_id',
        'nom',
        'url_evenement',
        'date_debut',
        'date_fin',
        'adresse',
        'salle',
        'statut',
        'heure_debut',
        'heure_fin',
        'url_evenement'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $evenement) {
            if (! $evenement->isDirty('statut') || empty($evenement->statut)) {
                $evenement->statut = $evenement->calculateStatut();
            }
        });
    }

    protected function getStartDateTime(): ?\Carbon\Carbon
    {
        if (! $this->date_debut) {
            return null;
        }

        $startDate = $this->date_debut->copy()->startOfDay();

        if ($this->heure_debut) {
            return $startDate->setTimeFromTimeString($this->heure_debut);
        }

        return $this->date_debut;
    }

    protected function getEndDateTime(): ?\Carbon\Carbon
    {
        if (! $this->date_fin) {
            return null;
        }

        $endDate = $this->date_fin->copy()->startOfDay();

        if ($this->heure_fin) {
            return $endDate->setTimeFromTimeString($this->heure_fin);
        }

        return $this->date_fin;
    }

    public function calculateStatut(): string
    {
        $startDateTime = $this->getStartDateTime();
        $endDateTime = $this->getEndDateTime();

        if ($startDateTime && now()->lessThan($startDateTime)) {
            return 'avenir';
        }

        if ($endDateTime && now()->greaterThan($endDateTime)) {
            return self::STATUT_FERME;
        }

        return self::STATUT_ENCOURS;
    }

    public function getStatutAttribute(?string $value): string
    {
        if (! $this->date_debut || ! $this->date_fin) {
            return $value ?? self::STATUT_ENCOURS;
        }

        if (! empty($value) && in_array($value, [self::STATUT_ENCOURS, self::STATUT_FERME, 'avenir'], true)) {
            return $value;
        }

        return $this->calculateStatut();
    }

    public function scopeAvenir($query)
    {
        return $query->whereRaw("CONCAT(date_debut, ' ', heure_debut) > ?", [now()->format('Y-m-d H:i:s')]);
    }

    public function scopeEncours($query)
    {
        return $query->whereRaw("CONCAT(date_debut, ' ', heure_debut) <= ?", [now()->format('Y-m-d H:i:s')])
                     ->whereRaw("CONCAT(date_fin, ' ', heure_fin) >= ?", [now()->format('Y-m-d H:i:s')]);
    }

    public function scopeFerme($query)
    {
        return $query->whereRaw("CONCAT(date_fin, ' ', heure_fin) < ?", [now()->format('Y-m-d H:i:s')]);
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




}
