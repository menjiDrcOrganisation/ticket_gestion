<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementFactory> */
    use HasFactory;

     protected $fillable = [
        'organisateur_id',
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
        return $this->belongsToMany(Billet::class, 'evenement_billet_type_billet')
                    ->withPivot('type_billet_id', 'nombre_billet')
                    ->withTimestamps();
    }

    public function scanneur()
    {
        return $this->belongsTo(Scanneur::class);
    }




}
