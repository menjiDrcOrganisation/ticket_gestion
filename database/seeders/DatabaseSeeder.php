<?php

namespace Database\Seeders;

use App\Models\DemandeEvenement;
use App\Models\TypeEvenement;
use App\Models\Evenement;
use App\Models\User;
use App\Models\TypeBillet;
use App\Models\Admin;
use Database\Seeders\ActiveEventsBilletScenarioSeeder;
use Database\Seeders\DemoEvenementScenarioSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
     public function run(): void{

    $this->call(TypeEvenementSeeder::class);
    $this->call(SuperAdminSeeder::class);
   
  }

}
