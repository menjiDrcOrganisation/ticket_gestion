<?php

namespace Database\Factories;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

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
