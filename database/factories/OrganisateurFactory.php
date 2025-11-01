<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class OrganisateurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Crée automatiquement un utilisateur associé
            'telephone' => $this->faker->phoneNumber(),
        ];
    }
}
