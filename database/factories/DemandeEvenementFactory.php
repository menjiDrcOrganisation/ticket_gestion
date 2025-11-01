<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeEvenementFactory extends Factory
{
    protected $model = \App\Models\DemandeEvenement::class;

    public function definition()
    {
        return [
            'type_evenement' => $this->faker->randomElement([
                'Concert', 'Conférence', 'Atelier', 'Exposition', 'Séminaire'
            ]),
            'contact_organisateur' => $this->faker->name(),
            'nom_evenement' => $this->faker->sentence(3),
            'affiche' => null, // ou $this->faker->image('public/storage/affiches',400,300,null,false)
            'description' => $this->faker->paragraph(),
            'statut' => $this->faker->randomElement(['en_attente', 'valide', 'ferme']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
