<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvenementBilletTypeBillet extends Model
{
    /** @use HasFactory<\Database\Factories\EvenementBilletTypeBilletFactory> */
    use HasFactory;
    protected     $fillable = [
       'evenement_id',
       'billet_id',
       'type_billet_id',
       'quantite_fictif',
       'quantite'
    ];
}
