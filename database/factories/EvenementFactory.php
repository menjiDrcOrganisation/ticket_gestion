<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organisateur;
use App\Models\Scanneur;

class EvenementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organisateur_id' => Organisateur::factory(),
            'scanneur_id' => null,
            'url_evenement' => $this->faker->lexify('event-????'),
            'nom' => $this->faker->sentence(3),
            'date_debut' => $this->faker->dateTimeBetween('now', '+2 months'),
            'date_fin' => $this->faker->dateTimeBetween('+2 months', '+4 months'),
            'adresse' => $this->faker->address(),
            'salle' => $this->faker->word(),
            'heure_debut' => $this->faker->time(),
            'heure_fin' => $this->faker->time(),
            'statut' => $this->faker->randomElement(['encours', 'ferme']),
        ];
    }
}
