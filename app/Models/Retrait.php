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
        'montant',
        'nom_detenteur',
        'date',
        'statut',
    ];
    public function organisateur()
    {
        return $this->belongsTo(Organisateur::class);
    }
    
}
