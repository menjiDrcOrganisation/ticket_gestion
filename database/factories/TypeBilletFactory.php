<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TypeBilletFactory extends Factory
{
    public function definition(): array
    {
        
        return [
            'nom_type' => $this->faker->unique()->randomElement(['Standard', 'VIP', 'VVIP', 'Premium']),
        ];
    }
}
