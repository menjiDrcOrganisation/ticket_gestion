<?php

namespace Tests\Feature\Admin;

use App\Jobs\RegenerateTicketPdfJob;
use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Transaction;
use App\Models\TypeBillet;
use App\Models\User;
use App\Services\TicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Supervision des transactions (routes admin/transactions) : accès réservé aux
 * administrateurs et génération forcée du billet.
 */
class TransactionAdminTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private Evenement $evenement;
    private TypeBillet $typeBillet;
    private EvenementTypeBillet $stock;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Queue::fake();

        $this->mock(TicketPdfService::class, fn (MockInterface $mock) => $mock->shouldReceive('generate')->andReturnUsing(function (Billet $billet) {
            $chemin = "billets/{$billet->code_billet}.pdf";
            Storage::disk('public')->put($chemin, 'PDF');
            $billet->update(['billetImage' => $chemin]);

            return $chemin;
        }));

        $this->evenement = $this->creerEvenement();
        $this->typeBillet = $this->creerTypeBillet('VIP');
        $this->stock = $this->ajouterStock($this->evenement, $this->typeBillet, 10, 25, 'USD');
    }

    private function creerTransaction(array $attributs = []): Transaction
    {
        return Transaction::create(array_merge([
            'reference' => 'CMD-' . Str::upper(Str::random(12)),
            'montant' => 50,
            'montant_unitaire' => 25,
            'nombre_billet' => 2,
            'numero_telephone' => '0990000003',
            'nom_complet_client' => 'Awa Client',
            'statut' => 'paiement_en_cours',
            'type' => 'paiement',
            'methode_paiement' => 'MPESA',
            'devise' => 'USD',
            'evenement_id' => $this->evenement->id,
            'type_billet_id' => $this->typeBillet->id,
        ], $attributs));
    }

    // ---- Accès : administrateurs uniquement ----------------------------------------------

    public static function routesTransactions(): array
    {
        return [
            'liste' => ['get', 'transactions.index', false],
            'détail' => ['get', 'transactions.show', true],
            'génération forcée' => ['post', 'transactions.force-generate', true],
            'remboursement' => ['post', 'transactions.refund', true],
        ];
    }

    private function appeler(string $methode, string $route, bool $avecId)
    {
        $url = $avecId ? route($route, $this->creerTransaction()->id) : route($route);

        return $this->call(strtoupper($methode), $url);
    }

    #[DataProvider('routesTransactions')]
    public function test_un_invite_ne_peut_pas_acceder_aux_transactions(string $methode, string $route, bool $avecId): void
    {
        $this->appeler($methode, $route, $avecId)->assertRedirect(route('login'));
    }

    #[DataProvider('routesTransactions')]
    public function test_un_organisateur_ne_peut_pas_acceder_aux_transactions(string $methode, string $route, bool $avecId): void
    {
        $this->actingAs($this->creerOrganisateur()->user);

        $this->appeler($methode, $route, $avecId)->assertForbidden();
    }

    #[DataProvider('routesTransactions')]
    public function test_un_scanneur_ne_peut_pas_acceder_aux_transactions(string $methode, string $route, bool $avecId): void
    {
        $this->actingAs($this->creerScanneur()->user);

        $this->appeler($methode, $route, $avecId)->assertForbidden();
    }

    public function test_un_organisateur_ne_peut_pas_rembourser_une_transaction(): void
    {
        $transaction = $this->creerTransaction(['statut' => 'paye']);

        $this->actingAs($this->creerOrganisateur()->user)
            ->post(route('transactions.refund', $transaction->id))
            ->assertForbidden();

        $transaction->refresh();
        $this->assertSame('paiement', $transaction->type);
        $this->assertSame('paye', $transaction->statut);
    }

    public function test_un_admin_peut_rembourser_une_transaction(): void
    {
        $transaction = $this->creerTransaction(['statut' => 'paye']);

        $this->actingAs($this->creerAdmin())
            ->from(route('transactions.show', $transaction->id))
            ->post(route('transactions.refund', $transaction->id))
            ->assertRedirect(route('transactions.show', $transaction->id))
            ->assertSessionHas('success');

        $transaction->refresh();
        $this->assertSame('remboursement', $transaction->type);
        $this->assertSame('annulee', $transaction->statut);
    }

    // ---- Génération forcée du billet ----------------------------------------------------

    private function forcerGeneration(Transaction $transaction, ?User $admin = null)
    {
        return $this->actingAs($admin ?? $this->creerAdmin())
            ->from(route('transactions.show', $transaction->id))
            ->post(route('transactions.force-generate', $transaction->id));
    }

    public function test_la_generation_forcee_cree_le_billet_et_decremente_le_stock(): void
    {
        $transaction = $this->creerTransaction();

        $this->forcerGeneration($transaction)
            ->assertRedirect(route('transactions.show', $transaction->id))
            ->assertSessionHas('success');

        $transaction->refresh();
        $billet = $transaction->billet;

        $this->assertNotNull($billet);
        $this->assertSame('paye', $transaction->statut);
        $this->assertNotNull($transaction->paid_at);
        $this->assertSame($this->evenement->id, $billet->evenement_id);
        $this->assertSame($this->typeBillet->id, $billet->type_billet_id);
        $this->assertSame(2, (int) $billet->quantite);
        $this->assertSame(2, (int) $billet->quantite_fictif);
        $this->assertSame('valide', $billet->statut);
        $this->assertSame('Awa Client', $billet->nom_auteur);
        Storage::disk('public')->assertExists($billet->billetImage);
        $this->assertSame(8, (int) $this->stock->fresh()->nombre_billet);
    }

    public function test_la_generation_forcee_ne_duplique_pas_un_billet_existant(): void
    {
        $billet = $this->vendreBillet($this->evenement, $this->typeBillet, 2);
        $transaction = $this->creerTransaction(['billet_id' => $billet->id, 'statut' => 'paye']);

        $this->forcerGeneration($transaction)->assertSessionHas('warning');

        $this->assertSame(1, Billet::count());
        $this->assertSame(10, (int) $this->stock->fresh()->nombre_billet);
    }

    public function test_la_generation_forcee_est_refusee_si_le_stock_est_insuffisant(): void
    {
        $this->stock->update(['nombre_billet' => 1]);
        $transaction = $this->creerTransaction();

        $this->forcerGeneration($transaction)->assertSessionHas('error');

        $this->assertSame(0, Billet::count());
        $this->assertNull($transaction->fresh()->billet_id);
        $this->assertSame(1, (int) $this->stock->fresh()->nombre_billet);
    }

    public function test_la_generation_forcee_est_refusee_pour_une_transaction_remboursee(): void
    {
        $transaction = $this->creerTransaction(['type' => 'remboursement', 'statut' => 'annulee']);

        $this->forcerGeneration($transaction)->assertSessionHas('error');

        $this->assertSame(0, Billet::count());
        $this->assertSame(10, (int) $this->stock->fresh()->nombre_billet);
    }

    public function test_la_generation_forcee_garde_le_billet_si_le_pdf_echoue(): void
    {
        $this->mock(TicketPdfService::class, fn (MockInterface $mock) => $mock->shouldReceive('generate')->andThrow(new \Exception('PDF HS')));
        $transaction = $this->creerTransaction();

        $this->forcerGeneration($transaction)->assertSessionHas('success');

        $transaction->refresh();
        $this->assertNotNull($transaction->billet_id);
        $this->assertSame('paye_sans_billet', $transaction->statut);
        Queue::assertPushed(RegenerateTicketPdfJob::class, fn (RegenerateTicketPdfJob $job) => $job->transactionId === $transaction->id);
    }
}
