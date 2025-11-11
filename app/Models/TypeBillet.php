<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeBillet extends Model
{
    /** @use HasFactory<\Database\Factories\TypeBilletFactory> */
    use HasFactory;
    protected $fillable = [
        'nom_type',
    ];


    function evenement_type_billet()
    {
        return $this->hasMany(EvenementTypeBillet::class, 'type_billet_id');
    } 
    
     public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'evenement_type_billets')
                    ->withPivot('nombre_billet', 'devise','prix_unitaire')
              ->withTimestamps();
    }
    
    
    
}
