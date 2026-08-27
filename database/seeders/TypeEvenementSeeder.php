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

            TypeEvenement::create(['nom_type' => $nomType]);
        }
    }
}
