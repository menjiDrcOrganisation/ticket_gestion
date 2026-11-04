<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class OrganisateurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state([
                'role' => 'organisateur',
                'password' => Hash::make('Organi12345'),

            ]),
            'telephone' => $this->faker->phoneNumber(),
        ];
    }
}
