<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_routes_api_sont_limitees_et_renvoient_429_en_json(): void
    {
        config(['ratelimit.api' => 3]);

        $this->getJson('/api/v1/evenements')
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', 3)
            ->assertHeader('X-RateLimit-Remaining', 2);
        $this->getJson('/api/v1/evenements')->assertOk();
        $this->getJson('/api/v1/evenements')->assertOk();

        // Sans en-tête Accept : la réponse reste du JSON, avec le délai avant de pouvoir réessayer.
        $this->get('/api/v1/evenements')
            ->assertStatus(429)
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Retry-After')
            ->assertJsonPath('success', false);
    }

    public function test_le_compteur_est_propre_a_chaque_adresse_ip(): void
    {
        config(['ratelimit.api' => 1]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->getJson('/api/v1/evenements')->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->getJson('/api/v1/evenements')->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->getJson('/api/v1/evenements')->assertOk();
    }

    public function test_l_acces_est_retabli_apres_la_fenetre_d_une_minute(): void
    {
        config(['ratelimit.api' => 1]);

        $this->getJson('/api/v1/evenements')->assertOk();
        $this->getJson('/api/v1/evenements')->assertStatus(429);

        $this->travel(61)->seconds();

        $this->getJson('/api/v1/evenements')->assertOk();
    }

    public function test_la_creation_d_evenement_a_un_plafond_plus_strict_partage_par_ses_deux_routes(): void
    {
        config(['ratelimit.creation_evenement' => 2]);

        // Données vides : la validation échoue (422), mais la tentative est comptée.
        $this->postJson('/api/v1/evenements', [])->assertStatus(422);
        $this->postJson('/api/v1/demande-evenement', [])->assertStatus(422);
        $this->postJson('/api/v1/evenements', [])->assertStatus(429);

        // Les routes de lecture ne sont pas touchées par ce plafond.
        $this->getJson('/api/v1/evenements')->assertOk();
    }

    public function test_les_routes_de_paiement_partagent_un_meme_compteur(): void
    {
        config(['ratelimit.paiement' => 2]);

        $this->postJson('/api/v1/billet/achatBillet', [])->assertStatus(422);
        $this->postJson('/api/v1/transactions/initier', [])->assertStatus(422);
        $this->postJson('/api/v1/transactions/CMD-INCONNUE/valider-paiement')->assertStatus(429);

        // Consulter une transaction reste possible.
        $this->getJson('/api/v1/transactions/CMD-INCONNUE/recapitulatif')->assertStatus(404);
    }

    public function test_le_webhook_de_paiement_echappe_au_plafond_general_mais_reste_borne(): void
    {
        config(['ratelimit.api' => 1, 'ratelimit.webhook' => 3]);

        // Avec "api" à 1, le 2e et le 3e appel seraient refusés si le webhook y était soumis.
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/transactions/callback', [])->assertStatus(422);
        }

        $this->postJson('/api/v1/transactions/callback', [])->assertStatus(429);
    }

    public function test_les_preflights_cors_ne_consomment_pas_le_quota(): void
    {
        config(['ratelimit.api' => 1, 'cors.allowed_origins' => ['https://front.test']]);

        for ($i = 0; $i < 3; $i++) {
            $this->options('/api/v1/evenements', [], [
                'Origin' => 'https://front.test',
                'Access-Control-Request-Method' => 'POST',
            ])->assertNoContent();
        }

        $this->getJson('/api/v1/evenements')->assertOk();
    }
}
