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
        'montant_unitaire',
        'nombre_billet',
        'numero_telephone',
        'nom_complet_client',
        'statut',
        'type',
        'methode_paiement',
        'devise',
        'description',
        'billet_id',
        'evenement_id',
        'type_billet_id',
        'provider_reference',
        'gateway_reference',
        'callback_payload',
        'payment_started_at',
        'paid_at',
        'failed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'montant_unitaire' => 'decimal:2',
            'callback_payload' => 'array',
            'payment_started_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

      

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

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function typeBillet()
    {
        return $this->belongsTo(TypeBillet::class, 'type_billet_id');
    }
}
