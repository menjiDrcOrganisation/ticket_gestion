<?php

namespace Database\Seeders;

use App\Models\DemandeEvenement;
use App\Models\TypeEvenement;
use App\Models\Evenement;
use App\Models\User;
use App\Models\TypeBillet;
use App\Models\Admin;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
{
    
    User::factory()->state([
    'role' => 'admin',
    'password' => Hash::make('Admin12345')])->create();



    // TypeEvenement::factory(4)->create();
    // Evenement::factory(4)->create();
    // TypeBillet::factory(4)->create();

    // DemandeEvenement::factory(20)->create();
    
    // \App\Models\EvenementBilletTypeBillet::factory(10)->create();

    // Tu peux aussi générer d'autres utilisateurs
    //\App\Models\User::factory(5)->create();
}

}
