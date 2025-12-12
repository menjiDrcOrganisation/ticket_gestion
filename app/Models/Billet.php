<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billet extends Model
{
    /** @use HasFactory<\Database\Factories\BilletFactory> */
    use HasFactory;
    protected   $fillable = [
       'date_achat',
       'nom_auteur',
       'numero',
       'email',
       'code_billet',
       'statut',
       'quantite', 
       'quantite_fictif',
       'evenement_id',
       'type_billet_id'
    ];

public function evenement()
{
    return $this->belongsTo(Evenement::class);
}

public function type_billet()
{
    return $this->belongsTo(TypeBillet::class);
}


public function evenementTypeBillet()
{
    return EvenementTypeBillet::where('evenement_id', $this->evenement_id)
        ->where('type_billet_id', $this->type_billet_id)
        ->first();
}


}
