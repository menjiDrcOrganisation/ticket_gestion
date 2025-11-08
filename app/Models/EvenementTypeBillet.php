<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvenementTypeBillet extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementTypeBilletFactory> */
    use HasFactory;
     protected $fillable = [
        'type_billet_id',
        'evenement_id',
        'type_billet_id', 
         'nombre_billet',
         'prix_unitaire',
         'devise'
    ];
    function evenement()
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }
    function type_billet()
    {
        return $this->belongsTo(TypeBillet::class, 'type_billet_id');
    }
}
