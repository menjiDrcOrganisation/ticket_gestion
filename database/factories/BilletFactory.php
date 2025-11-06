<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BilletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date_achat' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'nom_auteur' => $this->faker->name(),
            'numero' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'code_billet' => strtoupper($this->faker->bothify('BLT-####-????')),
        ];
    }
}
