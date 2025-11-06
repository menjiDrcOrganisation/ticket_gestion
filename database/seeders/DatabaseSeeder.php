<?php

namespace Database\Seeders;

use App\Models\DemandeEvenement;
use App\Models\TypeEvenement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
{
    User::factory()->admin()->create();
    
    TypeEvenement::factory(4)->create();

    DemandeEvenement::factory(20)->create();
     \App\Models\EvenementBilletTypeBillet::factory(10)->create();
    // Tu peux aussi générer d'autres utilisateurs
    //\App\Models\User::factory(5)->create();
}

}
