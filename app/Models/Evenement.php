<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TypeEvenement;

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

    public function billetsVendus(): int
    {
        return (int) $this->billets()->sum('quantite');
    }

    /**
     * Capacité totale = stock restant + billets vendus
     * (le stock evenement_type_billets.nombre_billet est décrémenté à chaque vente).
     */
    public function capaciteTotale(): int
    {
        $stockRestant = (int) EvenementTypeBillet::where('evenement_id', $this->id)->sum('nombre_billet');

        return $stockRestant + $this->billetsVendus();
    }

    public function tauxRemplissage(): float
    {
        return self::calculerTauxRemplissage($this->billetsVendus(), $this->capaciteTotale());
    }




}
