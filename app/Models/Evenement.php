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




}
