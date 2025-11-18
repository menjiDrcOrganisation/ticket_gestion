<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retrait extends Model
{
    /** @use HasFactory<\Database\Factories\RetraitFactory> */
    use HasFactory;
    protected $fillable = [
        'organisateur_id',
        'nom_detenteur',
        'montant',
        'date',
        'statut',
    ];
    public function organisateur()
    {
        return $this->belongsTo(Organisateur::class);
    }
    
}
