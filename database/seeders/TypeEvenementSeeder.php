<?php

namespace Database\Seeders;

use App\Models\TypeEvenement;
use Illuminate\Database\Seeder;

class TypeEvenementSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Concert',
            'Conference',
            'Festival',
            'Theatre',
            'Sport',
            'Exposition',
            'Atelier',
            'Gala',
            'Soiree',
            'Salon',
            'Masterclass',
            'Seminaire',
            'Projection',
            'Stand-up',
            'Networking',
        ];

        foreach ($catalog as $nomType) {
            if (TypeEvenement::where('nom_type', $nomType)->exists()) {
                continue;
            }

            // Keep model creation consistent with factory usage.
            $payload = TypeEvenement::factory()
                ->state(['nom_type' => $nomType])
                ->make()
                ->toArray();

            TypeEvenement::create($payload);
        }
    }
}
