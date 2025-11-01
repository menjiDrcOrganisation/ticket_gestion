<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TypeBilletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom_type' => $this->faker->randomElement(['Standard', 'VIP', 'VVIP', 'Premium']),
        ];
    }
}
