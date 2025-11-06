<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Evenement;
use App\Models\Billet;
use App\Models\TypeBillet;

class EvenementBilletTypeBilletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'evenement_id' => Evenement::factory(),
            'billet_id' => Billet::factory(),
            'type_billet_id' => TypeBillet::factory(),
            'statut' => $this->faker->randomElement(['disponible', 'épuisé', 'annulé']),
            'quantite' => 40,
            'quantite_fictif'=> 40,
        ];
    }
    // EvenementBilletTypeBillet.php
public function evenement() {
    return $this->belongsTo(Evenement::class);
}

public function billet() {
    return $this->belongsTo(Billet::class);
}

public function type_billet() {
    return $this->belongsTo(TypeBillet::class);
}

}
