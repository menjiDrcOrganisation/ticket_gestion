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
       'code_billet'
    
    ];

    public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'evenement_billet_type_billets')
                    ->withPivot('type_billet_id', 'quantite','quantite_fictif')
                    ->withTimestamps();
    }

    public function type_billet()
    {
        return $this->belongsToMany(TypeBillet::class, 'evenement_billet_type_billets')
                    ->withPivot('type_billet_id','evenement_id','statut','quantite','quantite_fictif')
                    ->withTimestamps();
    }

    



}
