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
       'quantite',
       'date_achat',
       'statut'
    ];
    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }
    public function billet()
    {
        return $this->belongsTo(Billet::class, 'billet_id');
    }
    public function type_billet()
    {
        return $this->belongsTo(TypeBillet::class, 'type_billet_id');
    }
    
}
