<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ressource extends Model
{
    /** @use HasFactory<\Database\Factories\RessourceFactory> */
    use HasFactory;

       protected $fillable = [
        'nom_artiste',
        'phrase_accroche',
        'a_propos',
        'photo_affiche',
        'evenement_id'
    ];


    public function evenements()
    {
        return $this->belongsTo(Evenement::class);
        
    }
}


