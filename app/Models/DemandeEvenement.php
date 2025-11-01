<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeEvenement extends Model
{
    /** @use HasFactory<\Database\Factories\DemandeEvenementFactory> */
    use HasFactory;
    protected     $fillable = [
       'type_evenement',
       'contact_organisateur',
       'nom_evenement',
       'affiche',
       'description',
       'statut'
    ]; 
    
    function typeEvenement()
    {
        return $this->belongsTo(TypeEvenement::class);
    }
}
