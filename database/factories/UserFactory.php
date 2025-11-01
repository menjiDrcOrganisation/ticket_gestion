<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => fake()->randomElement(['admin', 'organisateur', 'scanneur']),
            'password' => Hash::make('password'), // mot de passe par défaut
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Crée un utilisateur admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin',
            'email' => 'admin@tick.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);
    }
}
