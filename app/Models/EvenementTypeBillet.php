<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvenementTypeBillet extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementTypeBilletFactory> */
    use HasFactory;
     protected $fillable = [
        'evenement_id',
        'type_billet_id', 
         'nombre_billet',
         'prix_unitaire',
         'devise'
    ];
}
