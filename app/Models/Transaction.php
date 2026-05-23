<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'montant',
        'nombre_billet',
        'numero_telephone',
        'statut',
        'type',
        'methode_paiement',
        'devise',
        'description',
        'billet_id',
        'expires_at',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Relation avec le billet
     */
    public function billet()
    {
        return $this->belongsTo(Billet::class);
    }
}