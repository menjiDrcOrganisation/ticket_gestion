<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_d_accueil_exige_une_connexion(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_la_page_d_accueil_redirige_selon_le_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $organisateur = User::factory()->create(['role' => 'organisateur']);

        $this->actingAs($admin)->get('/')->assertRedirect(route('dashboard.admin.viewDash'));
        $this->actingAs($organisateur)->get('/')->assertRedirect(route('dashboard_orginasateur.show'));
    }
}
