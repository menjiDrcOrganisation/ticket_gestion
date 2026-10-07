<?php

namespace Tests\Feature\Paiement;

use App\Jobs\RegenerateTicketPdfJob;
use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Transaction;
use App\Models\TypeBillet;
use App\Services\ExchangeRateService;
use App\Services\TicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Parcours de paiement : initiation → lancement Mobile Money → callback → billet.
 * La passerelle, le taux de change et la génération PDF sont simulés.
 */
class PaiementIntegrationTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private const PASSERELLE = 'https://gateway.example.test/pay';

    private Evenement $evenement;
    private TypeBillet $typeBillet;
    private EvenementTypeBillet $stock;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Queue::fake();

        $this->mock(ExchangeRateService::class, fn (MockInterface $mock) => $mock->shouldReceive('getUSDtoCDF')->andReturn(2800.0));
        $this->mock(TicketPdfService::class, fn (MockInterface $mock) => $mock->shouldReceive('generate')->andReturnUsing(function (Billet $billet) {
            $chemin = "billets/{$billet->code_billet}.pdf";
            Storage::disk('public')->put($chemin, 'PDF');
            $billet->update(['billetImage' => $chemin]);

            return $chemin;
        }));

        $this->evenement = $this->creerEvenement(null, null, ['nom' => 'Festival']);
        $this->typeBillet = $this->creerTypeBillet('VIP');
        $this->stock = $this->ajouterStock($this->evenement, $this->typeBillet, 10, 25, 'USD');
    }

    private function donneesInitiation(array $surcharges = []): array
    {
        return array_merge([
            'type_billet' => $this->typeBillet->id,
            'id_evenement' => $this->evenement->id,
            'nombre_reel' => 2,
            'nom_complet_client' => 'Awa Client',
            'numero_client' => '0990000003',
            'service' => 'MPESA',
            'devise' => 'USD',
        ], $surcharges);
    }

    private function initier(array $surcharges = []): Transaction
    {
        $reference = $this->postJson(route('transactions.initier'), $this->donneesInitiation($surcharges))
            ->assertCreated()
            ->json('transaction.reference');

        return Transaction::where('reference', $reference)->firstOrFail();
    }

    private function envoyerCallback(Transaction $transaction, array $surcharges = [])
    {
        return $this->postJson(route('transactions.callback'), array_merge([
            'transactionReference' => $transaction->reference,
            'transactionStatus' => 'SUCCESS',
            'amount' => (float) $transaction->montant,
            'gatewayReference' => 'GW-123',
        ], $surcharges));
    }

    // ---- Initiation -----------------------------------------------------------------------

    public function test_initiation_d_une_transaction(): void
    {
        $response = $this->postJson(route('transactions.initier'), $this->donneesInitiation())
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('transaction.statut', 'en_attente')
            ->assertJsonPath('transaction.billet.evenement', 'Festival')
            ->assertJsonPath('transaction.billet.type', 'VIP')
            ->assertJsonPath('transaction.billet.quantite', 2)
            ->assertJsonPath('transaction.billet.montant_total', 50);

        $this->assertMatchesRegularExpression('/^CMD-\d{17}-[A-F0-9]{12}$/', $response->json('transaction.reference'));

        $transaction = Transaction::firstOrFail();
        $this->assertSame('50.00', $transaction->montant);
        $this->assertTrue($transaction->expires_at->between(now()->addMinutes(19), now()->addMinutes(21)));

        // Le stock n'est réservé qu'à la confirmation du paiement.
        $this->assertSame(10, $this->stock->fresh()->nombre_billet);
        Http::assertNothingSent();
    }

    public function test_conversion_de_devise_usd_vers_cdf(): void
    {
        $transaction = $this->initier(['devise' => 'CDF', 'nombre_reel' => 3]);

        $this->assertSame('70000.00', $transaction->montant_unitaire); // 25 USD × 2800
        $this->assertSame('210000.00', $transaction->montant);
        $this->assertSame('CDF', $transaction->devise);
    }

    public function test_conversion_de_devise_cdf_vers_usd(): void
    {
        $this->stock->update(['prix_unitaire' => 5600, 'devise' => 'CDF']);

        $transaction = $this->initier(['devise' => 'USD', 'nombre_reel' => 1]);

        $this->assertSame('2.00', $transaction->montant);
    }

    public function test_initiation_refusee_si_le_stock_est_insuffisant(): void
    {
        $this->postJson(route('transactions.initier'), $this->donneesInitiation(['nombre_reel' => 11]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Billets insuffisants.');

        $this->assertSame(0, Transaction::count());
    }

    public function test_initiation_refusee_pour_un_type_de_billet_hors_evenement(): void
    {
        $autreType = $this->creerTypeBillet('Standard');

        $this->postJson(route('transactions.initier'), $this->donneesInitiation(['type_billet' => $autreType->id]))
            ->assertNotFound();

        $this->assertSame(0, Transaction::count());
    }

    public static function initiationsInvalides(): array
    {
        return [
            'quantité nulle' => [['nombre_reel' => 0], 'nombre_reel'],
            'devise inconnue' => [['devise' => 'EUR'], 'devise'],
            'client manquant' => [['nom_complet_client' => ''], 'nom_complet_client'],
            'numéro trop long' => [['numero_client' => str_repeat('9', 26)], 'numero_client'],
            'service manquant' => [['service' => ''], 'service'],
            'événement non numérique' => [['id_evenement' => 'abc'], 'id_evenement'],
        ];
    }

    #[DataProvider('initiationsInvalides')]
    public function test_validation_de_l_initiation(array $surcharges, string $champ): void
    {
        $this->postJson(route('transactions.initier'), $this->donneesInitiation($surcharges))
            ->assertStatus(422)
            ->assertJsonValidationErrors($champ);
    }

    public function test_recapitulatif_d_une_transaction(): void
    {
        $transaction = $this->initier();

        $this->getJson(route('transactions.recapitulatif', $transaction->reference))
            ->assertOk()
            ->assertJsonPath('can_pay', true)
            ->assertJsonPath('transaction.reference', $transaction->reference);

        $this->getJson(route('transactions.recapitulatif', 'CMD-INCONNUE'))->assertNotFound();
    }

    // ---- Lancement du paiement --------------------------------------------------------------

    public function test_lancement_du_paiement_aupres_de_la_passerelle(): void
    {
        Http::fake([self::PASSERELLE => Http::response(['reference' => 'PROV-1'], 200)]);
        $transaction = $this->initier();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('transaction_reference', $transaction->reference);

        $transaction->refresh();
        $this->assertSame('paiement_en_cours', $transaction->statut);
        $this->assertSame('PROV-1', $transaction->provider_reference);
        $this->assertNotNull($transaction->payment_started_at);

        Http::assertSent(function (HttpRequest $request) use ($transaction) {
            return $request->url() === self::PASSERELLE
                && $request['transactionReference'] === $transaction->reference
                && $request['gatewayMode'] === '0'
                && $request['publicApiKey'] === 'pk_test_fake'
                && (float) $request['order']['amount'] === 50.0
                && $request['order']['currency'] === 'USD'
                && $request['paymentChannel']['provider'] === 'MPESA'
                && $request['paymentChannel']['walletID'] === '0990000003'
                && str_ends_with($request['paymentChannel']['callbackUrl'], '/api/v1/transactions/callback');
        });
    }

    public function test_refus_definitif_de_la_passerelle(): void
    {
        Http::fake([self::PASSERELLE => Http::response(['errors' => ['message' => 'Numéro invalide']], 400)]);
        $transaction = $this->initier();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))
            ->assertStatus(502)
            ->assertJsonPath('error', 'Numéro invalide');

        $transaction->refresh();
        $this->assertSame('echoue', $transaction->statut);
        $this->assertNotNull($transaction->failed_at);

        // Une transaction échouée peut être relancée.
        $this->getJson(route('transactions.recapitulatif', $transaction->reference))->assertJsonPath('can_pay', true);
    }

    public function test_reponse_incertaine_de_la_passerelle_laisse_le_paiement_en_attente(): void
    {
        Http::fake([self::PASSERELLE => Http::response(['title' => 'Service indisponible'], 503)]);
        $transaction = $this->initier();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))
            ->assertStatus(202)
            ->assertJsonPath('pending', true);

        $this->assertSame('paiement_en_cours', $transaction->fresh()->statut);
    }

    public function test_passerelle_injoignable(): void
    {
        Http::fake([self::PASSERELLE => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);
        $transaction = $this->initier();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))->assertStatus(202);

        $this->assertSame('paiement_en_cours', $transaction->fresh()->statut);
    }

    public function test_une_transaction_expiree_ne_peut_pas_etre_payee(): void
    {
        Http::fake();
        $transaction = $this->initier();
        $this->travel(21)->minutes();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))->assertStatus(422);

        $this->assertSame('en_attente', $transaction->fresh()->statut);
        Http::assertNothingSent();
    }

    public function test_un_paiement_deja_en_cours_ne_peut_pas_etre_relance(): void
    {
        Http::fake([self::PASSERELLE => Http::response(['reference' => 'PROV-1'], 200)]);
        $transaction = $this->initier();

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))->assertOk();
        $this->postJson(route('transactions.valider_paiement', $transaction->reference))->assertStatus(409);

        Http::assertSentCount(1);
    }

    public function test_une_transaction_payee_ne_peut_pas_etre_repayee(): void
    {
        Http::fake();
        $transaction = $this->initier();
        $transaction->update(['statut' => 'paye']);

        $this->postJson(route('transactions.valider_paiement', $transaction->reference))->assertStatus(409);
        $this->postJson(route('transactions.valider_paiement', 'CMD-INCONNUE'))->assertNotFound();

        Http::assertNothingSent();
    }

    // ---- Callback et émission du billet ---------------------------------------------------

    public function test_callback_de_succes_emet_le_billet_et_decremente_le_stock(): void
    {
        $transaction = $this->initier(['nombre_reel' => 3]);

        $this->envoyerCallback($transaction)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('statut', 'paye')
            ->assertJsonPath('download_url', route('transactions.download', $transaction->reference));

        $transaction->refresh();
        $this->assertSame('paye', $transaction->statut);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame('GW-123', $transaction->gateway_reference);

        $billet = $transaction->billet;
        $this->assertSame(3, $billet->quantite);
        $this->assertSame(3, $billet->quantite_fictif);
        $this->assertSame('valide', $billet->statut);
        $this->assertSame($this->evenement->id, $billet->evenement_id);
        $this->assertSame(7, $this->stock->fresh()->nombre_billet);

        // Le billet vendu est pris en compte dans le taux de remplissage.
        $this->assertSame(30.0, $this->evenement->tauxRemplissage());

        $this->get(route('transactions.download', $transaction->reference))->assertOk()->assertDownload();
    }

    public function test_un_callback_rejoue_n_emet_pas_de_second_billet(): void
    {
        $transaction = $this->initier();

        $this->envoyerCallback($transaction)->assertOk();
        $this->envoyerCallback($transaction)->assertOk()->assertJsonPath('message', 'Transaction deja traitee.');

        $this->assertSame(1, Billet::count());
        $this->assertSame(8, $this->stock->fresh()->nombre_billet);
    }

    public function test_callback_d_echec_du_fournisseur(): void
    {
        $transaction = $this->initier();

        $this->envoyerCallback($transaction, ['transactionStatus' => 'FAILED'])->assertStatus(422);

        $this->assertSame('echoue', $transaction->fresh()->statut);
        $this->assertSame(0, Billet::count());
        $this->assertSame(10, $this->stock->fresh()->nombre_billet);
    }

    public function test_callback_avec_un_montant_different_est_refuse(): void
    {
        $transaction = $this->initier();

        $this->envoyerCallback($transaction, ['amount' => 1])->assertStatus(422)->assertJsonPath('message', 'Montant callback invalide.');

        $this->assertSame('echoue', $transaction->fresh()->statut);
        $this->assertSame(0, Billet::count());
    }

    public function test_callback_sans_reference_ou_inconnu(): void
    {
        $this->postJson(route('transactions.callback'), ['transactionStatus' => 'SUCCESS'])->assertStatus(422);
        $this->postJson(route('transactions.callback'), ['transactionReference' => 'CMD-X', 'transactionStatus' => 'SUCCESS'])->assertNotFound();
    }

    public function test_verification_serveur_du_paiement_quand_elle_est_configuree(): void
    {
        $this->definirEnv('MOBILE_MONEY_VERIFY_URL', 'https://gateway.example.test/verify');
        Http::fake(['https://gateway.example.test/verify' => Http::response(['transactionStatus' => 'PENDING'])]);
        $transaction = $this->initier();

        $this->envoyerCallback($transaction)->assertStatus(422)->assertJsonPath('message', 'Paiement non confirme par le fournisseur.');

        $this->assertSame('echoue', $transaction->fresh()->statut);
        $this->assertSame(0, Billet::count());
    }

    public function test_verification_serveur_confirmee(): void
    {
        $this->definirEnv('MOBILE_MONEY_VERIFY_URL', 'https://gateway.example.test/verify');
        Http::fake(['https://gateway.example.test/verify' => Http::response(['transactionStatus' => 'SUCCESS'])]);
        $transaction = $this->initier();

        $this->envoyerCallback($transaction)->assertOk()->assertJsonPath('statut', 'paye');

        Http::assertSent(fn (HttpRequest $request) => $request['transactionReference'] === $transaction->reference
            && (float) $request['amount'] === 50.0);
    }

    public function test_echec_de_generation_du_pdf_apres_paiement(): void
    {
        $this->mock(TicketPdfService::class, fn (MockInterface $mock) => $mock->shouldReceive('generate')->andThrow(new \Exception('Dompdf KO')));
        $transaction = $this->initier();

        $this->envoyerCallback($transaction)
            ->assertOk()
            ->assertJsonPath('statut', 'paye_sans_billet')
            ->assertJsonPath('download_url', null);

        // Le paiement reste acquis et le billet existe : seule la génération PDF est relancée en tâche de fond.
        $this->assertSame(1, Billet::count());
        $this->assertSame(8, $this->stock->fresh()->nombre_billet);
        Queue::assertPushed(RegenerateTicketPdfJob::class, fn ($job) => $job->transactionId === $transaction->id);
    }

    public function test_le_billet_d_une_transaction_non_payee_n_est_pas_telechargeable(): void
    {
        $transaction = $this->initier();
        $billet = $this->vendreBillet($this->evenement, $this->typeBillet, 1, ['billetImage' => 'billets/x.pdf']);
        $transaction->update(['billet_id' => $billet->id]);

        $this->get(route('transactions.download', $transaction->reference))->assertStatus(422);
    }

    // ---- Transactions et rollback ----------------------------------------------------------

    public function test_rollback_si_le_stock_est_epuise_au_moment_de_la_confirmation(): void
    {
        $transaction = $this->initier(['nombre_reel' => 5]);
        // Entre-temps, d'autres acheteurs ont vidé le stock.
        $this->stock->update(['nombre_billet' => 4]);

        $this->envoyerCallback($transaction)->assertServerError();

        $transaction->refresh();
        $this->assertSame('en_attente', $transaction->statut);
        $this->assertNull($transaction->billet_id);
        $this->assertNull($transaction->paid_at);
        $this->assertSame(0, Billet::count());
        $this->assertSame(4, $this->stock->fresh()->nombre_billet);
    }

    public function test_rollback_si_la_creation_du_billet_echoue(): void
    {
        $transaction = $this->initier(['nombre_reel' => 2]);
        Billet::creating(fn () => throw new \RuntimeException('Insertion billet impossible'));

        $this->envoyerCallback($transaction)->assertServerError();

        // Le décrément du stock effectué avant l'échec est annulé.
        $this->assertSame(10, $this->stock->fresh()->nombre_billet);
        $this->assertNull($transaction->fresh()->billet_id);
        $this->assertSame(0, Billet::count());
    }

    private function definirEnv(string $cle, string $valeur): void
    {
        $anciennes = [$_ENV[$cle] ?? null, $_SERVER[$cle] ?? null];
        $_ENV[$cle] = $_SERVER[$cle] = $valeur;

        $this->beforeApplicationDestroyed(function () use ($cle, $anciennes): void {
            [$_ENV[$cle], $_SERVER[$cle]] = $anciennes;
        });
    }
}
