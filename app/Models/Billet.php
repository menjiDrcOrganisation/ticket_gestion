<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Billet extends Model
{
    /** @use HasFactory<\Database\Factories\BilletFactory> */
    use HasFactory;

    private static ?string $billetImageColumn = null;

    protected   $fillable = [
       'date_achat',
       'nom_auteur',
       'numero',
       'email',
       'billetImage',
       'billet_image',
       'code_billet',
       'statut',
       'quantite', 
       'quantite_fictif',
       'evenement_id',
       'type_billet_id'
    ];

public function getBilletImageAttribute($value)
{
    if (!empty($value)) {
        return $value;
    }

    return $this->attributes['billet_image'] ?? null;
}

public function setBilletImageAttribute($value): void
{
    $column = $this->resolveBilletImageColumn();
    $this->attributes[$column] = $value;
}

private function resolveBilletImageColumn(): string
{
    if (self::$billetImageColumn !== null) {
        return self::$billetImageColumn;
    }

    self::$billetImageColumn = Schema::hasColumn('billets', 'billetImage')
        ? 'billetImage'
        : 'billet_image';

    return self::$billetImageColumn;
}

public function evenement()
{
    return $this->belongsTo(Evenement::class);
}

public function type_billet()
{
    return $this->belongsTo(TypeBillet::class);
}

public function evenementTypeBilletRelation()
{
    return $this->hasOne(EvenementTypeBillet::class, 'type_billet_id', 'type_billet_id');
}


public function evenementTypeBillet()
{
    return $this->evenementTypeBilletRelation()
        ->where('evenement_id', $this->evenement_id)
        ->first();
}

public function transactions()
{
    return $this->hasMany(Transaction::class);
}


}
