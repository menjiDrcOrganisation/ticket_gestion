<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BilletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantite_reelle' => $this->faker->numberBetween(1, 100),
            'quantite_fictive' => $this->faker->numberBetween(1, 100),
            'date_achat' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'nom_auteur' => $this->faker->name(),
            'numero' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'code_billet' => strtoupper($this->faker->bothify('BLT-####-????')),
        ];
    }
}
