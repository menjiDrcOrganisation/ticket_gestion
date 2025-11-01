<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeEvenement>
 */
class TypeEvenementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom_type' => $this->faker->randomElement([
                'Concert',
                'Conférence',
                'Festival',
                'Théâtre',
                'Sport',
                'Exposition',
                'Atelier',
                'Gala',
                'Soirée',
            ]),
        ];
    }
}
